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

    // Properti Filter & Sort
    #[Url(history: true)]
    public $search = '';
    #[Url(history: true)]
    public $sortBy = 'code';
    #[Url(history: true)]
    public $sortDir = 'asc';
    #[Url]
    public $filterCredits = '';

    // Properti Form (Binding langsung ke wire:model)
    public $courseId; // Kosong jika Create, Terisi jika Update
    public $code, $name, $credits;

    public $isOpen = false; // Kontrol modal
    public $selectedRows = [];
    public $selectAll = false;

    public function mount()
    {
        $this->breadcrumb('Course Management');
    }

    // --- Create Logic ---
    public function create()
    {
        $this->resetInput();
        $this->isOpen = true; // Buka modal
    }

    // --- Update Logic ---
    public function edit($id)
    {
        $this->resetInput();
        $course = Course::findOrFail($id);

        $this->courseId = $course->id;
        $this->code     = $course->code;
        $this->name     = $course->name;
        $this->credits  = $course->credits;

        $this->isOpen = true; // Buka modal dengan data terisi
    }

    // --- Action Simpan (Create & Update) ---
    public function store()
    {
        // Validasi
        $validated = $this->validate([
            'code'    => [
                'required',
                // Regex diperbarui: 2-3 huruf di depan, sisanya angka, total maksimal 6
                // Contoh: CS101 (5), ENG202 (6)
                'regex:/^[a-zA-Z]{2,3}[0-9]{1,4}$/',
                'max:6', // Memastikan panjang string maksimal 6
                Rule::unique('courses', 'code')->ignore($this->courseId)->whereNull('deleted_at')
            ],
            'name'    => 'required|min:3|max:120',
            'credits' => 'required|integer|min:1|max:10',
        ]);

        DB::beginTransaction();
        try {
            // Jika courseId ada, maka Update. Jika tidak, maka Create.
            Course::updateOrCreate(
                ['id' => $this->courseId],
                [
                    'code'    => strtoupper($this->code),
                    'name'    => $this->name,
                    'credits' => $this->credits,
                ]
            );

            DB::commit();

            $this->dispatch(
                'notify',
                message: $this->courseId ? 'Mata kuliah berhasil diperbarui!' : 'Mata kuliah berhasil ditambahkan!',
                type: 'success'
            );

            $this->closeModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('notify', message: 'Terjadi kesalahan sistem!', type: 'error');
        }
    }

    protected function messages()
    {
        return [
            'code.required' => 'Kode mata kuliah wajib diisi.',
            'code.regex'    => 'Format kode tidak valid (Gunakan 2-3 huruf di awal, sisanya angka).',
            'code.max'      => 'Kode mata kuliah tidak boleh lebih dari 6 karakter.',
            'code.unique'   => 'Kode mata kuliah ini sudah terdaftar.',

            'name.required' => 'Nama mata kuliah wajib diisi.',
            'name.min'      => 'Nama mata kuliah minimal 3 karakter.',
            'name.max'      => 'Nama mata kuliah terlalu panjang (maksimal 120 karakter).',

            'credits.required' => 'Jumlah SKS wajib diisi.',
            'credits.integer'  => 'SKS harus berupa angka.',
            'credits.min'      => 'SKS minimal adalah 1.',
            'credits.max'      => 'SKS maksimal adalah 10.',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'code'    => [
                'required',
                'regex:/^[a-zA-Z]{2,3}[0-9]{1,4}$/',
                'max:6',
                Rule::unique('courses', 'code')->ignore($this->courseId)->whereNull('deleted_at')
            ],
            'name'    => 'required|min:3|max:120',
            'credits' => 'required|integer|min:1|max:10',
        ]);
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->reset(['courseId', 'code', 'name', 'credits']);
        $this->resetErrorBag();
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

    public function getCoursesQuery()
    {
        return Course::query()
            ->when($this->search, function ($q) {
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
            'trashedCount' => Course::onlyTrashed()->count(),
        ])
            ->layout('layouts.app', ['breadcrumbs' => $this->breadcrumbs])
            ->title('Course Management');
    }

    public function resetFilters()
    {
        $this->reset(['search', 'sortBy', 'sortDir', 'filterCredits']);
        $this->resetPage(); // Penting agar kembali ke halaman 1 setelah filter dihapus
    }

    public function setSort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = ($this->sortDir === 'asc') ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Ambil semua ID mata kuliah yang sedang tampil di halaman saat ini
            $this->selectedRows = $this->getCoursesQuery()
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate(10)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->resetSelection();
        }
    }

    public function resetSelection()
    {
        $this->reset(['selectedRows', 'selectAll']);
    }

    // Tambahkan ini agar saat pindah halaman, pilihan checkbox direset (opsional)
    public function updatedPage()
    {
        $this->resetSelection();
    }
}
