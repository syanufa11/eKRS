<?php

namespace App\Http\Controllers;

use App\Livewire\EnrollmentManager;
use App\Jobs\ExportEnrollmentsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use ZipArchive;

class EnrollmentExportController extends Controller
{
    // =========================================================================
    // KONSTANTA — TS-13: Export 5 Juta Data
    // =========================================================================

    /**
     * Jumlah baris per batch saat membaca dari database.
     * 5.000 = keseimbangan antara konsumsi memori dan jumlah round-trip DB.
     */
    private const FETCH_SIZE = 5000;

    /**
     * Batas baris per file XLSX sebelum membuat file baru (split).
     * Excel sendiri mendukung ~1 juta baris; kita batasi 1 juta agar
     * file tetap dapat dibuka dengan lancar di Excel/LibreOffice.
     */
    private const MAX_ROWS_PER_FILE = 1_000_000;

    /**
     * Header kolom untuk semua format export.
     */
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

    // =========================================================================
    // HELPER PRIVATE
    // =========================================================================

    /**
     * Bangun SQL + bindings untuk satu batch.
     *
     * Dua mode:
     *   A) selected_ids ada  → WHERE e.id IN (...)  — ambil semua sekaligus, tanpa cursor
     *   B) selected_ids kosong → keyset pagination  — e.id > $lastId LIMIT $fetchSize
     *
     * Return: [ $sql, $bindings, $isSelectedMode ]
     */
    private function buildExportSql(array $filters, int $lastId = 0, int $fetchSize = self::FETCH_SIZE): array
    {
        $selectCols = "
            e.id,
            s.nim,
            s.name          AS student_name,
            c.code          AS course_code,
            c.name          AS course_name,
            e.academic_year,
            e.semester,
            e.status,
            TO_CHAR(e.created_at AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI:SS') AS created_at
        ";

        $fromJoin = "
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            JOIN courses  c ON e.course_id  = c.id
        ";

        // Whitelist kolom sort — mencegah SQL injection
        $allowedSort = [
            'enrollments.id',
            'enrollments.created_at',
            'enrollments.academic_year',
            'students.nim',
            'students.name',
            'courses.code',
        ];
        $sortBy  = in_array($filters['sortBy'] ?? '', $allowedSort, true)
            ? $filters['sortBy'] : 'enrollments.id';
        $sortDir = strtolower($filters['sortDir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        $sortByMapped = str_replace(
            ['enrollments.', 'students.', 'courses.'],
            ['e.', 's.', 'c.'],
            $sortBy
        );

        // ── Mode A: selected_ids ─────────────────────────────────────────────────
        if (!empty($filters['selected_ids'])) {
            $ids          = array_map('intval', (array) $filters['selected_ids']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $sql = "
                SELECT {$selectCols}
                {$fromJoin}
                WHERE e.id IN ({$placeholders})
                  AND e.deleted_at IS NULL
                ORDER BY {$sortByMapped} {$sortDir}, e.id {$sortDir}
            ";
            return [$sql, $ids, true];
        }

        // ── Mode B: filter + keyset pagination ────────────────────────────────────
        [$whereClause, $bindings] = EnrollmentManager::buildRawWhereClause($filters);

        // Jika whereClause sudah mengandung "WHERE", sambungkan dengan AND; jika kosong buat baru
        $connector         = empty($whereClause) ? 'WHERE' : 'AND';
        $cursorIdx         = count($bindings) + 1;
        $cursorPlaceholder = '$' . $cursorIdx;
        $bindings[]        = $lastId;

        $sql = "
            SELECT {$selectCols}
            {$fromJoin}
            {$whereClause}
            {$connector} e.id > {$cursorPlaceholder}
            ORDER BY {$sortByMapped} {$sortDir}, e.id {$sortDir}
            LIMIT {$fetchSize}
        ";

        return [$sql, $bindings, false];
    }

    /**
     * Format row assoc-array menjadi array kolom terurut untuk export.
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
     * Eksekusi query dengan positional binding ($N untuk PostgreSQL, ? untuk selected).
     * Kedua mode sebenarnya sama: bindValue($i+1, $val).
     *
     * @param  \PDO    $pdo
     * @param  string  $sql
     * @param  array   $bindings
     * @param  bool    $isSelectedMode  true = ? placeholder, false = $N placeholder
     * @return array
     */
    private function executeQuery(\PDO $pdo, string $sql, array $bindings, bool $isSelectedMode): array
    {
        $stmt = $pdo->prepare($sql);
        foreach ($bindings as $i => $val) {
            $type = ($isSelectedMode || is_int($val)) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($i + 1, $val, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Buat writer XLSX baru dan tulis baris header.
     * Mengembalikan [$writer, $filePath].
     */
    private function createWriter(string $dir, int $index): array
    {
        $path   = $dir . "/data_enrollment_part_{$index}.xlsx";
        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(self::HEADERS));
        return [$writer, $path];
    }

    /**
     * Hapus seluruh isi direktori temp beserta direktorinya.
     */
    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file)) @unlink($file);
        }
        @rmdir($dir);
    }

    // =========================================================================
    // 1. XLSX DIRECT EXPORT
    // =========================================================================

    /**
     * TS-13 — XLSX Direct (halaman ini / data terpilih)
     *
     * Strategi:
     *  • Keyset pagination (FETCH_SIZE per batch) agar tidak OOM
     *  • Split otomatis setiap MAX_ROWS_PER_FILE baris → beberapa file XLSX
     *  • Semua file dikemas ke satu ZIP lalu didownload langsung
     *  • Direktori temp dihapus setelah download
     */
    public function xlsxDirect(Request $request)
    {
        $token    = $request->query('token');
        $cacheKey = "xlsx_export_{$token}";
        $cached   = Cache::get($cacheKey);

        if (!$cached) {
            Log::warning("[XLSX Direct] Token tidak ditemukan atau expired. token={$token}");
            abort(403, 'Token expired atau tidak valid.');
        }

        $filters = $cached['filters'];
        Cache::forget($cacheKey);

        // Pastikan proses tidak mati karena batas waktu/memori PHP
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $tempDir = storage_path("app/temp_xlsx_{$token}");
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            $fileIndex  = 1;
            $rowsInFile = 0;
            $lastId     = 0;
            $totalRows  = 0;
            $files      = [];

            [$writer, $currentPath] = $this->createWriter($tempDir, $fileIndex);
            $files[] = $currentPath;

            $pdo            = DB::connection()->getPdo();
            $isSelectedMode = !empty($filters['selected_ids']);

            if ($isSelectedMode) {
                // ── Mode A: selected_ids — ambil semua sekali ─────────────────────
                [$sql, $bindings,] = $this->buildExportSql($filters);
                $rows = $this->executeQuery($pdo, $sql, $bindings, true);

                foreach ($rows as $row) {
                    $writer->addRow(Row::fromValues($this->formatRow($row)));
                    $rowsInFile++;
                    $totalRows++;

                    if ($rowsInFile >= self::MAX_ROWS_PER_FILE) {
                        $writer->close();
                        $fileIndex++;
                        $rowsInFile = 0;
                        [$writer, $currentPath] = $this->createWriter($tempDir, $fileIndex);
                        $files[] = $currentPath;
                    }
                }
                unset($rows);
            } else {
                // ── Mode B: keyset pagination — cocok untuk dataset besar ────────
                while (true) {
                    [$sql, $bindings,] = $this->buildExportSql($filters, $lastId, self::FETCH_SIZE);
                    $rows = $this->executeQuery($pdo, $sql, $bindings, false);

                    if (empty($rows)) break;

                    foreach ($rows as $row) {
                        $writer->addRow(Row::fromValues($this->formatRow($row)));
                        $rowsInFile++;
                        $totalRows++;
                        $lastId = (int) $row['id'];

                        if ($rowsInFile >= self::MAX_ROWS_PER_FILE) {
                            $writer->close();
                            $fileIndex++;
                            $rowsInFile = 0;
                            [$writer, $currentPath] = $this->createWriter($tempDir, $fileIndex);
                            $files[] = $currentPath;
                        }
                    }
                    unset($rows);
                }
            }

            $writer->close();

            Log::info("[XLSX Direct] Selesai. total_rows={$totalRows} files={$fileIndex} token={$token}");

            // Kemas semua file XLSX ke satu ZIP
            $zipName = 'enrollments_export_' . now()->format('Ymd_His') . '.zip';
            $zipPath = $tempDir . '/' . $zipName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Gagal membuat file ZIP di: {$zipPath}");
            }
            foreach ($files as $f) {
                $zip->addFile($f, basename($f));
            }
            $zip->close();

            // Hapus file XLSX sementara (tetap simpan ZIP untuk download)
            foreach ($files as $f) {
                if (file_exists($f)) @unlink($f);
            }

            return response()
                ->download($zipPath, $zipName)
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('[XLSX Direct] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->cleanupDir($tempDir);
            abort(500, 'Export gagal: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. CSV STREAMING EXPORT
    // =========================================================================

    /**
     * TS-13 — CSV Streaming (semua data / filter aktif)
     *
     * Strategi:
     *  • response()->streamDownload() → data langsung dikirim ke browser line-by-line
     *  • Keyset pagination FETCH_SIZE per batch → konsumsi memori konstan
     *  • ob_flush() + flush() setiap batch agar koneksi tidak timeout di proxy
     *  • BOM UTF-8 di awal agar Excel membuka dengan encoding yang benar
     *  • Tidak ada batas ukuran file — stabil untuk 5 juta baris
     */
    public function csv(Request $request)
    {
        $token    = $request->query('token');
        $cacheKey = "csv_token_{$token}";
        $filters  = Cache::get($cacheKey);

        if (!$filters) {
            Log::warning("[CSV Export] Token tidak ditemukan atau expired. token={$token}");
            abort(403, 'Token expired atau tidak valid.');
        }

        Cache::forget($cacheKey);
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        $filename = 'enrollments_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filters, $filename) {
            // Buka output buffer langsung ke stream — jangan pakai ob_start()
            $handle    = fopen('php://output', 'w');
            $lastId    = 0;
            $totalRows = 0;
            $pdo       = DB::connection()->getPdo();

            // BOM UTF-8 — Excel membutuhkan ini untuk membaca CSV dengan benar
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::HEADERS);

            $isSelectedMode = !empty($filters['selected_ids']);

            if ($isSelectedMode) {
                // ── Mode A: selected_ids ───────────────────────────────────────
                [$sql, $bindings,] = $this->buildExportSql($filters);
                $rows = $this->executeQuery($pdo, $sql, $bindings, true);
                foreach ($rows as $row) {
                    fputcsv($handle, $this->formatRow($row));
                    $totalRows++;
                }
                unset($rows);
            } else {
                // ── Mode B: streaming keyset pagination ───────────────────────
                while (true) {
                    [$sql, $bindings,] = $this->buildExportSql($filters, $lastId, self::FETCH_SIZE);
                    $rows = $this->executeQuery($pdo, $sql, $bindings, false);

                    if (empty($rows)) break;

                    foreach ($rows as $row) {
                        fputcsv($handle, $this->formatRow($row));
                        $lastId = (int) $row['id'];
                        $totalRows++;
                    }

                    // Flush ke browser setiap batch agar koneksi tetap hidup
                    // dan browser menampilkan progress download secara incremental
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    unset($rows);
                }
            }

            fclose($handle);

            Log::info("[CSV Export] Selesai. total_rows={$totalRows} file={$filename}");
        }, $filename, [
            'Content-Type'      => 'text/csv; charset=UTF-8',
            'Cache-Control'     => 'no-store, no-cache, must-revalidate',
            'Pragma'            => 'no-cache',
            'Expires'           => '0',
            // Matikan buffering di Nginx/reverse proxy agar data mengalir langsung
            'X-Accel-Buffering' => 'no',
        ]);
    }

    // =========================================================================
    // 3. DISPATCH XLSX ASYNC JOB
    //    Dipanggil via POST fetch() dengan CSRF token dari blade.
    //    GET tidak bisa dipakai — Laravel mengembalikan 419 CSRF error.
    // =========================================================================

    public function xlsxDispatch(Request $request)
    {
        $token    = $request->query('token');
        $cacheKey = "xlsx_job_token_{$token}";
        $filters  = Cache::get($cacheKey);

        if (!$filters) {
            Log::warning("[XLSX Dispatch] Token tidak ditemukan atau expired. token={$token}");
            return response()->json(['error' => 'Token expired atau tidak valid.'], 403);
        }

        Cache::forget($cacheKey);

        ExportEnrollmentsJob::dispatch($filters, $token)->onQueue('exports');

        Log::info("[XLSX Dispatch] Job di-dispatch. token={$token}");

        return response()->json(['ok' => true]);
    }

    // =========================================================================
    // 4. POLLING STATUS XLSX ASYNC
    // =========================================================================

    public function xlsxStatus(Request $request)
    {
        $token = $request->query('token');

        if ($ready = Cache::get("export_ready_{$token}")) {
            return response()->json([
                'status'       => 'ready',
                'rows'         => $ready['rows'],
                'files'        => $ready['files'] ?? 1,
                'download_url' => route('enrollments.export.xlsx-download', ['token' => $token]),
            ]);
        }

        if ($error = Cache::get("export_error_{$token}")) {
            return response()->json([
                'status'  => 'error',
                'message' => $error['message'] ?? 'Terjadi kesalahan pada server.',
            ]);
        }

        $progress = Cache::get("export_progress_{$token}");
        return response()->json([
            'status'         => 'processing',
            'rows_processed' => $progress['rows_processed'] ?? 0,
        ]);
    }

    // =========================================================================
    // 5. DOWNLOAD HASIL JOB
    // =========================================================================

    public function xlsxDownload(Request $request)
    {
        $token = $request->query('token');
        $ready = Cache::get("export_ready_{$token}");

        if (!$ready) {
            abort(404, 'File tidak ditemukan atau sudah expired (>24 jam).');
        }

        $zipPath = storage_path("app/exports/{$token}/{$ready['file']}");

        if (!file_exists($zipPath)) {
            abort(404, 'File tidak ada di storage. Mungkin sudah dihapus.');
        }

        return response()->download($zipPath, $ready['file']);
    }
}
