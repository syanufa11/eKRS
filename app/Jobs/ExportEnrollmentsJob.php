<?php

namespace App\Jobs;

use App\Livewire\EnrollmentManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;

/**
 * ExportEnrollmentsJob
 * ═══════════════════════════════════════════════════════════════════════════
 * Menangani ekspor data enrollment ke XLSX secara asinkron melalui queue.
 *
 * STRATEGI UTAMA (TS-13):
 * ────────────────────────
 * 1. Query menggunakan ->chunk(CHUNK_SIZE) — bukan cursor() — karena
 *    PhpSpreadsheet membutuhkan akses acak ke sel, sementara cursor() hanya
 *    membaca sekali maju (forward-only). Chunk 5.000 baris per iterasi
 *    menjaga penggunaan memori tetap rendah dan terkontrol.
 *
 * 2. Split otomatis per 1.000.000 baris:
 *    Batas maksimum baris Excel adalah 1.048.576. Split setiap 1.000.000
 *    baris memberikan margin aman. Setiap bagian menjadi file .xlsx terpisah
 *    (part_1.xlsx, part_2.xlsx, dst.).
 *
 * 3. ZIP packaging:
 *    Jika data > 1 file (multi-part), semua .xlsx digabung ke satu .zip.
 *    Jika data ≤ 1 file, hasilnya langsung berupa single .xlsx.
 *
 * 4. Query builder di-share dengan EnrollmentManager::buildExportQuery()
 *    agar filter CSV dan XLSX selalu konsisten — satu sumber kebenaran.
 *
 * 5. Output disimpan ke storage/app/exports/{userId}/{fileName}.
 *    Status "ready" disimpan ke cache dengan key export_ready_{userId}
 *    agar frontend dapat polling dan menampilkan SweetAlert2 sukses.
 *
 * REQUIREMENT:
 *    composer require phpoffice/phpspreadsheet
 *    php artisan queue:work --queue=exports
 *    (atau tambahkan 'exports' ke horizon.php)
 */
class ExportEnrollmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Jumlah baris data per chunk query (jaga memori tetap rendah) */
    private const CHUNK_SIZE = 5_000;

    /** Jumlah baris data per file XLSX (margin dari batas 1.048.576) */
    private const ROWS_PER_FILE = 1_000_000;

    /** Kolom header XLSX */
    private const HEADERS = [
        'NIM',
        'Nama Mahasiswa',
        'Kode MK',
        'Mata Kuliah',
        'Tahun Akademik',
        'Semester',
        'Status',
        'Tanggal Daftar',
    ];

    /**
     * Batas waktu job dalam detik.
     * Set tinggi untuk data jutaan baris. Sesuaikan dengan timeout server.
     */
    public int $timeout = 3600;

    /**
     * Jumlah percobaan ulang jika job gagal.
     * Ekspor besar sebaiknya tidak diulang otomatis (bisa dobel file).
     */
    public int $tries = 1;

    public function __construct(
        private readonly array $filters,
        private readonly int   $userId = 0
    ) {}

    public function handle(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::disableQueryLog();

        $timestamp  = now()->format('Ymd_His');
        $exportDir  = "exports/{$this->userId}";
        $partPaths  = [];
        $partCount  = 1;
        $totalRows  = 0;
        $rowsInFile = 0;

        // Inisialisasi spreadsheet dan sheet pertama
        [$spreadsheet, $sheet] = $this->newSpreadsheet($partCount);
        $sheetRow = 2;  // Baris 1 = header

        // ── Stream data via chunk() ──────────────────────────────────────────
        // Menggunakan EnrollmentManager::buildExportQuery() sebagai
        // satu-satunya sumber query builder — konsisten dengan CSV export.
        EnrollmentManager::buildExportQuery($this->filters)
            ->chunk(self::CHUNK_SIZE, function ($rows) use (
                &$spreadsheet,
                &$sheet,
                &$sheetRow,
                &$rowsInFile,
                &$totalRows,
                &$partCount,
                &$partPaths,
                $exportDir,
                $timestamp
            ) {
                foreach ($rows as $row) {
                    // Jika file saat ini sudah penuh → simpan & mulai file baru
                    if ($rowsInFile >= self::ROWS_PER_FILE) {
                        $partPaths[] = $this->saveSpreadsheet(
                            $spreadsheet,
                            $exportDir,
                            $timestamp,
                            $partCount
                        );
                        $partCount++;
                        $rowsInFile = 0;
                        $sheetRow   = 2;

                        // Bebaskan memori spreadsheet lama sebelum buat yang baru
                        $spreadsheet->disconnectWorksheets();
                        unset($spreadsheet, $sheet);
                        gc_collect_cycles();

                        [$spreadsheet, $sheet] = $this->newSpreadsheet($partCount);
                    }

                    // Tulis baris data ke sheet
                    $sheet->fromArray([
                        $row->nim,
                        $row->student_name,
                        $row->course_code,
                        $row->course_name,
                        $row->academic_year,
                        $row->semester == 1 ? 'Ganjil' : 'Genap',
                        $row->status,
                        $row->created_at,
                    ], null, "A{$sheetRow}");

                    $sheetRow++;
                    $rowsInFile++;
                    $totalRows++;
                }
            });

        // ── Simpan spreadsheet terakhir ─────────────────────────────────────
        // Selalu simpan meski kosong (data 0 baris) agar menghasilkan file valid
        $partPaths[] = $this->saveSpreadsheet(
            $spreadsheet,
            $exportDir,
            $timestamp,
            $partCount
        );

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();

        // ── Package hasil ────────────────────────────────────────────────────
        if (count($partPaths) === 1) {
            // Single file: rename langsung ke nama final
            $finalPath = "{$exportDir}/enrollments_{$timestamp}.xlsx";
            Storage::move($partPaths[0], $finalPath);
        } else {
            // Multi-file: kemas semua part ke dalam satu ZIP
            $finalPath = $this->zipParts($partPaths, $exportDir, $timestamp);

            // Hapus file part sementara setelah dikemas
            foreach ($partPaths as $p) {
                Storage::delete($p);
            }
        }

        // ── Notifikasi user via cache polling ───────────────────────────────
        // Frontend (JS) polling /enrollments/export/status setiap beberapa detik.
        // Key harus cocok dengan yang di-forget saat dispatch di EnrollmentManager.
        $downloadUrl = Storage::url($finalPath);

        cache()->put(
            "export_ready_{$this->userId}",
            [
                'ready' => true,
                'url'   => $downloadUrl,
                'rows'  => $totalRows,
                'file'  => basename($finalPath),
            ],
            now()->addHours(2)
        );

        // Opsional: broadcast event real-time jika menggunakan Laravel Echo
        // event(new \App\Events\ExportReady($this->userId, $downloadUrl, $totalRows));
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    /**
     * Buat Spreadsheet baru dengan baris header dan styling.
     *
     * @return array{0: Spreadsheet, 1: \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet}
     */
    private function newSpreadsheet(int $partNumber): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Data_Part{$partNumber}");
        $sheet->fromArray(self::HEADERS, null, 'A1');

        // Styling header: bold + background abu-abu
        $lastCol     = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(self::HEADERS));
        $headerRange = "A1:{$lastCol}1";
        $headerStyle = $sheet->getStyle($headerRange);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');

        // Auto-width kolom (A–H = 8 kolom)
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [$spreadsheet, $sheet];
    }

    /**
     * Simpan spreadsheet ke storage dan kembalikan path relatifnya.
     */
    private function saveSpreadsheet(
        Spreadsheet $spreadsheet,
        string      $exportDir,
        string      $timestamp,
        int         $partNumber
    ): string {
        $relativePath = "{$exportDir}/enrollments_{$timestamp}_part{$partNumber}.xlsx";
        $localPath    = storage_path("app/{$relativePath}");

        @mkdir(dirname($localPath), 0755, true);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($localPath);

        return $relativePath;
    }

    /**
     * Kemas semua file XLSX ke dalam satu arsip ZIP.
     *
     * @param  string[] $partPaths Path relatif tiap file part
     * @return string Path relatif file ZIP
     */
    private function zipParts(array $partPaths, string $exportDir, string $timestamp): string
    {
        $zipRelative = "{$exportDir}/enrollments_{$timestamp}.zip";
        $zipAbsolute = storage_path("app/{$zipRelative}");

        @mkdir(dirname($zipAbsolute), 0755, true);

        $zip = new ZipArchive();
        $zip->open($zipAbsolute, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($partPaths as $path) {
            $absolute = storage_path("app/{$path}");
            $zip->addFile($absolute, basename($absolute));
        }

        $zip->close();

        return $zipRelative;
    }
}
