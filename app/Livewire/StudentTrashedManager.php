<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use App\Livewire\Traits\WithBreadcrumbs;

class StudentTrashedManager extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortField = 'deleted_at';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    #[Url(history: true)]
    public $filterKrs = '';

    public $selectedRows = [];
    public $selectAll = false;

    protected $allowedSorts = [
        'name',
        'nim',
        'email',
        'deleted_at',
        'enrollments_count'
    ];

    // --- Watchers ---
    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }
    public function updatedFilterKrs()
    {
        $this->resetPage();
        $this->resetSelection();
    }
    public function updatingPage()
    {
        $this->resetSelection();
    }

    // --- Sorting ---
    public function sortBy($field)
    {
        if (! in_array($field, $this->allowedSorts)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterKrs', 'sortField', 'sortDirection']);
        $this->resetPage();
        $this->resetSelection();
    }

    public function resetSelection()
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getTrashedQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // --- Query ---
    public function getTrashedQuery()
    {
        return Student::onlyTrashed()
            ->withCount('enrollments')
            ->when($this->search, function ($q) {
                $term = '%' . strtolower($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(nim) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->when($this->filterKrs !== '', function ($q) {
                if ($this->filterKrs === 'active') return $q->where('enrollments_count', '>', 0);
                if ($this->filterKrs === 'none') return $q->where('enrollments_count', 0);
            });
    }

    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumbMulti(
            'Student Management',
            'Student Trashed Management'
        );
    }

    public function render()
    {
        return view('livewire.student-trashed-manager', [
            'students' => $this->getTrashedQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(15),
        ])
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs
            ])
            ->title('Student Trashed Management');
    }

    // --- Restore ---
    public function confirmRestore($id = null)
    {
        if ($id === 'all') $msg = "Pulihkan SEMUA mahasiswa di sampah?";
        elseif ($id) $msg = "Pulihkan mahasiswa ini?";
        else $msg = "Pulihkan " . count($this->selectedRows) . " mahasiswa terpilih?";

        $this->dispatch('confirm-restore', id: $id, message: $msg);
    }

    #[On('restore-confirmed')]
    public function restore($id = null)
    {
        $query = Student::onlyTrashed();

        if ($id === 'all') $query->restore();
        elseif ($id) $query->where('id', $id)->restore();
        else $query->whereIn('id', $this->selectedRows)->restore();

        $this->resetSelection();
        $this->dispatch('notify', message: 'Data berhasil dipulihkan!', type: 'success');
    }

    // --- Force Delete ---
    public function confirmForceDelete($id = null)
    {
        if ($id === 'all') $msg = "KOSONGKAN SAMPAH? (Tindakan ini permanen)";
        elseif ($id) $msg = "Hapus permanen mahasiswa ini?";
        else $msg = "Hapus permanen " . count($this->selectedRows) . " data terpilih?";

        $this->dispatch('confirm-force-delete', id: $id, message: $msg);
    }

    #[On('force-delete-confirmed')]
    public function forceDelete($id = null)
    {
        $query = Student::onlyTrashed();

        if ($id === 'all') $query->forceDelete();
        elseif ($id) $query->where('id', $id)->forceDelete();
        else $query->whereIn('id', $this->selectedRows)->forceDelete();

        $this->resetSelection();
        $this->dispatch('notify', message: 'Data dihapus selamanya!', type: 'success');
    }

    public function goBack()
    {
        return redirect()->route('students.index');
    }
}
