<div class="p-6 antialiased text-gray-800 dark:text-gray-200" x-data="{ detailMode: $wire.entangle('isDetailOpen') }">

    {{-- ─── Header ─────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Student Management</h1>
            <p class="text-sm text-gray-500">Daftar mahasiswa unik dan riwayat pengambilan KRS.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Trash link with badge --}}
            @if($trashedCount > 0)
            <a href="{{ route('students.trashed') }}"
                class="relative inline-flex items-center px-4 py-2.5 text-sm font-semibold text-red-600 bg-red-50 dark:bg-red-500/10 rounded-xl hover:bg-red-100 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Sampah
                <span class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                    {{ $trashedCount }}
                </span>
            </a>
            @endif
        </div>
    </div>

    {{-- ─── Filter Bar ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari NIM atau nama mahasiswa..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
            </div>

            <div class="flex items-center gap-3">
                {{-- Button Reset Search --}}
                @if($search || $sortBy !== 'name')
                <button wire:click="resetFilters"
                    class="p-2.5 text-gray-500 hover:text-indigo-600 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-indigo-50 transition-all shadow-sm"
                    title="Reset Filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                @endif

                {{-- Bulk Action --}}
                @if(count($selectedRows) > 0)
                <div wire:key="bulk-action-student" class="flex gap-2 items-center">
                    <button wire:click="confirmTrash()"
                        class="px-4 py-2.5 bg-rose-50 text-rose-600 text-sm font-medium rounded-xl hover:bg-rose-100 transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Buang ke Sampah ({{ count($selectedRows) }})
                    </button>
                    <button wire:click="resetSelection" class="text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 px-2 transition-colors">Batal</button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Table ───────────────────────────────────────────────────────────────── --}}
    <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">

        {{-- Loading overlay --}}
        <div wire:loading.flex class="absolute inset-0 z-10 bg-white/50 dark:bg-gray-900/50 backdrop-blur-[1px] items-center justify-center">
            <div class="flex items-center px-4 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-full border dark:border-gray-700">
                <svg class="animate-spin h-5 w-5 text-indigo-600 mr-3" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium">Memuat data...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer group" wire:click="setSort('nim')">
                            <div class="flex items-center">
                                NIM
                                <span class="ml-2 text-gray-400 group-hover:text-indigo-500">
                                    @if($sortBy === 'nim') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @else ↕ @endif
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer group" wire:click="setSort('name')">
                            <div class="flex items-center">
                                Nama Mahasiswa
                                <span class="ml-2 text-gray-400 group-hover:text-indigo-500">
                                    @if($sortBy === 'name') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @else ↕ @endif
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Total KRS</th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($students as $student)
                    <tr wire:key="row-{{ $student->id }}" class="hover:bg-gray-50/80 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="p-4 text-center" wire:key="td-chk-{{ $student->id }}">
                            <input type="checkbox" wire:model.live="selectedRows" value="{{ $student->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-xs font-mono font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 rounded-lg">
                                {{ $student->nim }}
                            </span>
                        </td>
                        <td class="p-4 font-medium">{{ $student->name }}</td>
                        <td class="p-4 text-sm text-gray-500 dark:text-gray-400">{{ $student->email }}</td>

                        {{-- ─── Total KRS: colored status badges ─── --}}
                        <td class="p-4 text-center">
                            <div class="flex flex-wrap justify-center gap-1">
                                @forelse($student->enrollments->groupBy('status') as $status => $items)
                                @php
                                    $badgeColors = [
                                        'DRAFT'     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                                        'SUBMITTED' => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
                                        'APPROVED'  => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
                                        'REJECTED'  => 'bg-red-100 text-red-700 ring-1 ring-red-200',
                                    ];
                                    $bc = $badgeColors[strtoupper($status)] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200';
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $bc }}">
                                    {{ $items->count() }} {{ $status }}
                                </span>
                                @empty
                                <span class="text-xs text-gray-400 font-medium">—</span>
                                @endforelse
                            </div>
                        </td>

                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Detail --}}
                                <button wire:click="showDetail({{ $student->id }})"
                                    class="p-2 hover:bg-white dark:hover:bg-gray-700 rounded-lg text-gray-500 hover:text-indigo-600 shadow-sm transition-all border border-transparent hover:border-gray-200"
                                    title="Detail KRS">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                {{-- Move to Trash --}}
                                <button wire:click="confirmTrash({{ $student->id }})"
                                    class="p-2 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg text-gray-500 hover:text-rose-600 transition-all"
                                    title="Buang ke Sampah">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr wire:key="empty-row-student">
                        <td colspan="6" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">Mahasiswa tidak ditemukan</h3>
                                <p class="text-gray-500 text-sm max-w-xs mx-auto">Coba gunakan kata kunci pencarian lain.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-800">
            {{ $students->links() }}
        </div>
    </div>

    {{-- ─── Modal Detail KRS ────────────────────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="detailMode" x-cloak
            x-on:keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">

            <div x-show="detailMode"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-[90rem] bg-white rounded-[2.5rem] shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">

                {{-- ── Close Button (Top Right) ── --}}
                <button @click="$wire.closeModal()"
                    class="absolute top-5 right-5 z-20 inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-500 transition-all shadow-sm"
                    title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="flex flex-col md:flex-row flex-1 min-h-0">

                    {{-- ── LEFT PANEL: Profil Mahasiswa + Statistik KRS ── --}}
                    <div class="w-full md:w-[300px] shrink-0 bg-slate-50/80 border-r border-slate-100 flex flex-col overflow-y-auto">
                        <div class="p-8 pt-7 flex-1">
                            @if($studentData)

                            {{-- Identitas --}}
                            <div class="mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-100 flex items-center justify-center mb-4">
                                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 leading-tight">{{ $studentData->name }}</h3>
                                <p class="text-sm font-bold text-indigo-600 tracking-widest uppercase mt-0.5">{{ $studentData->nim }}</p>
                            </div>

                            {{-- Info --}}
                            <div class="space-y-3 mb-6">
                                <div class="p-3.5 bg-white rounded-2xl border border-slate-200 shadow-sm">
                                    <span class="text-[10px] text-slate-400 font-black uppercase block mb-0.5">Email</span>
                                    <span class="text-sm font-semibold text-slate-700 break-all">{{ $studentData->email }}</span>
                                </div>
                                <div class="p-3.5 bg-white rounded-2xl border border-slate-200 shadow-sm">
                                    <span class="text-[10px] text-slate-400 font-black uppercase block mb-0.5">Terdaftar Sejak</span>
                                    <span class="text-sm font-semibold text-slate-700">
                                        {{ $studentData->created_at->format('d M Y') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Statistik KRS --}}
                            @if($krsStats)
                            <div class="mb-2">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Ringkasan KRS</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="p-3 bg-indigo-50 rounded-2xl text-center">
                                        <span class="text-2xl font-black text-indigo-700">{{ $krsStats['total_matkul'] }}</span>
                                        <span class="text-[10px] font-bold text-indigo-400 block mt-0.5 uppercase tracking-wide">Matkul</span>
                                    </div>
                                    <div class="p-3 bg-violet-50 rounded-2xl text-center">
                                        <span class="text-2xl font-black text-violet-700">{{ $krsStats['total_sks'] }}</span>
                                        <span class="text-[10px] font-bold text-violet-400 block mt-0.5 uppercase tracking-wide">Total SKS</span>
                                    </div>
                                    <div class="p-3 bg-sky-50 rounded-2xl text-center col-span-2">
                                        <span class="text-2xl font-black text-sky-700">{{ $krsStats['total_periode'] }}</span>
                                        <span class="text-[10px] font-bold text-sky-400 block mt-0.5 uppercase tracking-wide">Periode / Semester</span>
                                    </div>
                                </div>

                                {{-- Status Breakdown --}}
                                @if($krsStats['status_counts']->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Status KRS</p>
                                    @foreach($krsStats['status_counts'] as $status => $count)
                                    @php
                                        $colors = [
                                            'DRAFT'     => 'bg-slate-50 text-slate-600 ring-slate-200',
                                            'SUBMITTED' => 'bg-blue-50 text-blue-700 ring-blue-200',
                                            'APPROVED'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                            'REJECTED'  => 'bg-red-50 text-red-700 ring-red-200',
                                        ];
                                        $colorClass = $colors[strtoupper($status)] ?? 'bg-slate-50 text-slate-600 ring-slate-200';
                                    @endphp
                                    <div class="flex items-center justify-between px-3 py-2 rounded-xl ring-1 {{ $colorClass }}">
                                        <span class="text-xs font-bold">{{ $status }}</span>
                                        <span class="text-xs font-black">{{ $count }} matkul</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endif

                            @endif
                        </div>

                        {{-- Tombol Tutup --}}
                        <div class="p-6 pt-0">
                            <button @click="$wire.closeModal()"
                                class="w-full py-3.5 bg-slate-900 text-white rounded-2xl font-bold uppercase tracking-widest text-xs hover:bg-slate-700 transition">
                                Tutup
                            </button>
                        </div>
                    </div>

                    {{-- ── RIGHT PANEL: Tabel Mata Kuliah & KRS ── --}}
                    <div class="flex-1 min-w-0 flex flex-col p-6 md:p-8 bg-white overflow-hidden">

                        {{-- Header Panel Kanan --}}
                        <div class="mb-5 shrink-0 pr-10">
                            <h4 class="text-2xl font-black text-slate-900">Riwayat KRS</h4>
                            <p class="text-xs text-slate-400 font-medium mt-0.5">Data mata kuliah yang pernah diambil</p>
                        </div>

                        {{-- Search + Filter Status --}}
                        <div class="flex flex-col sm:flex-row flex-wrap gap-2 mb-4 shrink-0">
                            {{-- Search --}}
                            <div class="relative flex-1 min-w-[180px]">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input wire:model.live.debounce.300ms="detailSearch" type="text"
                                    placeholder="Cari kode / nama mata kuliah..."
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 placeholder-slate-400">
                            </div>

                            {{-- Filter Status --}}
                            @if(count($statusOptions) > 0)
                            <div class="relative">
                                <select wire:model.live="detailStatusFilter"
                                    class="appearance-none pl-3 pr-8 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 text-slate-600 font-medium cursor-pointer min-w-[130px]">
                                    <option value="">Semua Status</option>
                                    @foreach($statusOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @endif

                            {{-- Active filters badge --}}
                            @if($detailSearch || $detailStatusFilter)
                            <button wire:click="$set('detailSearch', ''); $set('detailStatusFilter', '')"
                                class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-red-50 text-red-500 text-xs font-bold rounded-xl hover:bg-red-100 transition-all border border-red-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filter
                            </button>
                            @endif
                        </div>

                        {{-- Tabel KRS + Matkul --}}
                        <div class="flex-1 overflow-hidden border border-slate-100 rounded-2xl relative">
                            {{-- Modal Detail Loading State --}}
                            <div wire:loading.flex wire:target="detailSearch, detailStatusFilter, setDetailSort" class="absolute inset-0 z-10 bg-white/50 backdrop-blur-[1px] items-center justify-center">
                                <div class="flex items-center px-4 py-2 bg-white shadow-xl rounded-full border border-slate-100">
                                    <svg class="animate-spin h-5 w-5 text-indigo-600 mr-3" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Memuat riwayat...</span>
                                </div>
                            </div>

                            <div class="overflow-auto h-full">
                                <table class="w-full min-w-[640px] text-left text-sm">
                                    <thead class="sticky top-0 bg-white z-10 border-b border-slate-100">
                                        <tr class="text-slate-500 font-semibold uppercase tracking-widest text-[10px]">
                                            <th wire:click="setDetailSort('id')"
                                                class="px-4 py-3.5 cursor-pointer hover:text-indigo-600 transition-colors whitespace-nowrap w-[40%]">
                                                Mata Kuliah
                                                {!! $detailSortBy == 'id' ? ($detailSortDir == 'asc' ? '↑' : '↓') : '↕' !!}
                                            </th>
                                            <th class="px-4 py-3.5 text-center whitespace-nowrap w-[8%]">SKS</th>
                                            <th wire:click="setDetailSort('semester')"
                                                class="px-4 py-3.5 text-center cursor-pointer hover:text-indigo-600 transition-colors whitespace-nowrap w-[10%]">
                                                Semester
                                                {!! $detailSortBy == 'semester' ? ($detailSortDir == 'asc' ? '↑' : '↓') : '↕' !!}
                                            </th>
                                            <th wire:click="setDetailSort('academic_year')"
                                                class="px-4 py-3.5 text-center cursor-pointer hover:text-indigo-600 transition-colors whitespace-nowrap w-[20%]">
                                                Periode
                                                {!! $detailSortBy == 'academic_year' ? ($detailSortDir == 'asc' ? '↑' : '↓') : '↕' !!}
                                            </th>
                                            <th wire:click="setDetailSort('status')"
                                                class="px-4 py-3.5 text-center cursor-pointer hover:text-indigo-600 transition-colors whitespace-nowrap w-[15%]">
                                                Status
                                                {!! $detailSortBy == 'status' ? ($detailSortDir == 'asc' ? '↑' : '↓') : '↕' !!}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 bg-white">
                                        @forelse($enrollments as $krs)
                                        @php
                                            $course = $krs->course;
                                            $statusColors = [
                                                'DRAFT'     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                                                'SUBMITTED' => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
                                                'APPROVED'  => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
                                                'REJECTED'  => 'bg-red-100 text-red-700 ring-1 ring-red-200',
                                            ];
                                            $sc = $statusColors[strtoupper($krs->status ?? '')] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200';
                                        @endphp
                                        <tr wire:key="krs-{{ $krs->id }}" class="hover:bg-indigo-50/20 transition-colors">

                                            {{-- Mata Kuliah: kode + nama + prodi --}}
                                            <td class="px-4 py-3.5">
                                                <div class="flex flex-col">
                                                    <span class="font-mono text-[10px] font-bold text-indigo-500 tracking-widest uppercase">
                                                        {{ $course->code ?? '-' }}
                                                    </span>
                                                    <span class="font-bold text-slate-800 text-sm leading-snug">
                                                        {{ $course->name ?? '-' }}
                                                    </span>
                                                    @if(!empty($course->study_program))
                                                    <span class="text-[10px] text-slate-400 mt-0.5">
                                                        {{ $course->study_program }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- SKS --}}
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-violet-50 text-violet-700 font-black text-sm ring-1 ring-violet-100">
                                                    {{ $course->credits ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Semester --}}
                                            <td class="px-4 py-3.5 text-center">
                                                @php $semVal = $krs->semester ?? null; @endphp
                                                @if($semVal !== null && $semVal !== '')
                                                @php $semesterType = ($semVal % 2 == 1) ? 'Ganjil' : 'Genap'; @endphp
                                                <span class="inline-flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-sky-50 text-sky-700 font-black text-sm ring-1 ring-sky-100">
                                                    <span>{{ $semVal }}</span>
                                                    <span class="text-xs font-semibold">{{ $semesterType }}</span>
                                                </span>
                                                @else
                                                <span class="text-slate-300 text-sm font-bold">—</span>
                                                @endif
                                            </td>

                                            {{-- Periode / Tahun Ajaran --}}
                                            <td class="px-4 py-3.5 text-center">
                                                @if(!empty($krs->academic_year))
                                                <span class="text-xs font-bold text-slate-700 bg-slate-50 px-2.5 py-1.5 rounded-lg whitespace-nowrap ring-1 ring-slate-100">
                                                    {{ $krs->academic_year }}
                                                </span>
                                                @else
                                                <span class="text-slate-300 text-sm font-bold">—</span>
                                                @endif
                                            </td>

                                            {{-- Status --}}
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide {{ $sc }}">
                                                    {{ $krs->status ?? '-' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr wire:key="empty-krs-row">
                                            <td colspan="5" class="py-16 text-center">
                                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span class="text-sm font-medium">Tidak ada data KRS ditemukan</span>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Pagination Modal --}}
                        <div class="mt-4 shrink-0">
                            @if($isDetailOpen && $enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                                <p class="text-xs text-slate-400 font-medium">
                                    Menampilkan {{ $enrollments->firstItem() ?? 0 }}–{{ $enrollments->lastItem() ?? 0 }}
                                    dari <span class="font-bold text-slate-600">{{ $enrollments->total() }}</span> entri
                                </p>
                                <div class="text-sm">
                                    {{ $enrollments->links() }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
