<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use App\Livewire\Traits\WithBreadcrumbs;

class CourseManager extends Component
{
    use WithPagination;
    
    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumb('Course Management');
    }

    // Properti Filter & Sort (Disinkronkan ke URL)
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'code';

    #[Url(history: true)]
    public $sortDir = 'asc';

    #[Url]
    public $filterCredits = '';

    // Properti Form
    public $courseId, $code, $name, $credits;
    public $isOpen = false;
    public $isOtherCredits = false;

    // Properti Bulk Selection
    public $selectedRows = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getCoursesQuery()
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate(10)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatingPage()
    {
        $this->selectedRows = [];
        $this->selectAll    = false;
    }

    public function setSort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function getCoursesQuery()
    {
        return Course::query()
            ->when($this->search, function ($q) {
                // PostgreSQL: Gunakan ILIKE untuk pencarian tanpa memperdulikan besar/kecil huruf
                return $q->where(function ($subQuery) {
                    $subQuery->where('name', 'ILIKE', '%' . $this->search . '%')
                        ->orWhere('code', 'ILIKE', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCredits, fn($q) => $q->where('credits', $this->filterCredits));
    }


    public function render()
    {
        return view('livewire.course-manager', [
            'courses' => $this->getCoursesQuery()
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate(10),
            'trashedCount' => \App\Models\Course::onlyTrashed()->count(),
        ])
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs,
            ])
            ->title('Course Management');
    }



    // ─── Modal Logic ────────────────────────────────────────────────────────────

    public function create()
    {
        $this->resetInput();
        $this->isOpen = true;
    }

    public function edit(Course $course)
    {
        $this->courseId = $course->id;
        $this->code     = $course->code;
        $this->name     = $course->name;
        $this->credits  = $course->credits;
        $this->isOpen   = true;
    }

    public function store()
    {
        DB::beginTransaction();

        try {
            $validated = $this->validate([
                'code'    => ['required', 'regex:/^[a-zA-Z]{2,4}[0-9]{3}$/', Rule::unique('courses', 'code')->ignore($this->courseId)->whereNull('deleted_at')],
                'name'    => 'required|min:3|max:120',
                'credits' => 'required|integer|min:1|max:10',
            ]);

            Course::updateOrCreate(
                ['id' => $this->courseId],
                [
                    'code'    => strtoupper($this->code),
                    'name'    => $this->name,
                    'credits' => $this->credits,
                ]
            );

            DB::commit();

            $this->isOpen = false;
            $this->dispatch('notify', message: $this->courseId ? 'Data diperbarui!' : 'Data ditambahkan!', type: 'success');
            $this->resetInput();
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);
            $this->dispatch('notify', message: 'Gagal menyimpan data!', type: 'error');
        }
    }


    // ─── Single Delete (Soft) ────────────────────────────────────────────────────

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id, message: 'Data akan dipindahkan ke Sampah.');
    }

    // ─── Bulk Delete (Soft) ──────────────────────────────────────────────────────

    public function confirmBulkDelete()
    {
        $count = count($this->selectedRows);
        $this->dispatch(
            'confirm-delete',
            id: null,
            message: "Pindahkan {$count} data yang dipilih ke Sampah?"
        );
    }

    // ─── Executed after SweetAlert confirms ─────────────────────────────────────

    #[On('delete-confirmed')]
    public function delete($id = null)
    {
        DB::beginTransaction();

        try {
            if ($id) {
                Course::destroy($id);
                $msg = 'Data dipindahkan ke Sampah!';
            } else {
                Course::whereIn('id', $this->selectedRows)->delete();
                $this->selectedRows = [];
                $this->selectAll = false;
                $msg = 'Semua data terpilih dipindahkan ke Sampah!';
            }

            DB::commit();

            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);
            $this->dispatch('notify', message: 'Gagal menghapus data!', type: 'error');
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function updatedCredits($value)
    {
        if ($value === 'other') {
            $this->isOtherCredits = true;
            $this->credits = null;
        } else {
            $this->isOtherCredits = false;
        }
    }

    private function resetInput()
    {
        $this->reset(['courseId', 'code', 'name', 'credits']);
        $this->resetErrorBag();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCredits', 'sortBy', 'sortDir']);
        $this->resetPage();
    }

    // =========================================================================
    // FITUR BUANG KE SAMPAH (SOFT DELETE) - SINGLE & BULK
    // =========================================================================

    public function confirmTrash($id = null)
    {
        $count = count($this->selectedRows);
        $msg = $id ? "Pindahkan mata kuliah ini ke keranjang sampah?" : "Pindahkan {$count} mata kuliah terpilih ke keranjang sampah?";

        $this->dispatch('confirm-trash', id: $id, message: $msg);
    }

    #[On('trash-confirmed')]
    public function trash($id = null)
    {
        DB::beginTransaction();

        try {
            if ($id) {
                Course::destroy($id);
                $msg = 'Mata kuliah dipindahkan ke Sampah!';
            } else {
                Course::whereIn('id', $this->selectedRows)->delete();
                $msg = count($this->selectedRows) . ' mata kuliah terpilih dipindahkan ke Sampah!';
                $this->resetSelection();
            }

            DB::commit();

            $this->dispatch('notify', message: $msg, type: 'success');
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);
            $this->dispatch('notify', message: 'Gagal memindahkan ke sampah!', type: 'error');
        }
    }


    // ─── Reset Selection ─────────────────────────────────────────────────────────
    public function resetSelection()
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }
}
