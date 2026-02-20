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
            // Gunakan $this->perPage agar konsisten dengan tampilan aktif
            $this->selectedRows = $this->applyQuery()
                ->select('enrollments.id')
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
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
     * Menyiapkan token ekspor.
     *
     * Scope yang tersedia:
     *   'all'      → seluruh data (tanpa filter apapun)
     *   'filtered' → hanya data sesuai filter/search aktif
     *   'page'     → hanya baris pada halaman yang sedang ditampilkan
     *   'selected' → hanya baris yang dicentang (checkbox)
     */
    public function prepareExportCsv($scope = 'all'): void
    {
        try {
            $filters = $this->buildFilterSnapshot();

            match ($scope) {
                // ── Export Semua: buang semua filter ──────────────────────────
                'all' => $filters = [
                    'search'         => '',
                    'filterStatus'   => '',
                    'filterSemester' => '',
                    'filterYear'     => '',
                    'filterCourse'   => '',
                    'filterOperator' => 'AND',
                    'sortBy'         => 'enrollments.id',
                    'sortDir'        => 'desc',
                ],

                // ── Export Filter/Search: pakai snapshot filter aktif saat ini ─
                'filtered' => null, // $filters sudah terisi dari buildFilterSnapshot()

                // ── Export Halaman Ini: ambil ID baris di page sekarang ────────
                // Filter aktif ($filters) tetap dipakai, hanya batasi ke ID halaman ini
                'page' => (function () use (&$filters) {
                    $filters['selected_ids'] = $this->applyQuery()
                        ->select('enrollments.id')
                        ->orderBy($this->sortBy, $this->sortDir)
                        ->forPage($this->getPage(), $this->perPage)
                        ->pluck('enrollments.id')
                        ->toArray();
                })(),

                // ── Export Terpilih: ID dari checkbox ─────────────────────────
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
            // Gunakan Cache bukan Session — lebih reliable di shared hosting
            // karena tidak bergantung pada cookie browser atau session driver.
            // Cache key di-prefix 'csv_export_' dan expire 10 menit.
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

    /**
     * Membangun query dasar untuk ekspor CSV/XLSX.
     * Mendukung ekspor berdasarkan filter aktif atau baris yang dipilih secara spesifik.
     */
    /**
     * Membangun query export murni menggunakan raw PDO cursor via LazyCollection.
     *
     * Scope:
     *   selected_ids → whereIn (page/selected/checkbox)
     *   filter aktif → search + dropdown filter
     *   kosong semua → SELURUH data (no WHERE)
     */
    public static function buildExportQuery(array $f)
    {
        // Gunakan positional bindings (?) — array_values() wajib agar urutan index selalu 0,1,2...
        $where    = ['enrollments.deleted_at IS NULL'];
        $bindings = [];

        // ── Scope 1: ID spesifik (page / selected) ──────────────────────────
        if (!empty($f['selected_ids'])) {
            $ids          = array_map('intval', (array) $f['selected_ids']);
            $placeholders = implode(',', $ids); // integer literal, aman tanpa binding
            $where[]      = "enrollments.id IN ({$placeholders})";

            return self::rawExportQuery(implode(' AND ', $where), $bindings, $f['sortBy'] ?? 'enrollments.id', $f['sortDir'] ?? 'asc');
        }

        // ── Scope 2: search teks ─────────────────────────────────────────────
        if (!empty($f['search'])) {
            $s      = trim($f['search']);
            $sLike  = '%' . $s . '%';  // contains  — untuk nama
            $sStart = $s . '%';         // starts-with — untuk NIM & kode MK
            $where[]    = "(students.nim ILIKE ? OR students.name ILIKE ? OR courses.code ILIKE ? OR courses.name ILIKE ?)";
            $bindings[] = $sStart;  // NIM starts-with
            $bindings[] = $sLike;   // nama contains
            $bindings[] = $sStart;  // kode MK starts-with
            $bindings[] = $sLike;   // nama MK contains
        }

        // ── Scope 3: dropdown filter ─────────────────────────────────────────
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

        return self::rawExportQuery(implode(' AND ', $where), $bindings, $f['sortBy'] ?? 'enrollments.id', $f['sortDir'] ?? 'asc');
    }

    /**
     * Jalankan raw SELECT dengan PDO cursor — paling cepat, paling hemat RAM.
     * Tidak ada Eloquent overhead, tidak ada model hydration.
     */
    private static function rawExportQuery(string $whereClause, array $bindings, string $sortBy = 'enrollments.id', string $sortDir = 'asc')
    {
        // Whitelist kolom ORDER BY — cegah SQL injection
        $allowedSortColumns = [
            'enrollments.id',
            'enrollments.created_at',
            'enrollments.status',
            'enrollments.academic_year',
            'enrollments.semester',
            'students.nim',
            'students.name',
            'courses.code',
            'courses.name',
        ];
        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'enrollments.id';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'DESC' : 'ASC';

        $sql = "
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
            WHERE {$whereClause}
            ORDER BY {$sortBy} {$sortDir}
        ";

        // DB::cursor() di PostgreSQL tidak selalu reliable dengan positional bindings.
        // Gunakan raw PDO langsung agar execute() + fetch() benar-benar streaming
        // baris per baris tanpa load semua data ke RAM.
        return new \Illuminate\Support\LazyCollection(function () use ($sql, $bindings) {
            $pdo  = DB::getPdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($bindings));
            $stmt->setFetchMode(\PDO::FETCH_OBJ);

            while ($row = $stmt->fetch()) {
                yield $row;
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
