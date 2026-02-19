<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Jobs\ExportEnrollmentsJob;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Livewire\Traits\WithBreadcrumbs;

class EnrollmentManager extends Component
{
    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumb('Enrollment Management');
        // atau:
        // $this->breadcrumbMulti('Academic', 'Enrollment Management');
    }

    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'enrollments.created_at';

    #[Url(history: true)]
    public $sortDir = 'desc';

    public $filterStatus   = '';
    public $filterSemester = '';
    public $filterYear     = '';
    public $filterCourse   = '';
    public $filterOperator = 'AND';

    public $isOpen       = false;
    public $editMode     = false;
    public $isDetailOpen = false;
    public $selectedId;
    public $selectedEnrollment;

    public $student_nim, $student_name, $student_email;
    public $course_id, $academic_year = '2025/2026', $semester = '1', $status = 'DRAFT';

    public $selectedRows = [];
    public $selectAll    = false;

    public $exportJobDispatched = false;

    private const ALLOWED_SORT_COLUMNS = [
        'enrollments.id',
        'enrollments.created_at',
        'enrollments.academic_year',
        'students.nim',
        'students.name',
        'courses.code',
    ];

    #[Url(history: true)]
    public int $perPage = 15;

    // ─── Pagination ────────────────────────────────────────────────────────────
    public function updatingPage(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $q = $this->applyQuery()
                ->select('enrollments.id')
                ->orderBy($this->sortBy, $this->sortDir);

            // Jika perPage = 0 (Semua), ambil semua ID; jika tidak, hanya halaman aktif
            $this->selectedRows = ($this->perPage === 0)
                ? $q->pluck('enrollments.id')->map(fn($id) => (string) $id)->toArray()
                : $q->paginate($this->perPage)->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // ─── Validation ────────────────────────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'student_nim'   => 'required|numeric|digits_between:8,12',
            'student_name'  => 'required|string|min:3|max:100',
            'student_email' => 'required|email|max:150',
            'course_id'     => 'required|exists:courses,id',
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'      => 'required|in:1,2',
            'status'        => 'required|in:DRAFT,SUBMITTED,APPROVED,REJECTED',
        ];
    }

    protected function messages(): array
    {
        return [
            'student_nim.required'        => 'NIM wajib diisi.',
            'student_nim.numeric'         => 'NIM hanya boleh berisi angka.',
            'student_nim.digits_between'  => 'NIM harus terdiri dari 8 hingga 12 digit.',
            'student_name.required'       => 'Nama mahasiswa wajib diisi.',
            'student_name.string'         => 'Nama mahasiswa harus berupa teks.',
            'student_name.min'            => 'Nama mahasiswa minimal 3 karakter.',
            'student_name.max'            => 'Nama mahasiswa maksimal 100 karakter.',
            'student_email.required'      => 'Email mahasiswa wajib diisi.',
            'student_email.email'         => 'Format email tidak valid.',
            'student_email.max'           => 'Email maksimal 150 karakter.',
            'course_id.required'          => 'Mata kuliah wajib dipilih.',
            'course_id.exists'            => 'Mata kuliah yang dipilih tidak ditemukan.',
            'academic_year.required'      => 'Tahun ajaran wajib diisi.',
            'academic_year.regex'         => 'Format tahun ajaran harus seperti 2025/2026.',
            'semester.required'           => 'Semester wajib dipilih.',
            'semester.in'                 => 'Semester hanya boleh Ganjil (1) atau Genap (2).',
            'status.required'             => 'Status wajib dipilih.',
            'status.in'                   => 'Status tidak valid.',
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Public wrapper untuk validateOnly — dipanggil dari Alpine via @this.call()
     * di dalam x-teleport dimana wire:model tidak bisa bekerja.
     */
    public function validateField(string $field): void
    {
        $allowed = ['student_nim', 'student_name', 'student_email', 'course_id', 'academic_year', 'semester', 'status'];
        if (in_array($field, $allowed)) {
            $this->validateOnly($field);
        }
    }

    // ─── Filter reactivity ─────────────────────────────────────────────────────
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }
    public function updatedFilterSemester(): void
    {
        $this->resetPage();
    }
    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }
    public function updatedFilterCourse(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search         = '';
        $this->filterStatus   = '';
        $this->filterSemester = '';
        $this->filterYear     = '';
        $this->filterCourse   = '';
        $this->filterOperator = 'AND';
        $this->sortBy         = 'enrollments.created_at';
        $this->sortDir        = 'desc';
        $this->perPage        = 15;
        $this->selectedRows   = [];
        $this->selectAll      = false;

        session()->forget(['export_token', 'csv_export_token']);
        $this->resetPage();
        $this->dispatch('notify', message: 'Data telah di-reset ke kondisi awal.', type: 'info');
    }

    // ─── Form helpers ──────────────────────────────────────────────────────────
    public function resetForm(): void
    {
        $this->reset([
            'selectedId',
            'student_nim',
            'student_name',
            'student_email',
            'course_id',
            'status',
            'editMode',
            'isDetailOpen',
            'isOpen',
        ]);
        $this->academic_year = '2025/2026';
        $this->semester      = '1';
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function showDetail($id): void
    {
        $this->selectedEnrollment = Enrollment::with(['student', 'course'])->findOrFail($id);
        $this->isDetailOpen       = true;
    }

    public function edit($id): void
    {
        $this->resetForm();
        $this->editMode = true;

        $enrollment = Enrollment::with('student')->findOrFail($id);

        $this->selectedId    = $id;
        $this->student_nim   = $enrollment->student->nim;
        $this->student_name  = $enrollment->student->name;
        $this->student_email = $enrollment->student->email;
        $this->course_id     = (string) $enrollment->course_id;
        $this->academic_year = $enrollment->academic_year;
        $this->semester      = (string) $enrollment->semester;
        $this->status        = $enrollment->status;
        $this->isOpen        = true;
    }

    public function store(): void
    {
        $this->validate();

        $existing = Student::where('email', $this->student_email)
            ->where('nim', '!=', $this->student_nim)
            ->first();

        if ($existing) {
            $this->addError('student_email', 'Email sudah digunakan oleh mahasiswa lain.');
            return;
        }

        try {
            DB::transaction(function () {
                $student = Student::updateOrCreate(
                    ['nim' => $this->student_nim],
                    ['name' => $this->student_name, 'email' => $this->student_email]
                );

                $query = Enrollment::where([
                    'student_id'    => $student->id,
                    'course_id'     => $this->course_id,
                    'academic_year' => $this->academic_year,
                    'semester'      => $this->semester,
                ]);

                if ($this->editMode) {
                    $query->where('id', '!=', $this->selectedId);
                }

                if ($query->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'course_id' => 'Mahasiswa sudah mengambil mata kuliah ini pada periode tersebut.',
                    ]);
                }

                $payload = [
                    'student_id'    => $student->id,
                    'course_id'     => $this->course_id,
                    'academic_year' => $this->academic_year,
                    'semester'      => $this->semester,
                    'status'        => $this->status,
                ];

                if ($this->editMode) {
                    Enrollment::find($this->selectedId)->update($payload);
                    $msg = 'Data KRS berhasil diperbarui!';
                } else {
                    Enrollment::create($payload);
                    $msg = 'Data KRS berhasil ditambahkan!';
                }

                $this->dispatch('notify', message: $msg, type: 'success');
                $this->isOpen = false;
                $this->resetForm();
            });
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) throw $e;
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // ─── Sorting ───────────────────────────────────────────────────────────────
    public function setSort($field): void
    {
        $actualField = in_array($field, self::ALLOWED_SORT_COLUMNS, true)
            ? $field : 'enrollments.created_at';

        if ($this->sortBy === $actualField) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $actualField;
            $this->sortDir = 'asc';
        }
    }

    /**
     * Core Query Builder — dioptimalkan untuk PostgreSQL.
     */
    private function applyQuery()
    {
        $query = Enrollment::query()
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id');

        $query->when($this->search, function ($q) {
            $searchTerm = trim($this->search);
            $q->where(function ($s) use ($searchTerm) {
                $s->where('students.nim', 'ilike', $searchTerm . '%')
                    ->orWhere('students.name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('courses.code', 'ilike', $searchTerm . '%')
                    ->orWhere('courses.name', 'ilike', '%' . $searchTerm . '%');
            });
        });

        $query->where(function ($q) {
            $isOr   = $this->filterOperator === 'OR';
            $method = $isOr ? 'orWhere' : 'where';

            if ($this->filterStatus)   $q->where('enrollments.status', $this->filterStatus);
            if ($this->filterYear)     $q->$method('enrollments.academic_year', $this->filterYear);
            if ($this->filterCourse)   $q->$method('courses.code', $this->filterCourse);
            if ($this->filterSemester) {
                $semesterValue = $this->filterSemester === 'GANJIL' ? 1 : 2;
                $q->$method('enrollments.semester', $semesterValue);
            }
        });

        return $query;
    }

    // ─── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        $baseQuery = $this->applyQuery()
            ->select([
                'enrollments.id',
                'enrollments.student_id',
                'enrollments.course_id',
                'enrollments.academic_year',
                'enrollments.semester',
                'enrollments.status',
                'enrollments.created_at',
                'enrollments.updated_at',
                'enrollments.deleted_at',
                'students.nim  as student_nim',
                'students.name as student_name',
                'courses.code  as course_code',
                'courses.name  as course_name',
            ])
            ->orderBy($this->sortBy, $this->sortDir);

        if ($this->perPage === 0) {
            $allItems    = $baseQuery->get();
            $total       = $allItems->count();
            $enrollments = new \Illuminate\Pagination\LengthAwarePaginator(
                $allItems,
                $total,
                max($total, 1),
                1,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } else {
            $enrollments = $baseQuery->paginate($this->perPage);
        }

        return view('livewire.enrollment-manager', [
            'enrollments'  => $enrollments,
            'courses_list' => \App\Models\Course::orderBy('code')->get(),
            'years_list'   => \App\Models\Enrollment::distinct()
                ->orderBy('academic_year', 'desc')
                ->pluck('academic_year'),
            'trashedCount' => \App\Models\Enrollment::onlyTrashed()->count(),
        ])
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs
            ])
            ->title('Enrollment Management');
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    // =========================================================================
    // TS-13: EXPORT 5 JUTA DATA — CSV STREAMING + XLSX ASYNC JOB
    // =========================================================================

    /**
     * prepareExportCsv()
     * ─────────────────────────────────────────────────────────────────────────
     * Simpan snapshot filter ke Cache (bukan session — agar tidak ada session
     * lock saat controller membaca di request terpisah), lalu dispatch event
     * JS 'csv-ready' yang memicu browser membuka URL download.
     *
     * Download sesungguhnya terjadi di EnrollmentExportController::csv()
     * via response()->streamDownload() — tidak terbatas ukuran file.
     */
    public function prepareExportCsv(string $scope = 'all'): void
    {
        try {
            $filters = $this->buildFilterSnapshot($scope);
            $token   = \Illuminate\Support\Str::random(40);

            // TTL 5 menit — cukup untuk browser membuka URL download
            Cache::put("csv_token_{$token}", $filters, now()->addMinutes(5));

            $downloadUrl = route('enrollments.export.csv', ['token' => $token]);

            $this->dispatch('csv-ready', url: $downloadUrl, file: 'enrollments_export.csv');
        } catch (\Exception $e) {
            Log::error('[prepareExportCsv] ' . $e->getMessage());
            $this->dispatch('export-error', message: 'Gagal menyiapkan CSV: ' . $e->getMessage());
        }
    }

    /**
     * prepareExportXlsx()
     * ─────────────────────────────────────────────────────────────────────────
     * Untuk scope 'all' — proses berjalan di background queue job (ExportEnrollmentsJob).
     * Simpan filter ke Cache, lalu dispatch event JS 'xlsx-job-ready' yang
     * memicu frontend mengirim POST ke /enrollments/export/xlsx-dispatch.
     *
     * Mengapa POST dan bukan GET?
     * Laravel melindungi semua route non-GET/non-HEAD dengan verifikasi CSRF.
     * GET akan kena 419 Page Expired. Frontend harus menyertakan X-CSRF-TOKEN header.
     */
    public function prepareExportXlsx(string $scope = 'all'): void
    {
        try {
            $filters = $this->buildFilterSnapshot($scope);
            $token   = \Illuminate\Support\Str::random(40);

            // TTL 10 menit — cukup untuk user mengklik dispatch dari frontend
            Cache::put("xlsx_job_token_{$token}", $filters, now()->addMinutes(10));

            $this->dispatch('xlsx-job-ready', [
                'token'        => $token,
                'dispatch_url' => route('enrollments.export.xlsx-dispatch', ['token' => $token]),
                'status_url'   => route('enrollments.export.xlsx-status'),
            ]);
        } catch (\Exception $e) {
            Log::error('[prepareExportXlsx] ' . $e->getMessage());
            $this->dispatch('export-error', message: 'Gagal menyiapkan XLSX: ' . $e->getMessage());
        }
    }

    /**
     * exportXlsxDirect()
     * ─────────────────────────────────────────────────────────────────────────
     * Untuk scope 'current_page' atau 'selected' — jumlah data terbatas,
     * download langsung via controller (EnrollmentExportController::xlsxDirect())
     * tanpa queue. Tetap menggunakan keyset pagination di controller agar
     * tidak OOM meskipun halaman menampilkan banyak baris.
     */
    public function exportXlsxDirect(string $scope = 'all'): void
    {
        try {
            $filters = $this->buildFilterSnapshot($scope);
            $token   = \Illuminate\Support\Str::random(32);

            Cache::put("xlsx_export_{$token}", [
                'filters'    => $filters,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ], now()->addMinutes(10));

            $downloadUrl = route('enrollments.export.xlsx-direct', ['token' => $token]);

            // Gunakan event 'csv-ready' yang sama — frontend hanya perlu trigger download
            $this->dispatch('csv-ready', url: $downloadUrl, file: 'enrollments_export.zip');
        } catch (\Exception $e) {
            Log::error('[exportXlsxDirect] ' . $e->getMessage());
            $this->dispatch('export-error', message: 'Gagal menyiapkan XLSX: ' . $e->getMessage());
        }
    }

    // ─── Helper: snapshot filter aktif ────────────────────────────────────────

    /**
     * buildFilterSnapshot()
     * ─────────────────────────────────────────────────────────────────────────
     * Kemas semua filter + sorting ke satu array yang dapat disimpan di Cache
     * dan dibaca oleh controller maupun Job di request/process terpisah.
     *
     * @param string $scope  'all' | 'selected' | 'current_page'
     */
    private function buildFilterSnapshot(string $scope = 'all'): array
    {
        $snapshot = [
            'search'         => $this->search,
            'filterStatus'   => $this->filterStatus,
            'filterSemester' => $this->filterSemester,
            'filterYear'     => $this->filterYear,
            'filterCourse'   => $this->filterCourse,
            'filterOperator' => $this->filterOperator,
            'sortBy'         => $this->sortBy,
            'sortDir'        => $this->sortDir,
            'selected_ids'   => [],
        ];

        if ($scope === 'selected' && count($this->selectedRows) > 0) {
            // Cast ke int untuk keamanan sebelum disimpan
            $snapshot['selected_ids'] = array_map('intval', $this->selectedRows);
        } elseif ($scope === 'current_page') {
            $query = $this->applyQuery()
                ->select('enrollments.id')
                ->orderBy($this->sortBy, $this->sortDir);

            $snapshot['selected_ids'] = ($this->perPage > 0)
                ? $query->paginate($this->perPage)->pluck('id')->map(fn($id) => (int) $id)->toArray()
                : $query->pluck('enrollments.id')->map(fn($id) => (int) $id)->toArray();
        }

        return $snapshot;
    }

    /**
     * buildExportQuery() — static, dipakai controller & Job
     * ─────────────────────────────────────────────────────────────────────────
     * Single source of truth untuk query ekspor via Eloquent builder.
     * Dipakai ketika dibutuhkan Eloquent query (bukan raw PDO).
     */
    public static function buildExportQuery(array $f)
    {
        $query = Enrollment::query()
            ->toBase()
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select([
                'enrollments.id',
                'students.nim',
                'students.name  as student_name',
                'courses.code   as course_code',
                'courses.name   as course_name',
                'enrollments.academic_year',
                'enrollments.semester',
                'enrollments.status',
                'enrollments.created_at',
            ]);

        if (!empty($f['selected_ids'])) {
            $query->whereIn('enrollments.id', $f['selected_ids']);

            if (!empty($f['sortBy'])) {
                $query->orderBy($f['sortBy'], $f['sortDir'] ?? 'asc');
            }
            return $query;
        }

        if (!empty($f['search'])) {
            $search = $f['search'];
            $query->where(function ($q) use ($search) {
                $q->where('students.nim', 'ilike', "$search%")
                    ->orWhere('students.name', 'ilike', "%$search%")
                    ->orWhere('courses.code', 'ilike', "$search%")
                    ->orWhere('courses.name', 'ilike', "%$search%");
            });
        }

        $query->where(function ($q) use ($f) {
            $isOr   = ($f['filterOperator'] ?? 'AND') === 'OR';
            $method = $isOr ? 'orWhere' : 'where';

            if (!empty($f['filterStatus'])) {
                $q->where('enrollments.status', $f['filterStatus']);
            }
            if (!empty($f['filterYear'])) {
                $q->$method('enrollments.academic_year', $f['filterYear']);
            }
            if (!empty($f['filterCourse'])) {
                $q->$method('courses.code', $f['filterCourse']);
            }
            if (!empty($f['filterSemester'])) {
                $val = ($f['filterSemester'] === 'GANJIL') ? 1 : 2;
                $q->$method('enrollments.semester', $val);
            }
        });

        if (!empty($f['sortBy'])) {
            $query->orderBy($f['sortBy'], $f['sortDir'] ?? 'desc');
        } else {
            $query->orderBy('enrollments.created_at', 'desc');
        }

        return $query;
    }

    /**
     * buildRawWhereClause()
     * ─────────────────────────────────────────────────────────────────────────
     * Menghasilkan SQL WHERE clause + bindings array dari filter snapshot.
     * Dipakai oleh EnrollmentExportController dan ExportEnrollmentsJob
     * untuk raw PDO query dengan positional binding ($1, $2, ...).
     *
     * Return: [$whereSql, $bindings]
     *   $whereSql  = string "WHERE ..." atau "" jika tidak ada filter
     *   $bindings  = array nilai untuk prepared statement (positional: $1, $2, ...)
     *
     * PENTING:
     *  - selected_ids di-handle di level controller/job secara terpisah (? placeholder)
     *  - Method ini selalu menambahkan e.deleted_at IS NULL sebagai base condition
     */
    public static function buildRawWhereClause(array $f): array
    {
        $conditions = [];
        $bindings   = [];

        // Base condition — pastikan data ter-soft-delete tidak ikut di export
        $conditions[] = 'e.deleted_at IS NULL';

        if (!empty($f['search'])) {
            $val = trim($f['search']);
            $b1  = count($bindings) + 1;
            $b2  = count($bindings) + 2;
            $conditions[] = "(s.nim ILIKE \${$b1} OR s.name ILIKE \${$b2})";
            $bindings[]   = $val . '%';
            $bindings[]   = '%' . $val . '%';
        }

        // Filter-filter lain (status, year, course, semester)
        $filterMap = [
            'filterStatus'   => 'e.status',
            'filterYear'     => 'e.academic_year',
            'filterCourse'   => 'c.code',
            'filterSemester' => 'e.semester',
        ];

        $filterClauses = [];
        foreach ($filterMap as $key => $col) {
            if (empty($f[$key])) continue;

            $val = $f[$key];
            if ($key === 'filterSemester') {
                $val = ($val === 'GANJIL' || (string) $val === '1') ? 1 : 2;
            }

            $b               = count($bindings) + 1;
            $filterClauses[] = "{$col} = \${$b}";
            $bindings[]      = $val;
        }

        if (!empty($filterClauses)) {
            $glue         = ($f['filterOperator'] ?? 'AND') === 'OR' ? ' OR ' : ' AND ';
            $conditions[] = '(' . implode($glue, $filterClauses) . ')';
        }

        $whereSql = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        return [$whereSql, $bindings];
    }

    /**
     * interpolateBindings()
     * ─────────────────────────────────────────────────────────────────────────
     * Interpolasi positional bindings ($1, $2, ...) ke SQL string.
     * HANYA dipakai untuk COPY TO STDOUT yang tidak support prepared statement.
     */
    public static function interpolateBindings(string $sql, array $bindings): string
    {
        foreach ($bindings as $i => $val) {
            $placeholder = '$' . ($i + 1);
            if (is_int($val)) {
                $escaped = (string) $val;
            } elseif (is_null($val)) {
                $escaped = 'NULL';
            } else {
                $escaped = function_exists('pg_escape_literal')
                    ? pg_escape_literal((string) $val)
                    : "'" . addslashes((string) $val) . "'";
            }
            $sql = str_replace($placeholder, $escaped, $sql);
        }
        return $sql;
    }

    /**
     * buildPgConnString()
     * ─────────────────────────────────────────────────────────────────────────
     * Buat connection string PostgreSQL native dari config Laravel.
     */
    public static function buildPgConnString(): string
    {
        $cfg   = config('database.connections.' . config('database.default'));
        $parts = [
            'host='     . ($cfg['host']     ?? 'localhost'),
            'port='     . ($cfg['port']     ?? 5432),
            'dbname='   . ($cfg['database'] ?? ''),
            'user='     . ($cfg['username'] ?? ''),
            'password=' . ($cfg['password'] ?? ''),
        ];
        if (!empty($cfg['sslmode'])) {
            $parts[] = 'sslmode=' . $cfg['sslmode'];
        }
        return implode(' ', $parts);
    }

    // ─── Soft Delete ───────────────────────────────────────────────────────────
    public function confirmDelete($id): void
    {
        $this->dispatch('confirm-delete', id: $id, message: 'Data KRS akan dipindahkan ke Sampah.');
    }

    public function confirmBulkDelete(): void
    {
        $count = count($this->selectedRows);
        $this->dispatch('confirm-delete', id: null, message: "Pindahkan {$count} data KRS ke Sampah?");
    }

    #[On('delete-confirmed')]
    public function delete($id = null): void
    {
        DB::beginTransaction();

        try {
            if ($id) {
                Enrollment::destroy($id);
                $msg = 'Data KRS dipindahkan ke Sampah!';
            } else {
                Enrollment::whereIn('id', $this->selectedRows)->delete();
                $this->selectedRows = [];
                $this->selectAll    = false;
                $msg = 'Semua data KRS terpilih dipindahkan ke Sampah!';
            }

            DB::commit();
            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('notify', message: 'Gagal memindahkan data ke Sampah!', type: 'error');
        }
    }

    public function resetSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    public function confirmTrash($id = null): void
    {
        $count = count($this->selectedRows);
        $msg   = $id
            ? 'Pindahkan data KRS ini ke keranjang sampah?'
            : "Pindahkan {$count} data KRS terpilih ke keranjang sampah?";

        $this->dispatch('confirm-trash', id: $id, message: $msg);
    }

    #[On('trash-confirmed')]
    public function trash($id = null): void
    {
        DB::beginTransaction();

        try {
            if ($id) {
                Enrollment::destroy($id);
                $msg = 'Data KRS dipindahkan ke Sampah!';
            } else {
                Enrollment::whereIn('id', $this->selectedRows)->delete();
                $msg = count($this->selectedRows) . ' data KRS terpilih dipindahkan ke Sampah!';
                $this->resetSelection();
            }

            DB::commit();
            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('notify', message: 'Gagal memindahkan data ke Sampah!', type: 'error');
        }
    }

    public function getIsFilteredProperty()
    {
        return !empty($this->search)
            || !empty($this->filterStatus)
            || !empty($this->filterCourse);
    }
}
