<?php

namespace App\Http\Controllers;

use App\Livewire\EnrollmentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $token = $request->query('token', '');
        $sessionKey = "csv_export_{$token}";
        $sessionData = session()->get($sessionKey);

        if (!$sessionData) {
            abort(404, 'Token ekspor tidak valid atau sudah kedaluwarsa. Silakan klik Export CSV lagi.');
        }

        if (now()->timestamp > ($sessionData['expires_at'] ?? 0)) {
            session()->forget($sessionKey);
            abort(410, 'Link ekspor sudah kedaluwarsa. Silakan klik Export CSV lagi.');
        }

        session()->forget($sessionKey);

        $filters = $sessionData['filters'] ?? [];
        $fileName = 'enrollments_' . now()->format('Ymd_His') . '.csv';

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::disableQueryLog();

        return response()->streamDownload(
            function () use ($filters) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8

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
                flush();

                try {
                    $rowCount = 0;
                    $buffer = '';
                    $flushAt = 2000;

                    $query = EnrollmentManager::buildExportQuery($filters)->toBase();

                    foreach ($query->cursor() as $row) {
                        $buffer .= $this->buildCsvRow([
                            $row->nim,
                            $row->student_name,
                            $row->course_code,
                            $row->course_name,
                            $row->academic_year,
                            // Mapping Semester
                            $row->semester == 1 ? 'Ganjil' : 'Genap',
                            $row->status,
                            // Format Tanggal Indonesia
                            date('d/m/Y H:i', strtotime($row->created_at)),
                        ]);

                        $rowCount++;

                        if ($rowCount % $flushAt === 0) {
                            fwrite($handle, $buffer);
                            $buffer = '';
                            flush();
                        }
                    }

                    if ($buffer !== '') {
                        fwrite($handle, $buffer);
                        flush();
                    }
                } catch (\Exception $e) {
                    Log::error('CSV export stream error: ' . $e->getMessage());
                    fputcsv($handle, ['ERROR: Terjadi kesalahan saat generate data. ' . $e->getMessage()]);
                }

                fclose($handle);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }

    private function buildCsvRow(array $fields): string
    {
        $escaped = array_map(function ($field) {
            $field = (string) $field;
            if (str_contains($field, '"') || str_contains($field, ',') || str_contains($field, "\n")) {
                return '"' . str_replace('"', '""', $field) . '"';
            }
            return $field;
        }, $fields);

        return implode(',', $escaped) . "\n";
    }
}
