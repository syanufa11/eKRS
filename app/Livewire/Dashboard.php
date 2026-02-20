<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Enrollment;
use App\Livewire\Traits\WithBreadcrumbs;
use Livewire\Attributes\Title;

#[Title('Home')]
class Dashboard extends Component
{
    use WithBreadcrumbs;

    public function mount()
    {
        $this->homeBreadcrumb(); // 🔥 khusus dashboard
    }

    public string $academicYear = 'all';
    public string $search = '';

    public function render()
    {
        // 1. Base Query untuk Statistik
        $statsQuery = Enrollment::query();
        if ($this->academicYear !== 'all') {
            $statsQuery->where('academic_year', $this->academicYear);
        }

        // 2. Hitung Statistik (Optimized for Postgres)
        // PostgreSQL mengembalikan hasil count/sum sebagai string/numeric,
        // pastikan casting ke (int) di PHP atau gunakan FILTER clause (Postgres native)
        $statsRaw = $statsQuery->selectRaw("
            count(*) as total,
            count(*) FILTER (WHERE status = 'APPROVED') as approved,
            count(*) FILTER (WHERE status = 'SUBMITTED') as submitted,
            count(*) FILTER (WHERE status = 'DRAFT') as draft,
            count(*) FILTER (WHERE status = 'REJECTED') as rejected
        ")->first();

        $stats = [
            'total'     => (int) ($statsRaw->total ?? 0),
            'approved'  => (int) ($statsRaw->approved ?? 0),
            'submitted' => (int) ($statsRaw->submitted ?? 0),
            'draft'     => (int) ($statsRaw->draft ?? 0),
            'rejected'  => (int) ($statsRaw->rejected ?? 0),
        ];

        // 3. Query untuk Tabel (Filter Tahun + Search)
        $enrollments = Enrollment::query()
            ->with(['student', 'course'])
            ->when($this->academicYear !== 'all', function ($q) {
                $q->where('academic_year', $this->academicYear);
            })
            ->when($this->search !== '', function ($q) {
                $q->whereHas('student', function ($sq) {
                    // PostgreSQL: Gunakan ILIKE untuk case-insensitive search
                    $sq->where('name', 'ILIKE', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->limit(8)
            ->get();

        // 4. Update Chart via Browser Event
        $this->dispatch('update-chart', stats: $stats);

        // 5. Daftar Tahun Ajaran
        $academicYears = Enrollment::distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        return view('livewire.dashboard', [
            'stats'         => $stats,
            'enrollments'   => $enrollments,
            'academicYears' => $academicYears,
        ])
            ->layout('layouts.app')
            ->title('Home');
    }
}
