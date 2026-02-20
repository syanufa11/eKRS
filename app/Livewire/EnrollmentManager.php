<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Livewire\Traits\WithBreadcrumbs;

class EnrollmentManager extends Component
{
    use WithPagination, WithBreadcrumbs;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'students.nim';

    #[Url(history: true)]
    public $sortDir = 'asc';

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

    #[Url(history: true)]
    public $perPage = 15;

    #[Validate]
    public $student_nim;

    #[Validate]
    public $student_name;

    #[Validate]
    public $student_email;

    #[Validate]
    public $course_id;

    #[Validate]
    public $academic_year = '2025/2026';

    #[Validate]
    public $semester = '1';

    #[Validate]
    public $status = 'DRAFT';

    public $selectedRows = [];
    public $selectAll    = false;

    private array $sortableFields = [
        'students.nim',
        'students.name',
        'courses.code',
        'courses.name',
        'enrollments.academic_year',
        'enrollments.semester',
        'enrollments.status',
        'enrollments.created_at',
    ];

    public function mount(): void
    {
        $this->breadcrumb('Enrollment Management');
    }

    // ─── Aturan Validasi ───────────────────────────────────────────────────────
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

    // ─── Pesan Validasi Bahasa Indonesia ──────────────────────────────────────
    protected function messages(): array
    {
        return [
            'student_nim.required'        => 'NIM wajib diisi.',
            'student_nim.numeric'         => 'NIM harus berupa angka.',
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

            'academic_year.required'      => 'Tahun akademik wajib diisi.',
            'academic_year.regex'         => 'Format tahun akademik tidak valid. Gunakan format: 2025/2026.',

            'semester.required'           => 'Semester wajib dipilih.',
            'semester.in'                 => 'Semester tidak valid. Pilih Ganjil (1) atau Genap (2).',

            'status.required'             => 'Status wajib dipilih.',
            'status.in'                   => 'Status tidak valid. Pilih: Draft, Submitted, Approved, atau Rejected.',
        ];
    }

    // ─── Pagination & Selection ────────────────────────────────────────────────
    public function updatingPage(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->dispatch('notify', message: 'Tampilan per halaman diperbarui.', type: 'info');
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = $this->applyQuery()
                ->select('enrollments.id')
                ->orderBy($this->sortBy, $this->sortDir)
                ->forPage($this->getPage(), $this->perPage)
                ->pluck('enrollments.id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function updatedSortBy(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
        $this->resetPage();
    }

    public function updatedSortDir(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
        $this->resetPage();
    }

    // ─── Validation Helper ────────────────────────────────────────────────────
    public function validateField($field): void
    {
        $this->validateOnly($field);
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
        $this->reset(['search', 'filterStatus', 'filterSemester', 'filterYear', 'filterCourse']);
        $this->sortBy       = 'students.nim';
        $this->sortDir      = 'asc';
        $this->perPage      = 15;
        $this->selectedRows = [];
        $this->selectAll    = false;
        $this->resetPage();
        $this->dispatch('notify', message: 'Filter berhasil direset.', type: 'info');
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
        $this->status        = 'DRAFT';
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
        if (!in_array($field, $this->sortableFields)) return;

        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $field;
            $this->sortDir = 'asc';
        }

        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    // ─── Base query builder ────────────────────────────────────────────────────
    private function applyQuery()
    {
        return Enrollment::query()
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $search = trim($this->search);
                    $q->where('students.nim', 'ilike', $search . '%')
                        ->orWhere('students.name', 'ilike', '%' . $search . '%')
                        ->orWhere('courses.code', 'ilike', $search . '%')
                        ->orWhere('courses.name', 'ilike', '%' . $search . '%');
                });
            })
            ->where(function ($query) {
                $isOr   = $this->filterOperator === 'OR';
                $method = $isOr ? 'orWhere' : 'where';

                if ($this->filterStatus) {
                    $query->$method('enrollments.status', $this->filterStatus);
                }

                if ($this->filterYear) {
                    $query->$method('enrollments.academic_year', $this->filterYear);
                }

                if ($this->filterCourse) {
                    $query->$method('courses.code', $this->filterCourse);
                }

                if ($this->filterSemester) {
                    $val = $this->filterSemester === 'GANJIL' ? '1' : '2';
                    $query->$method('enrollments.semester', $val);
                }
            });
    }

    // ─── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.enrollment-manager', [
            'enrollments'  => $this->applyQuery()
                ->select(
                    'enrollments.*',
                    'students.nim  as student_nim',
                    'students.name as student_name',
                    'courses.code  as course_code',
                    'courses.name  as course_name'
                )
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'courses_list' => Course::orderBy('code')->get(),
            'years_list'   => Enrollment::distinct()->orderBy('academic_year', 'desc')->pluck('academic_year'),
            'trashedCount' => Enrollment::onlyTrashed()->count(),
        ])->layout('layouts.app', [
            'breadcrumbs' => $this->breadcrumbs
        ])->title('Enrollment Management');
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    // ─── Selection ────────────────────────────────────────────────────────────

    /**
     * Toggle individual row — dipanggil dari Alpine x-on:change di blade.
     * Selalu pakai string agar konsisten dengan selectedRows array.
     */
    public function toggleRow(string $id): void
    {
        if (in_array($id, $this->selectedRows)) {
            $this->selectedRows = array_values(array_filter($this->selectedRows, fn($r) => $r !== $id));
            $this->selectAll    = false;
        } else {
            $this->selectedRows[] = $id;
        }
    }

    public function resetSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    // ─── Export Handling ───────────────────────────────────────────────────────
    public function prepareExportCsv($scope = 'all'): void
    {
        try {
            $filters = $this->buildFilterSnapshot();

            match ($scope) {
                'all' => $filters = [
                    'search'         => '',
                    'filterStatus'   => '',
                    'filterSemester' => '',
                    'filterYear'     => '',
                    'filterCourse'   => '',
                    'filterOperator' => 'AND',
                    'sortBy'         => 'students.nim',
                    'sortDir'        => 'asc',
                ],

                'filtered' => null,

                'page' => (function () use (&$filters) {
                    $filters['selected_ids'] = $this->applyQuery()
                        ->select('enrollments.id')
                        ->orderBy($this->sortBy, $this->sortDir)
                        ->forPage($this->getPage(), $this->perPage)
                        ->pluck('enrollments.id')
                        ->toArray();
                })(),

                'selected' => (function () use (&$filters) {
                    if (empty($this->selectedRows)) {
                        $this->dispatch('notify', message: 'Pilih data atau baris terlebih dahulu!', type: 'error');
                        throw new \RuntimeException('__CANCEL__');
                    }
                    $filters['selected_ids'] = $this->selectedRows;
                })(),

                default => null,
            };

            $token = \Illuminate\Support\Str::random(32);
            \Illuminate\Support\Facades\Cache::put("csv_export_{$token}", [
                'filters'    => $filters,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ], now()->addMinutes(10));

            $suffixMap = [
                'all'      => 'semua',
                'filtered' => 'filter',
                'page'     => 'halaman',
                'selected' => 'terpilih',
            ];
            $fileName = 'enrollments_' . ($suffixMap[$scope] ?? $scope) . '_' . now()->format('Ymd_His') . '.csv';

            $this->dispatch('csv-ready', url: route('enrollments.export.csv', ['token' => $token]), file: $fileName);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== '__CANCEL__') {
                $this->dispatch('export-error', message: 'Gagal menyiapkan ekspor: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->dispatch('export-error', message: 'Gagal menyiapkan ekspor: ' . $e->getMessage());
        }
    }

    private function buildFilterSnapshot(): array
    {
        return [
            'search'         => $this->search,
            'filterStatus'   => $this->filterStatus,
            'filterSemester' => $this->filterSemester,
            'filterYear'     => $this->filterYear,
            'filterCourse'   => $this->filterCourse,
            'filterOperator' => $this->filterOperator,
            'sortBy'         => $this->sortBy,
            'sortDir'        => $this->sortDir,
        ];
    }

    public static function buildExportQuery(array $f)
    {
        $where    = ['enrollments.deleted_at IS NULL'];
        $bindings = [];

        if (!empty($f['selected_ids'])) {
            $ids          = array_map('intval', (array) $f['selected_ids']);
            $placeholders = implode(',', $ids);
            $where[]      = "enrollments.id IN ({$placeholders})";

            return self::rawExportQuery(implode(' AND ', $where), $bindings);
        }

        if (!empty($f['search'])) {
            $s      = trim($f['search']);
            $sLike  = '%' . $s . '%';
            $sStart = $s . '%';
            $where[]    = "(students.nim ILIKE ? OR students.name ILIKE ? OR courses.code ILIKE ? OR courses.name ILIKE ?)";
            $bindings[] = $sStart;
            $bindings[] = $sLike;
            $bindings[] = $sStart;
            $bindings[] = $sLike;
        }

        $isOr  = ($f['filterOperator'] ?? 'AND') === 'OR';
        $glue  = $isOr ? ' OR ' : ' AND ';
        $parts = [];

        if (!empty($f['filterStatus'])) {
            $parts[]    = "enrollments.status = ?";
            $bindings[] = $f['filterStatus'];
        }
        if (!empty($f['filterYear'])) {
            $parts[]    = "enrollments.academic_year = ?";
            $bindings[] = $f['filterYear'];
        }
        if (!empty($f['filterCourse'])) {
            $parts[]    = "courses.code = ?";
            $bindings[] = $f['filterCourse'];
        }
        if (!empty($f['filterSemester'])) {
            $parts[]    = "enrollments.semester = ?";
            $bindings[] = $f['filterSemester'] === 'GANJIL' ? '1' : '2';
        }

        if (!empty($parts)) {
            $where[] = '(' . implode($glue, $parts) . ')';
        }

        return self::rawExportQuery(implode(' AND ', $where), $bindings);
    }

    private static function rawExportQuery(string $whereClause, array $bindings)
    {
        return new \Illuminate\Support\LazyCollection(function () use ($whereClause, $bindings) {
            $chunkSize = 10000;
            $lastId    = 0;

            $keySql = "
                SELECT
                    enrollments.id,
                    students.nim,
                    students.name        AS student_name,
                    courses.code         AS course_code,
                    courses.name         AS course_name,
                    enrollments.academic_year,
                    enrollments.semester,
                    enrollments.status,
                    enrollments.created_at
                FROM enrollments
                INNER JOIN students ON enrollments.student_id = students.id
                INNER JOIN courses  ON enrollments.course_id  = courses.id
                WHERE ({$whereClause}) AND enrollments.id > ?
                ORDER BY enrollments.id ASC
                LIMIT {$chunkSize}
            ";

            while (true) {
                $rows = DB::select($keySql, array_merge(array_values($bindings), [$lastId]));

                if (empty($rows)) {
                    break;
                }

                foreach ($rows as $row) {
                    yield $row;
                }

                $lastId = end($rows)->id;

                if (count($rows) < $chunkSize) {
                    break;
                }
            }
        });
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
        if ($id) {
            Enrollment::destroy($id);
            $msg = 'Data KRS dipindahkan ke Sampah!';
        } else {
            Enrollment::whereIn('id', $this->selectedRows)->delete();
            $this->selectedRows = [];
            $this->selectAll    = false;
            $msg = 'Semua data KRS terpilih dipindahkan ke Sampah!';
        }
        $this->dispatch('notify', message: $msg, type: 'success');
    }

    public function confirmTrash($id = null): void
    {
        $count = count($this->selectedRows);
        $msg   = $id ? 'Pindahkan data KRS ini ke keranjang sampah?' : "Pindahkan {$count} data KRS terpilih ke keranjang sampah?";
        $this->dispatch('confirm-trash', id: $id, message: $msg);
    }

    #[On('trash-confirmed')]
    public function trash($id = null): void
    {
        if ($id) {
            Enrollment::destroy($id);
            $msg = 'Data KRS dipindahkan ke Sampah!';
        } else {
            Enrollment::whereIn('id', $this->selectedRows)->delete();
            $msg = count($this->selectedRows) . ' data KRS terpilih dipindahkan ke Sampah!';
            $this->resetSelection();
        }
        $this->dispatch('notify', message: $msg, type: 'success');
    }
}
