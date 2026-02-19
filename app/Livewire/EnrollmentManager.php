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
use App\Jobs\ExportEnrollmentsJob;
use App\Livewire\Traits\WithBreadcrumbs;

class EnrollmentManager extends Component
{
    use WithPagination, WithBreadcrumbs;

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

    #[Url(history: true)]
    public $perPage = 15; // Properti baru untuk limit halaman

    // ─── Validasi menggunakan Atribut Livewire 3 ───────────────────────────
    #[Validate('required|numeric|digits_between:8,12')]
    public $student_nim;

    #[Validate('required|string|min:3|max:100')]
    public $student_name;

    #[Validate('required|email|max:150')]
    public $student_email;

    #[Validate('required|exists:courses,id')]
    public $course_id;

    #[Validate(['required', 'regex:/^\d{4}\/\d{4}$/'])]
    public $academic_year = '2025/2026';

    #[Validate('required|in:1,2')]
    public $semester = '1';

    #[Validate('required|in:DRAFT,SUBMITTED,APPROVED,REJECTED')]
    public $status = 'DRAFT';

    public $selectedRows = [];
    public $selectAll    = false;
    public $exportJobDispatched = false;

    public function mount()
    {
        $this->breadcrumb('Enrollment Management');
    }

    // ─── Pagination & Selection ────────────────────────────────────────────────
    public function updatingPage(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = $this->applyQuery()
                ->select('enrollments.id')
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate(15)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // ─── Validation Helper (Dipanggil dari Blade/Alpine) ───────────────────────
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
        $this->resetPage();
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
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $field;
            $this->sortDir = 'asc';
        }
    }

    // ─── Base query builder ────────────────────────────────────────────────────
    // ─── Base query builder ────────────────────────────────────────────────────
    private function applyQuery()
    {
        return Enrollment::query()
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            // Scope pencarian utama
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $search = trim($this->search);
                    // PostgreSQL menggunakan ILIKE untuk case-insensitive
                    $q->where('students.nim', 'ilike', $search . '%')
                        ->orWhere('students.name', 'ilike', '%' . $search . '%')
                        ->orWhere('courses.code', 'ilike', $search . '%')
                        ->orWhere('courses.name', 'ilike', '%' . $search . '%');
                });
            })
            // Scope filter tambahan (Status, Semester, dll)
            ->where(function ($query) {
                $isOr = $this->filterOperator === 'OR';
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
                ->paginate($this->perPage), // Gunakan variabel dinamis
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

    // ─── Export Handling ───────────────────────────────────────────────────────
    // Tambahkan properti untuk loading state
    public $isExporting = false;

    /**
     * Menyiapkan token ekspor berdasarkan cakupan (scope)
     * Scope: 'all' (semua filter), 'current_page' (hanya page ini), 'selected' (checkbox)
     */
    /**
     * Menyiapkan token ekspor yang adaptif terhadap filter dan scope.
     * Scope: 'all' (semua filter), 'selected' (halaman/baris terpilih)
     */
    public function prepareExportCsv($scope = 'all'): void
    {
        try {
            // Ambil snapshot filter saat ini
            $filters = $this->buildFilterSnapshot();

            // Tambahkan konteks pagination dan seleksi
            $filters['perPage'] = $this->perPage;

            if ($scope === 'selected') {
                if (empty($this->selectedRows)) {
                    $this->dispatch('notify', message: 'Pilih data atau baris terlebih dahulu!', type: 'error');
                    return;
                }
                // Kirim ID spesifik yang dicentang
                $filters['selected_ids'] = $this->selectedRows;
            }

            $token = \Illuminate\Support\Str::random(32);
            session()->put("csv_export_{$token}", [
                'filters'    => $filters,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ]);

            $downloadUrl = route('enrollments.export.csv', ['token' => $token]);
            $fileName    = 'enrollments_' . ($scope === 'selected' ? 'selected_' : 'filtered_') . now()->format('Ymd_His') . '.csv';

            $this->dispatch('csv-ready', url: $downloadUrl, file: $fileName);
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

    /**
     * Membangun query dasar untuk ekspor CSV/XLSX.
     * Mendukung ekspor berdasarkan filter aktif atau baris yang dipilih secara spesifik.
     */
    public static function buildExportQuery(array $f)
    {
        $query = Enrollment::query()
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('courses',  'enrollments.course_id',  '=', 'courses.id');

        // 1. Prioritas: ID Terpilih (Bulk Action atau Per Page)
        if (!empty($f['selected_ids'])) {
            $query->whereIn('enrollments.id', $f['selected_ids']);
        } else {
            // 2. Filter Pencarian
            if (!empty($f['search'])) {
                $search = trim($f['search']);
                $query->where(function ($q) use ($search) {
                    $q->where('students.nim', 'ilike', $search . '%')
                        ->orWhere('students.name', 'ilike', '%' . $search . '%')
                        ->orWhere('courses.code', 'ilike', $search . '%')
                        ->orWhere('courses.name', 'ilike', '%' . $search . '%');
                });
            }

            // 3. Filter Dropdown (Status, Tahun, Semester)
            $query->where(function ($q) use ($f) {
                $isOr = ($f['filterOperator'] ?? 'AND') === 'OR';
                $method = $isOr ? 'orWhere' : 'where';

                if (!empty($f['filterStatus']))   $q->$method('enrollments.status', $f['filterStatus']);
                if (!empty($f['filterYear']))     $q->$method('enrollments.academic_year', $f['filterYear']);
                if (!empty($f['filterCourse']))   $q->$method('courses.code', $f['filterCourse']);
                if (!empty($f['filterSemester'])) {
                    $val = $f['filterSemester'] === 'GANJIL' ? '1' : '2';
                    $q->$method('enrollments.semester', $val);
                }
            });
        }

        return $query->select([
            'students.nim',
            'students.name as student_name',
            'courses.code as course_code',
            'courses.name as course_name',
            'enrollments.academic_year',
            'enrollments.semester',
            'enrollments.status',
            'enrollments.created_at',
        ])
            ->orderBy($f['sortBy'] ?? 'enrollments.created_at', $f['sortDir'] ?? 'desc');
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

    public function resetSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
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
