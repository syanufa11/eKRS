<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use App\Livewire\Traits\WithBreadcrumbs;

class CourseTrashedManager extends Component
{
    use WithPagination;

    // --- State Properties ---
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortField = 'deleted_at';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    #[Url(history: true)]
    public $filterCredits = '';

    // Bulk Selection
    public $selectedRows = [];
    public $selectAll = false;

    // --- Lifecycle & Watchers ---
    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedFilterCredits()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingPage()
    {
        $this->resetSelection();
    }

    // --- Features ---
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
            // Ambil ID hanya dari halaman yang sedang tampil (sesuai filter & sorting)
            $this->selectedRows = $this->getTrashedQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function resetSelection()
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    // --- Query Builder ---
    // --- Query Builder ---
    public function getTrashedQuery()
    {
        return Course::onlyTrashed()
            ->when($this->search, function ($q) {
                // PostgreSQL: ILIKE jauh lebih efisien daripada LOWER(...) LIKE
                return $q->where(function ($sub) {
                    $sub->where('name', 'ILIKE', '%' . $this->search . '%')
                        ->orWhere('code', 'ILIKE', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCredits, function ($q) {
                return $q->where('credits', $this->filterCredits);
            });
    }

    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumbMulti(
            'Course Management',
            'Course Trashed Management'
        );
    }

    public function render()
    {
        return view('livewire.course-trashed-manager', [
            'courses' => $this->getTrashedQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
        ])
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs
            ])
            ->title('Course Trashed Management');
    }

    // --- Actions: Restore ---
    public function confirmRestore($id = null)
    {
        if ($id === 'all') $msg = "Pulihkan SEMUA mata kuliah di keranjang?";
        elseif ($id) $msg = "Pulihkan mata kuliah ini?";
        else $msg = "Pulihkan " . count($this->selectedRows) . " mata kuliah terpilih?";

        $this->dispatch('confirm-restore', id: $id, message: $msg);
    }

    #[On('restore-confirmed')]
    public function restore($id = null)
    {
        DB::beginTransaction();

        try {
            if ($id === 'all') {
                Course::onlyTrashed()->restore();
                $msg = 'Semua data dipulihkan!';
            } elseif ($id) {
                Course::onlyTrashed()->findOrFail($id)->restore();
                $msg = 'Data dipulihkan!';
            } else {
                Course::onlyTrashed()->whereIn('id', $this->selectedRows)->restore();
                $msg = count($this->selectedRows) . ' data dipulihkan!';
            }

            DB::commit();

            $this->resetSelection();
            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);
            $this->dispatch('notify', message: 'Gagal memulihkan data!', type: 'error');
        }
    }


    // --- Actions: Force Delete ---
    public function confirmForceDelete($id = null)
    {
        if ($id === 'all') $msg = "Hapus PERMANEN SEMUA data di keranjang?";
        elseif ($id) $msg = "Hapus PERMANEN mata kuliah ini?";
        else $msg = "Hapus PERMANEN " . count($this->selectedRows) . " data terpilih?";

        $this->dispatch('confirm-force-delete', id: $id, message: $msg);
    }

    #[On('force-delete-confirmed')]
    public function forceDelete($id = null)
    {
        DB::beginTransaction();

        try {
            if ($id === 'all') {
                Course::onlyTrashed()->forceDelete();
                $msg = 'Keranjang sampah dikosongkan!';
            } elseif ($id) {
                Course::onlyTrashed()->findOrFail($id)->forceDelete();
                $msg = 'Data dihapus permanen!';
            } else {
                Course::onlyTrashed()->whereIn('id', $this->selectedRows)->forceDelete();
                $msg = count($this->selectedRows) . ' data dihapus permanen!';
            }

            DB::commit();

            $this->resetSelection();
            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);
            $this->dispatch('notify', message: 'Gagal menghapus permanen!', type: 'error');
        }
    }


    // Tambahkan fungsi ini di dalam class CourseTrashedManager
    public function resetFilters()
    {
        $this->reset(['search', 'filterCredits', 'sortField', 'sortDirection']);
        $this->resetPage(); // Kembali ke halaman 1
        $this->resetSelection(); // Bersihkan centang
    }

    public function goBack()
    {
        return redirect()->route('courses.index');
    }
}
