<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Enrollment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use App\Livewire\Traits\WithBreadcrumbs;

class StudentManager extends Component
{
    use WithPagination;

    // State untuk Tabel Utama
    #[Url(history: true)] public $search = '';
    #[Url(history: true)] public $sortBy = 'name';
    #[Url(history: true)] public $sortDir = 'asc';

    // State untuk Modal Detail
    public $isDetailOpen = false;
    public $selectedStudentId;
    public $detailSearch = '';
    public $detailSortBy = 'academic_year';
    public $detailSortDir = 'desc';
    public $detailStatusFilter = '';

    // Bulk Selection
    public $selectedRows = [];
    public $selectAll = false;

    // ─── Select All (per page) ────────────────────────────────────────────────

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = Student::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nim', 'like', $this->search . '%'))
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate(15, ['*'], 'page')
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function updatingPage()
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    // ─── Modal Detail ─────────────────────────────────────────────────────────

    public function showDetail($id)
    {
        $this->selectedStudentId  = $id;
        $this->isDetailOpen       = true;
        $this->detailStatusFilter = '';
        $this->resetPage('detailPage');
    }

    public function closeModal()
    {
        $this->isDetailOpen = false;
        $this->reset(['selectedStudentId', 'detailSearch', 'detailSortBy', 'detailSortDir', 'detailStatusFilter']);
    }

    // ─── Sort ─────────────────────────────────────────────────────────────────

    public function setSort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = ($this->sortDir === 'asc') ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $field;
            $this->sortDir = 'asc';
        }
    }

    public function setDetailSort($field)
    {
        if ($this->detailSortBy === $field) {
            $this->detailSortDir = ($this->detailSortDir === 'asc') ? 'desc' : 'asc';
        } else {
            $this->detailSortBy  = $field;
            $this->detailSortDir = 'asc';
        }
        $this->resetPage('detailPage');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDetailSearch()
    {
        $this->resetPage('detailPage');
    }

    public function updatedDetailStatusFilter()
    {
        $this->resetPage('detailPage');
    }

    // =========================================================================
    // FITUR BUANG KE SAMPAH (SOFT DELETE) - SINGLE & BULK
    // =========================================================================

    public function confirmTrash($id = null)
    {
        $count = count($this->selectedRows);
        $msg = $id ? "Pindahkan data mahasiswa ini ke keranjang sampah?" : "Pindahkan {$count} mahasiswa terpilih ke keranjang sampah?";

        $this->dispatch('confirm-trash', id: $id, message: $msg);
    }

    #[On('trash-confirmed')]
    public function trash($id = null)
    {
        DB::beginTransaction();

        try {
            if ($id) {
                Student::destroy($id);
                $msg = 'Data mahasiswa dipindahkan ke Sampah!';
            } else {
                Student::whereIn('id', $this->selectedRows)->delete();
                $msg = count($this->selectedRows) . ' mahasiswa terpilih dipindahkan ke Sampah!';
                $this->resetSelection();
            }

            DB::commit();
            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('notify', message: 'Gagal memindahkan mahasiswa ke Sampah!', type: 'error');
        }
    }

    // ─── Reset Selection ─────────────────────────────────────────────────────────
    public function resetSelection()
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumb('Student Management');
    }


    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        // render() — ubah query $students
        $students = Student::query()
            ->withCount('enrollments')
            ->with(['enrollments' => fn($q) => $q->select('student_id', 'status')]) // ← tambahkan ini
            ->when($this->search, function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('nim', 'ilike', $this->search . '%');
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(15, ['*'], 'page');

        $studentData    = null;
        $enrollments    = collect();
        $krsStats       = null;
        $statusOptions  = [];

        if ($this->isDetailOpen && $this->selectedStudentId) {
            $studentData = Student::find($this->selectedStudentId);

            // Ambil semua status unik untuk filter dropdown
            $statusOptions = Enrollment::where('student_id', $this->selectedStudentId)
                ->distinct()
                ->pluck('status')
                ->filter()
                ->sort()
                ->values()
                ->toArray();

            // Query KRS dengan data matkul lengkap
            // Query KRS dengan data matkul lengkap
            $enrollments = Enrollment::where('student_id', $this->selectedStudentId)
                ->with('course')
                ->when($this->detailSearch, function ($q) {
                    $q->whereHas('course', function ($sub) {
                        // Menggunakan kurung tutup (closure) untuk mengelompokkan kondisi OR
                        $sub->where(function ($inner) {
                            // PostgreSQL: Gunakan ILIKE untuk case-insensitive search
                            $inner->where('name', 'ILIKE', '%' . $this->detailSearch . '%')
                                ->orWhere('code', 'ILIKE', '%' . $this->detailSearch . '%');
                        });
                    });
                })
                ->when($this->detailStatusFilter, fn($q) => $q->where('status', $this->detailStatusFilter))
                ->orderBy($this->detailSortBy, $this->detailSortDir)
                ->paginate(8, ['*'], 'detailPage');

            // Statistik ringkasan KRS (dari semua enrollment tanpa filter)
            $allEnrollments = Enrollment::where('student_id', $this->selectedStudentId)
                ->with('course')
                ->get();

            $krsStats = [
                'total_matkul'  => $allEnrollments->count(),
                'total_sks'     => $allEnrollments->sum(fn($e) => optional($e->course)->credits ?? 0),
                'total_periode' => $allEnrollments->pluck('academic_year')->unique()->count(),
                'status_counts' => $allEnrollments->groupBy('status')->map->count(),
            ];
        }

        return view('livewire.student-manager', [
            'students'      => $students,
            'studentData'   => $studentData,
            'enrollments'   => $enrollments,
            'krsStats'      => $krsStats,
            'statusOptions' => $statusOptions,
            'trashedCount'  => \App\Models\Student::onlyTrashed()->count(),
        ])
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs
            ])
            ->title('Student Management');
    }

    public function resetFilters()
    {
        $this->reset(['search', 'sortBy', 'sortDir']);
        $this->resetPage();
    }
}
