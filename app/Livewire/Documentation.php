<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Dokumentasi Teknis')]
class Documentation extends Component
{
    public string $activeTab = 'setup';

    // Daftar Task sesuai instruksi [cite: 168, 282]
    public array $tasks = [
        'setup' => [
            'title' => 'Setup & Seeding',
            'icon' => 'fa-tools',
            'items' => ['TS-01']
        ],
        'crud' => [
            'title' => 'CRUD & Transaksi',
            'icon' => 'fa-database',
            'items' => ['TS-02', 'TS-03', 'TS-04', 'TS-11']
        ],
        'server-side' => [
            'title' => 'Server-Side Logic',
            'icon' => 'fa-server',
            'items' => ['TS-05', 'TS-06', 'TS-07', 'TS-08', 'TS-09', 'TS-10']
        ],
        'export' => [
            'title' => 'Export & Delete',
            'icon' => 'fa-file-export',
            'items' => ['TS-12', 'TS-13']
        ],
        'pdf' => [
            'title' => 'Instruksi PDF',
            'icon' => 'fa-file-pdf',
            'items' => []
        ]
    ];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.documentation')
            ->layout('layouts.app');
    }
}
