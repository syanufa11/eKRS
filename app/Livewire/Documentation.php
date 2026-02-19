<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;

class Documentation extends Component
{
    /**
     * Disimpan di URL query string (?section=ts01) agar
     * section tetap terbuka saat halaman di-refresh atau
     * link dibagikan ke orang lain.
     */
    #[Url(as: 'section', keep: true)]
    public string $activeSection = 'overview';

    /**
     * Struktur navigasi sidebar.
     */
    public array $nav = [
        [
            'group' => 'Pengantar',
            'icon'  => 'fa-book-open',
            'items' => [
                ['key' => 'overview', 'label' => 'Ringkasan Teknis'],
                ['key' => 'stack',    'label' => 'Tech Stack'],
            ],
        ],
        [
            'group' => 'Panduan Fitur',
            'icon'  => 'fa-rocket',
            'items' => [
                ['key' => 'crud',       'label' => 'CRUD & Form'],
                ['key' => 'filter',     'label' => 'Filter & Pencarian'],
                ['key' => 'export',     'label' => 'Export CSV'],
                ['key' => 'softdelete', 'label' => 'Soft Deletes & Trash'],
            ],
        ],
        [
            'group' => 'Skenario Pengujian',
            'icon'  => 'fa-flask',
            'items' => [
                ['key' => 'ts01',   'label' => 'TS-01 · Setup & Seed'],
                ['key' => 'ts02',   'label' => 'TS-02 · Atomic Transaction'],
                ['key' => 'ts0304', 'label' => 'TS-03 & 04 · Validasi'],
                ['key' => 'ts05',   'label' => 'TS-05 · Pagination'],
                ['key' => 'ts06',   'label' => 'TS-06 · Sorting'],
                ['key' => 'ts07',   'label' => 'TS-07 · Quick Filter'],
                ['key' => 'ts08',   'label' => 'TS-08 · Live Search'],
                ['key' => 'ts0910', 'label' => 'TS-09 & 10 · Advanced Filter'],
                ['key' => 'ts11',   'label' => 'TS-11 · Update Data'],
                ['key' => 'ts12',   'label' => 'TS-12 · Soft Deletes'],
                ['key' => 'ts13',   'label' => 'TS-13 · Export 5jt Data'],
            ],
        ],
        [
            'group' => 'Laporan',
            'icon'  => 'fa-file-lines',
            'items' => [
                ['key' => 'report', 'label' => 'Laporan Teknis'],
            ],
        ],
    ];

    public function setSection(string $key): void
    {
        if (array_key_exists($key, array_column(array_merge(...array_column($this->nav, 'items')), null, 'key'))) {
            $this->activeSection = $key;
        }
    }

    public function render()
    {
        return view('livewire.documentation')->extends('layouts.fullscreen-layout');
    }
}
