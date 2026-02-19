<?php

namespace App\Livewire;

use App\Models\Enrollment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use App\Livewire\Traits\WithBreadcrumbs;

class EnrollmentTrashedManager extends Component
{
    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumbMulti(
            'Enrollment Management',
            'Enrollment Trashed Management'
        );
    }
    use WithPagination;

    // --- Filter & Sorting State ---
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortField = 'deleted_at';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    #[Url(history: true)]
    public $filterYear = '';

    #[Url(history: true)]
    public $filterSemester = '';

    // Bulk selection
    public $selectedRows = [];
    public $selectAll = false;

    // --- Watchers & Lifecycle ---
    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }
    public function updatedFilterYear()
    {
        $this->resetPage();
        $this->resetSelection();
    }
    public function updatedFilterSemester()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingPage()
    {
        $this->resetSelection();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterYear', 'filterSemester', 'sortField', 'sortDirection']);
        $this->resetPage();
        $this->resetSelection();
    }

    public function resetSelection()
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getTrashedQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(15)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // --- Query Trashed KRS ---
    public function getTrashedQuery()
    {
        return Enrollment::onlyTrashed()
            ->with(['student', 'course'])
            ->when($this->search, function ($q) {
                $term = '%' . strtolower($this->search) . '%';

                // Kita bungkus semua OR dalam satu group WHERE agar tidak merusak ONLYTRASHED
                return $q->where(function ($query) use ($term) {
                    $query->whereHas('student', function ($sub) use ($term) {
                        $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(nim) LIKE ?', [$term]);
                    })
                        ->orWhereHas('course', function ($sub) use ($term) {
                            $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                                ->orWhereRaw('LOWER(code) LIKE ?', [$term]);
                        })
                        // Tambahan: Cari juga di kolom semester atau tahun akademik milik Enrollment itu sendiri
                        ->orWhereRaw('LOWER(academic_year) LIKE ?', [$term])
                        ->orWhere('semester', 'LIKE', $term);
                });
            })
            ->when($this->filterYear, fn($q) => $q->where('academic_year', $this->filterYear))
            ->when($this->filterSemester, fn($q) => $q->where('semester', $this->filterSemester));
    }


    public function render()
    {
        return view('livewire.enrollment-trashed-manager', [
            'enrollments' => $this->getTrashedQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(15),
            'academicYears' => \App\Models\Enrollment::onlyTrashed()
                ->distinct()
                ->pluck('academic_year'),
        ])
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs
            ])
            ->title('Enrollment Trashed Management');
    }

    // --- Actions ---
    public function confirmRestore($id = null)
    {
        $msg = $id === 'all' ? "Pulihkan SEMUA data KRS?" : ($id ? "Pulihkan data KRS ini?" : "Pulihkan " . count($this->selectedRows) . " data terpilih?");
        $this->dispatch('confirm-restore', id: $id, message: $msg);
    }

    #[On('restore-confirmed')]
    public function restore($id = null)
    {
        DB::beginTransaction();

        try {
            $query = Enrollment::onlyTrashed();

            if ($id === 'all') {
                $query->restore();
            } elseif ($id) {
                $query->findOrFail($id)->restore();
            } else {
                $query->whereIn('id', $this->selectedRows)->restore();
            }

            DB::commit();

            $this->resetSelection();
            $this->dispatch('notify', message: 'Data berhasil dipulihkan', type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('notify', message: 'Gagal memulihkan data!', type: 'error');
        }
    }

    public function confirmForceDelete($id = null)
    {
        $msg = $id === 'all' ? "Kosongkan keranjang sampah secara permanen?" : "Hapus permanen data ini?";
        $this->dispatch('confirm-force-delete', id: $id, message: $msg);
    }

    #[On('force-delete-confirmed')]
    public function forceDelete($id = null)
    {
        DB::beginTransaction();

        try {
            $query = Enrollment::onlyTrashed();

            if ($id === 'all') {
                $query->forceDelete();
            } elseif ($id) {
                $query->findOrFail($id)->forceDelete();
            } else {
                $query->whereIn('id', $this->selectedRows)->forceDelete();
            }

            DB::commit();

            $this->resetSelection();
            $this->dispatch('notify', message: 'Data dihapus permanen', type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('notify', message: 'Gagal menghapus permanen!', type: 'error');
        }
    }


    public function goBack()
    {
        return redirect()->route('enrollments.index');
    }
}
