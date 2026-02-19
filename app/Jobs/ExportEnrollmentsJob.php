<?php

namespace App\Jobs;

use App\Livewire\EnrollmentManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use ZipArchive;

/**
 * ExportEnrollmentsJob
 * ─────────────────────────────────────────────────────────────────────────────
 * TS-13: Export 5 Juta Data — proses background via Laravel Queue.
 *
 * Strategi agar stabil untuk dataset sangat besar (5 juta+ baris):
 *  1. Keyset pagination (e.id > $lastId LIMIT FETCH_SIZE) → memori konstan,
 *     tidak ada OFFSET yang makin lambat seiring bertambahnya data.
 *  2. Split file XLSX setiap MAX_ROWS_PER_FILE baris → file tetap dapat
 *     dibuka di Excel/LibreOffice (batas Excel ~1,04 juta baris per sheet).
 *  3. Semua file XLSX dikemas ke satu ZIP → satu URL download.
 *  4. Progress dilaporkan ke Cache setiap PROGRESS_INTERVAL baris →
 *     frontend dapat polling dan menampilkan progress bar.
 *  5. Timeout job 1 jam; $tries = 1 (tidak retry) karena file parsial
 *     akan dikira selesai jika job di-retry.
 *  6. Direktori output TIDAK dihapus setelah job selesai — file ZIP
 *     tetap tersedia 24 jam untuk didownload.
 *  7. Pada error: cache key "export_error_{token}" diisi, direktori
 *     output dibersihkan agar tidak membuang disk space.
 */
class ExportEnrollmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ── Konfigurasi job ──────────────────────────────────────────────────────
    public $timeout = 3600;  // 1 jam — cukup untuk 5 juta baris
    public $tries   = 1;     // Jangan retry — file parsial bisa membingungkan

    // ── Konstanta export ─────────────────────────────────────────────────────
    private const FETCH_SIZE        = 5000;       // Baris per batch DB
    private const MAX_ROWS_PER_FILE = 1_000_000;  // Baris per file XLSX
    private const PROGRESS_INTERVAL = 10_000;     // Update cache setiap N baris

    private const HEADERS = [
        'ID Enrollment',
        'NIM',
        'Nama Mahasiswa',
        'Kode Mata Kuliah',
        'Nama Mata Kuliah',
        'Tahun Akademik',
        'Semester',
        'Status',
        'Tanggal Dibuat',
    ];

    public function __construct(
        protected array  $filters,
        protected string $token
    ) {}

    // =========================================================================
    // ENTRY POINT
    // =========================================================================

    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $tokenPath   = "exports/{$this->token}";
        $absoluteDir = storage_path("app/{$tokenPath}");

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        Log::info("[XLSX Job] Mulai. token={$this->token}");

        try {
            $fileIndex          = 1;
            $rowsInCurrentFile  = 0;
            $totalRowsProcessed = 0;
            $lastId             = 0;
            $generatedFiles     = [];

            [$writer, $currentFilePath] = $this->createWriter($tokenPath, $fileIndex);
            $generatedFiles[] = $currentFilePath;

            $pdo            = DB::connection()->getPdo();
            $isSelectedMode = !empty($this->filters['selected_ids']);

            if ($isSelectedMode) {
                // ── Mode A: selected_ids (jumlah kecil, ambil sekali) ─────────
                $rows = $this->fetchSelectedRows($pdo);

                foreach ($rows as $row) {
                    $writer->addRow(Row::fromValues($this->formatRow($row)));
                    $rowsInCurrentFile++;
                    $totalRowsProcessed++;

                    if ($rowsInCurrentFile >= self::MAX_ROWS_PER_FILE) {
                        $writer->close();
                        $fileIndex++;
                        $rowsInCurrentFile = 0;
                        [$writer, $currentFilePath] = $this->createWriter($tokenPath, $fileIndex);
                        $generatedFiles[] = $currentFilePath;
                    }
                }
                unset($rows);
            } else {
                // ── Mode B: keyset pagination untuk seluruh dataset ───────────
                //
                // Kita prepare statement SEKALI di luar loop, lalu rebind cursor
                // di dalam loop. Ini menghindari overhead prepare() berulang dan
                // lebih aman daripada memanggil PDO::prepare() jutaan kali.

                [$whereClause, $baseBindings] = EnrollmentManager::buildRawWhereClause($this->filters);

                $allowedSort = [
                    'enrollments.id',
                    'enrollments.created_at',
                    'enrollments.academic_year',
                    'students.nim',
                    'students.name',
                    'courses.code',
                ];
                $sortBy  = in_array($this->filters['sortBy'] ?? '', $allowedSort, true)
                    ? $this->filters['sortBy'] : 'enrollments.id';
                $sortDir = strtolower($this->filters['sortDir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
                $sortByMapped = str_replace(
                    ['enrollments.', 'students.', 'courses.'],
                    ['e.', 's.', 'c.'],
                    $sortBy
                );

                $connector         = empty($whereClause) ? 'WHERE' : 'AND';
                $cursorIdx         = count($baseBindings) + 1;
                $cursorPlaceholder = '$' . $cursorIdx;

                $sql = "
                    SELECT
                        e.id,
                        s.nim,
                        s.name          AS student_name,
                        c.code          AS course_code,
                        c.name          AS course_name,
                        e.academic_year,
                        e.semester,
                        e.status,
                        TO_CHAR(e.created_at AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI:SS') AS created_at
                    FROM enrollments e
                    JOIN students s ON e.student_id = s.id
                    JOIN courses  c ON e.course_id  = c.id
                    {$whereClause}
                    {$connector} e.id > {$cursorPlaceholder}
                    ORDER BY {$sortByMapped} {$sortDir}, e.id {$sortDir}
                    LIMIT " . self::FETCH_SIZE . "
                ";

                // Prepare sekali, bind berulang — efisien untuk jutaan iterasi
                $stmt = $pdo->prepare($sql);

                while (true) {
                    // Bind filter bindings (statis)
                    foreach ($baseBindings as $i => $val) {
                        $stmt->bindValue(
                            $i + 1,
                            $val,
                            is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR
                        );
                    }
                    // Bind cursor (dinamis setiap iterasi)
                    $stmt->bindValue($cursorIdx, $lastId, \PDO::PARAM_INT);
                    $stmt->execute();

                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (empty($rows)) break;

                    foreach ($rows as $row) {
                        $writer->addRow(Row::fromValues($this->formatRow($row)));
                        $rowsInCurrentFile++;
                        $totalRowsProcessed++;
                        $lastId = (int) $row['id'];

                        if ($rowsInCurrentFile >= self::MAX_ROWS_PER_FILE) {
                            $writer->close();
                            $fileIndex++;
                            $rowsInCurrentFile = 0;
                            [$writer, $currentFilePath] = $this->createWriter($tokenPath, $fileIndex);
                            $generatedFiles[] = $currentFilePath;
                        }
                    }

                    // Laporkan progress ke Cache agar frontend bisa polling
                    if ($totalRowsProcessed % self::PROGRESS_INTERVAL === 0) {
                        Cache::put("export_progress_{$this->token}", [
                            'rows_processed' => $totalRowsProcessed,
                        ], now()->addMinutes(10));

                        Log::info("[XLSX Job] Progress: {$totalRowsProcessed} baris. token={$this->token}");
                    }

                    unset($rows);
                }
            }

            $writer->close();

            // ── Kemas semua file XLSX ke satu ZIP ────────────────────────────
            $zipFileName = 'enrollments_export_' . now()->format('Ymd_His') . '.zip';
            $zipFilePath = $absoluteDir . '/' . $zipFileName;

            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Gagal membuat file ZIP di: {$zipFilePath}");
            }
            foreach ($generatedFiles as $filePath) {
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();

            // Hapus file XLSX sementara — hanya ZIP yang perlu disimpan
            foreach ($generatedFiles as $filePath) {
                if (file_exists($filePath)) @unlink($filePath);
            }

            // ── Tandai selesai di Cache ───────────────────────────────────────
            Cache::put("export_ready_{$this->token}", [
                'ready' => true,
                'file'  => $zipFileName,
                'rows'  => $totalRowsProcessed,
                'files' => $fileIndex,   // Jumlah file XLSX sebelum di-zip
            ], now()->addHours(24));

            Cache::forget("export_progress_{$this->token}");

            Log::info(
                "[XLSX Job] Selesai. total_rows={$totalRowsProcessed} " .
                    "xlsx_files={$fileIndex} zip={$zipFileName} token={$this->token}"
            );
        } catch (\Throwable $e) {
            Log::error('[XLSX Job] Error: ' . $e->getMessage(), [
                'token' => $this->token,
                'trace' => $e->getTraceAsString(),
            ]);

            // Simpan error ke Cache agar frontend dapat menampilkan pesan
            Cache::put("export_error_{$this->token}", [
                'error'   => true,
                'message' => 'Terjadi kesalahan saat memproses export: ' . $e->getMessage(),
            ], now()->addMinutes(10));

            Cache::forget("export_progress_{$this->token}");

            // Bersihkan file yang mungkin sudah dibuat sebagian
            $this->cleanupDir(storage_path("app/exports/{$this->token}"));

            // Re-throw agar Laravel Queue mencatat job sebagai failed
            throw $e;
        }
    }

    // =========================================================================
    // HELPER PRIVATE
    // =========================================================================

    /**
     * Ambil baris berdasarkan selected_ids (Mode A).
     * Jumlahnya selalu kecil (1 halaman UI), aman diambil sekali.
     */
    private function fetchSelectedRows(\PDO $pdo): array
    {
        $ids          = array_map('intval', (array) $this->filters['selected_ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $allowedSort = [
            'enrollments.id',
            'enrollments.created_at',
            'enrollments.academic_year',
            'students.nim',
            'students.name',
            'courses.code',
        ];
        $sortBy  = in_array($this->filters['sortBy'] ?? '', $allowedSort, true)
            ? $this->filters['sortBy'] : 'enrollments.id';
        $sortDir = strtolower($this->filters['sortDir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $sortByMapped = str_replace(
            ['enrollments.', 'students.', 'courses.'],
            ['e.', 's.', 'c.'],
            $sortBy
        );

        $sql = "
            SELECT
                e.id, s.nim,
                s.name AS student_name, c.code AS course_code, c.name AS course_name,
                e.academic_year, e.semester, e.status,
                TO_CHAR(e.created_at AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI:SS') AS created_at
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            JOIN courses  c ON e.course_id  = c.id
            WHERE e.id IN ({$placeholders})
              AND e.deleted_at IS NULL
            ORDER BY {$sortByMapped} {$sortDir}, e.id {$sortDir}
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Format row assoc-array ke array kolom terurut.
     */
    private function formatRow(array $row): array
    {
        return [
            (int)   $row['id'],
            $row['nim'],
            $row['student_name'],
            $row['course_code'],
            $row['course_name'],
            $row['academic_year'],
            ((int) $row['semester'] === 1 ? 'Ganjil' : 'Genap'),
            $row['status'],
            $row['created_at'],
        ];
    }

    /**
     * Buat writer XLSX baru dengan baris header.
     */
    private function createWriter(string $tokenPath, int $index): array
    {
        $fileName = "data_enrollment_part_{$index}.xlsx";
        $fullPath = storage_path("app/{$tokenPath}/{$fileName}");
        $writer   = new Writer();
        $writer->openToFile($fullPath);
        $writer->addRow(Row::fromValues(self::HEADERS));
        return [$writer, $fullPath];
    }

    /**
     * Hapus seluruh isi direktori beserta direktorinya.
     */
    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file)) @unlink($file);
        }
        @rmdir($dir);
    }
}
