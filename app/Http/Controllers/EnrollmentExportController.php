<?php

namespace App\Http\Controllers;

use App\Livewire\EnrollmentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentExportController extends Controller
{
    private const FLUSH_EVERY = 5000;

    public function csv(Request $request): StreamedResponse
    {
        $token    = $request->query('token', '');
        $cacheKey = "csv_export_{$token}";

        $sessionData = Cache::get($cacheKey);

        if (!$sessionData) {
            Log::warning('[CSV Export] Token tidak ditemukan', [
                'token'        => $token,
                'cache_driver' => config('cache.default'),
            ]);
            abort(404, 'Token ekspor tidak valid atau sudah kedaluwarsa.');
        }

        if (now()->timestamp > ($sessionData['expires_at'] ?? 0)) {
            Cache::forget($cacheKey);
            abort(410, 'Link ekspor sudah kedaluwarsa. Silakan klik Export CSV lagi.');
        }

        Cache::forget($cacheKey);

        $filters  = $sessionData['filters'] ?? [];
        $fileName = 'enrollments_' . now()->format('Ymd_His') . '.csv';

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::disableQueryLog();

        return response()->streamDownload(
            function () use ($filters, $fileName) {

                $obLevel = ob_get_level();
                for ($i = 0; $i < $obLevel; $i++) {
                    if (!@ob_end_clean()) {
                        @ob_flush();
                        break;
                    }
                }

                if (!headers_sent()) {
                    header('Content-Type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="' . $fileName . '"');
                    header('Cache-Control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('X-Accel-Buffering: no');
                    header('Content-Encoding: none');
                }

                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'NIM',
                    'Nama Mahasiswa',
                    'Kode MK',
                    'Mata Kuliah',
                    'Tahun Akademik',
                    'Semester',
                    'Status',
                    'Tanggal Daftar',
                ]);

                if (ob_get_level() > 0) ob_flush();
                flush();

                try {
                    $f = $filters;
                    unset($f['perPage']);
                    if (empty($f['selected_ids'])) {
                        unset($f['selected_ids']);
                    }

                    $rowCount = 0;
                    $buffer   = '';

                    foreach (EnrollmentManager::buildExportQuery($f) as $row) {
                        $buffer .= $this->csvLine([
                            $row->nim,
                            $row->student_name,
                            $row->course_code,
                            $row->course_name,
                            $row->academic_year,
                            $row->semester == 1 ? 'Ganjil' : 'Genap',
                            $row->status,
                            $row->created_at
                                ? date('d/m/Y H:i', strtotime($row->created_at))
                                : '',
                        ]);

                        $rowCount++;

                        if ($rowCount % self::FLUSH_EVERY === 0) {
                            fwrite($handle, $buffer);
                            $buffer = '';
                            if (ob_get_level() > 0) ob_flush();
                            flush();
                        }
                    }

                    if ($buffer !== '') {
                        fwrite($handle, $buffer);
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }

                    if ($rowCount === 0) {
                        fputcsv($handle, ['(Tidak ada data yang ditemukan)']);
                    }

                    Log::info('[CSV Export] Selesai', ['rows' => $rowCount, 'file' => $fileName]);

                } catch (\Throwable $e) {
                    Log::error('[CSV Export] ' . $e->getMessage(), [
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'filters' => $filters,
                    ]);
                    fputcsv($handle, ['ERROR: ' . $e->getMessage()]);
                } finally {
                    fclose($handle);
                }
            },
            $fileName,
            [
                'Content-Type'           => 'text/csv; charset=UTF-8',
                'Content-Disposition'    => 'attachment; filename="' . $fileName . '"',
                'Cache-Control'          => 'no-store, no-cache, must-revalidate',
                'Pragma'                 => 'no-cache',
                'X-Accel-Buffering'      => 'no',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Encoding'       => 'none',
            ]
        );
    }

    private function csvLine(array $fields): string
    {
        $parts = [];
        foreach ($fields as $field) {
            $field = (string) $field;
            if (
                str_contains($field, '"')
                || str_contains($field, ',')
                || str_contains($field, "\n")
                || str_contains($field, "\r")
            ) {
                $parts[] = '"' . str_replace('"', '""', $field) . '"';
            } else {
                $parts[] = $field;
            }
        }
        return implode(',', $parts) . "\r\n";
    }
}
