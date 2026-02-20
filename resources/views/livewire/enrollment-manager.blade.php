<div class="p-6 antialiased text-gray-800 dark:text-gray-200" x-data="{ open: $wire.entangle('isOpen'), editMode: $wire.entangle('editMode'), detailMode: $wire.entangle('isDetailOpen') }">
    {{-- ─── Header Section ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Enrollment Management</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                <p class="text-sm text-slate-500 font-medium">Manajemen Data Enrollment & Filter Lanjutan</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full lg:w-auto">

<<<<<<< HEAD
            {{-- ── Export CSV ──────────────────────────────────────────────────────
                 Alur: wire:click="prepareExportCsv" → Livewire simpan filter ke Cache
                 → dispatch event JS 'csv-ready' → browser download native via anchor.
                 TIDAK menggunakan fetch()+blob agar file besar (5 juta baris) tidak
                 membebani memori browser.
            --}}
=======
>>>>>>> 98b3f57 (update 4)
            {{-- ── Export Dropdown (responsive) ──────────────────────────────── --}}
            <div class="flex flex-wrap items-center gap-2" x-data="{ exportOpen: false }" @click.outside="exportOpen = false">

                @if(count($selectedRows) > 0)
                {{-- Checkbox mode: tombol export selected + clear ──────────────── --}}
                <div class="flex items-center gap-2 animate-in fade-in slide-in-from-left-2 duration-300">
                    <button wire:click="prepareExportCsv('selected')" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white
                               px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm
                               transition-all shadow-lg shadow-emerald-200 dark:shadow-none">
                        <svg wire:loading wire:target="prepareExportCsv('selected')" class="w-3.5 h-3.5 animate-spin shrink-0" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <svg wire:loading.remove wire:target="prepareExportCsv('selected')" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span wire:loading.remove wire:target="prepareExportCsv('selected')">
                            <span class="hidden sm:inline">Export Terpilih</span>
                            <span class="sm:hidden">Terpilih</span>
                            ({{ count($selectedRows) }})
                        </span>
                        <span wire:loading wire:target="prepareExportCsv('selected')">...</span>
                    </button>
                    <button wire:click="resetSelection"
                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-colors"
                        title="Batalkan pilihan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endif

                {{-- Dropdown 3 opsi export ─────────────────────────────────────── --}}
                <div class="relative">
                    <button @click="exportOpen = !exportOpen"
                        class="inline-flex items-center gap-1.5 sm:gap-2
                               bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                               text-gray-700 dark:text-gray-200 px-3 sm:px-4 py-2 sm:py-2.5
                               rounded-xl font-bold text-xs sm:text-sm hover:bg-gray-50 dark:hover:bg-gray-700
                               transition-all shadow-sm"
                        :class="exportOpen ? 'ring-2 ring-indigo-400' : ''">
                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="hidden xs:inline">Export CSV</span>
                        <span class="xs:hidden">CSV</span>
                        <svg class="w-3 h-3 text-gray-400 transition-transform shrink-0" :class="exportOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="exportOpen" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                        class="absolute right-0 mt-2 w-60 sm:w-64 bg-white dark:bg-gray-900
                               rounded-2xl shadow-xl border border-slate-100 dark:border-gray-800
                               overflow-hidden z-50">

                        <div class="px-4 pt-3 pb-1.5 border-b border-slate-50 dark:border-gray-800">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pilih Cakupan Export</p>
                        </div>

                        {{-- Export Semua — selalu tampil --}}
                        <button wire:click="prepareExportCsv('all')" wire:loading.attr="disabled"
                            @click="exportOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors text-left group">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center shrink-0 group-hover:bg-slate-200 dark:group-hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700 dark:text-gray-200 truncate">Semua Data</p>
                                <p class="text-[11px] text-slate-400 truncate">Seluruh data dari database</p>
                            </div>
                            <svg wire:loading wire:target="prepareExportCsv('all')" class="w-4 h-4 animate-spin shrink-0 text-indigo-500" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </button>

                        {{-- Export Hasil Filter — hanya muncul kalau ada filter/search aktif --}}
                        @if($search !== '' || $filterStatus !== '' || $filterSemester !== '' || $filterYear !== '' || $filterCourse !== '')
                        <button wire:click="prepareExportCsv('filtered')" wire:loading.attr="disabled"
                            @click="exportOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors text-left group border-t border-slate-50 dark:border-gray-800">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 transition-colors">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700 dark:text-gray-200 truncate">Hasil Filter</p>
                                <p class="text-[11px] text-slate-400 truncate">
                                    {{ collect(['search' => $search, 'status' => $filterStatus, 'semester' => $filterSemester, 'tahun' => $filterYear, 'MK' => $filterCourse])->filter()->keys()->implode(', ') }} aktif
                                </p>
                            </div>
                            <svg wire:loading wire:target="prepareExportCsv('filtered')" class="w-4 h-4 animate-spin shrink-0 text-indigo-500" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </button>
                        @endif

                        {{-- Export Halaman Ini — selalu tampil --}}
                        <button wire:click="prepareExportCsv('page')" wire:loading.attr="disabled"
                            @click="exportOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors text-left group border-t border-slate-50 dark:border-gray-800">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0 group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition-colors">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700 dark:text-gray-200 truncate">Halaman Ini</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $perPage }} baris halaman sekarang</p>
                            </div>
                            <svg wire:loading wire:target="prepareExportCsv('page')" class="w-4 h-4 animate-spin shrink-0 text-indigo-500" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </button>

                    </div>
                </div>
            </div>

            {{-- ── Tambah Enrollment ───────────────────────────────────────────── --}}
            <button wire:click="create"
                class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-2.5
                       bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-200
                       dark:shadow-none hover:bg-indigo-700 transition-all active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Enrollment
            </button>

            @if($trashedCount > 0)
            {{-- ── Lihat Sampah ────────────────────────────────────────────────── --}}
            <a href="{{ route('enrollments.trashed') }}"
                class="flex-1 lg:flex-none inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                       font-bold text-sm transition-all gap-2 bg-white dark:bg-gray-800
                       border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-300
                       hover:bg-slate-50 dark:hover:bg-gray-700 relative">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Lihat Sampah
                <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                    {{ $trashedCount }}
                </span>
            </a>
            @endif
        </div>
    </div>

    {{-- ─── Advanced Multi-Filter Section ─────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 p-4 sm:p-6 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-gray-800 mb-4">

        {{-- Search + AND/OR toggle --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.500ms="search"
                    type="text"
                    placeholder="Cari nama, NIM, atau mata kuliah…"
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-gray-800 border-none
                           rounded-2xl focus:ring-2 focus:ring-indigo-500/20 text-sm">
            </div>

            <div class="flex bg-slate-100 dark:bg-gray-800 p-1 rounded-2xl w-fit shrink-0">
                <button wire:click="$set('filterOperator', 'AND')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                           {{ $filterOperator === 'AND' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-slate-500' }}">
                    AND
                </button>
                <button wire:click="$set('filterOperator', 'OR')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                           {{ $filterOperator === 'OR' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-slate-500' }}">
                    OR
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 sm:gap-3 items-center">

            {{-- Dropdown Per Page --}}
            <div class="flex items-center gap-2 px-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Tampil</span>
                <select wire:model.live="perPage"
                    class="bg-slate-50 dark:bg-gray-800 border-none rounded-xl text-xs py-2 px-2 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer font-bold text-indigo-600">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <select wire:model.live="filterYear"
                class="bg-slate-50 dark:bg-gray-800 border-none rounded-xl text-xs py-2.5 px-3 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                <option value="">Semua Tahun</option>
                @foreach($years_list as $yr)
                <option value="{{ $yr }}">{{ $yr }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus"
                class="bg-slate-50 dark:bg-gray-800 border-none rounded-xl text-xs py-2.5 px-3 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="DRAFT">DRAFT</option>
                <option value="SUBMITTED">SUBMITTED</option>
                <option value="APPROVED">APPROVED</option>
                <option value="REJECTED">REJECTED</option>
            </select>

            <select wire:model.live="filterSemester"
                class="bg-slate-50 dark:bg-gray-800 border-none rounded-xl text-xs py-2.5 px-3 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                <option value="">Semua Semester</option>
                <option value="GANJIL">GANJIL (1)</option>
                <option value="GENAP">GENAP (2)</option>
            </select>

            <select wire:model.live="filterCourse"
                class="bg-slate-50 dark:bg-gray-800 border-none rounded-xl text-xs py-2.5 px-3 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                <option value="">Semua MK</option>
                @foreach($courses_list as $c)
                <option value="{{ $c->code }}">{{ $c->code }}</option>
                @endforeach
            </select>

            {{-- Tombol Reset --}}
            @if(
            $search !== '' ||
            $filterStatus !== '' ||
            $filterSemester !== '' ||
            $filterYear !== '' ||
            $filterCourse !== '' ||
            $filterOperator !== 'AND' ||
            $sortBy !== 'enrollments.created_at' ||
            $sortDir !== 'desc' ||
            count($selectedRows) > 0
            )
            <div class="col-span-2 sm:col-span-1">
                <button type="button"
                    wire:click="resetFilters"
                    wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-500/10 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all border border-rose-100 dark:border-rose-500/20">

                    <svg wire:loading.remove wire:target="resetFilters" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>

                    <svg wire:loading wire:target="resetFilters" class="w-3.5 h-3.5 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>

                    <span>Reset</span>
                </button>
            </div>
            @endif
        </div>

        {{-- ── Bulk Action Bar ────────────────────────────────────────────────── --}}
        @if(count($selectedRows) > 0)
        <div wire:key="bulk-action-bar"
            class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 sm:p-4 mt-4
                   bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-2xl">

            <span class="text-xs sm:text-sm font-bold text-indigo-700 dark:text-indigo-400 shrink-0">
                <span class="bg-indigo-600 text-white px-2 py-0.5 rounded-md mr-1 text-xs">{{ count($selectedRows) }}</span>
                Enrollment Terpilih
            </span>

            <div class="flex flex-wrap items-center gap-2 sm:ml-auto">
                <button wire:click="confirmTrash()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5
                           bg-rose-50 dark:bg-rose-500/10 text-rose-600
                           border border-rose-200 dark:border-rose-800
                           text-xs font-bold rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Buang ke Sampah
                </button>

                <button wire:click="resetSelection"
                    class="text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-300
                           px-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-700 transition-all">
                    Batal
                </button>
            </div>
        </div>
        @endif

    </div>

    {{-- ─── Table Section ───────────────────────────────────────────────────────── --}}
    <div class="relative bg-white dark:bg-gray-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-gray-800 overflow-hidden relative min-h-[400px]">

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
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-gray-800/50 border-b border-slate-200/60 dark:border-gray-800 text-slate-500">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </th>
                        <th class="p-4 w-12 text-center text-xs font-bold uppercase tracking-wider">No.</th>
                        <th class="p-3 sm:p-4 text-xs font-bold uppercase tracking-wider cursor-pointer group"
                            wire:click="setSort('students.nim')">
                            <div class="flex items-center gap-1.5">
                                Mahasiswa / NIM
                                <span class="text-slate-300 group-hover:text-indigo-500 transition-colors">
                                    {{ $sortBy === 'students.nim' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-xs font-bold uppercase tracking-wider cursor-pointer group" wire:click="setSort('courses.code')">
                            <div class="flex items-center gap-2">Mata Kuliah <span class="text-slate-300 group-hover:text-indigo-500">↕</span></div>
                        </th>
                        <th class="p-4 text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="p-4 text-xs font-bold uppercase tracking-wider text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                    @forelse($enrollments as $enrollment)
                    <tr wire:key="row-{{ $enrollment->id }}" class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">

                        {{-- ✅ FIXED: Alpine reactive checkbox                          --}}
                        {{-- x-bind:checked langsung watch $wire.selectedRows di client   --}}
                        {{-- tidak bergantung Livewire re-render untuk update DOM checkbox --}}
                        <td class="p-4 text-center"
                            x-data="{ id: '{{ (string) $enrollment->id }}' }">
                            <input type="checkbox"
                                wire:key="chk-{{ $enrollment->id }}"
                                x-bind:checked="$wire.selectedRows.includes(id)"
                                x-on:change="$wire.toggleRow(id)"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </td>

                        <td class="p-4 text-center text-sm font-medium text-slate-500">
                            {{ $enrollments->firstItem() + $loop->index }}
                        </td>
                        <td class="p-3 sm:p-4">
                            <div class="font-bold text-sm text-slate-700 dark:text-gray-200 leading-snug">
                                {{ $enrollment->student_name }}
                            </div>
                            <div class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 font-semibold tracking-wider">
                                {{ $enrollment->student_nim }}
                            </div>
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                <span class="bg-slate-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-[10px] text-slate-500 dark:text-gray-400">
                                    {{ $enrollment->academic_year }}
                                </span>
                                <span class="text-slate-300 text-[10px]">•</span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $enrollment->semester == 1 ? 'Ganjil' : 'Genap' }}
                                </span>
                                {{-- Badge status mobile --}}
                                <span class="sm:hidden px-1.5 py-0.5 text-[9px] font-black tracking-wider rounded
                                    {{ $enrollment->status === 'APPROVED'  ? 'bg-emerald-100 text-emerald-700' :
                                       ($enrollment->status === 'REJECTED'  ? 'bg-rose-100 text-rose-700' :
                                       ($enrollment->status === 'SUBMITTED' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600')) }}">
                                    {{ $enrollment->status }}
                                </span>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 rounded-lg">{{ $enrollment->course_code }}</span>
                            <p class="text-xs text-slate-500 mt-1">{{ $enrollment->course_name }}</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-[10px] font-black tracking-wider rounded-md
                                {{ $enrollment->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' :
                                   ($enrollment->status === 'REJECTED' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' :
                                   ($enrollment->status === 'SUBMITTED' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : 'bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300')) }}">
                                {{ $enrollment->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="edit({{ $enrollment->id }})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 rounded-lg transition-all" title="Edit">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="confirmTrash({{ $enrollment->id }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all" title="Ke Sampah">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr wire:key="empty-row-enrollment">
                        <td colspan="6" class="py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-slate-50 dark:bg-gray-800 rounded-full mb-3">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-gray-300">Tidak ada data Enrollment</h3>
                                <p class="text-xs text-slate-500 mt-1">Gunakan filter pencarian atau tambahkan data baru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-800">
            {{ $enrollments->links() }}
        </div>
    </div>

    @include('livewire.partials.enrollment-modals')

</div>

@script
<script>
    Livewire.on('csv-ready', (event) => {
        const data = event[0] || event;
        const downloadUrl = data.url;
        const fileName = data.file ?? 'enrollments_export.csv';

        Swal.fire({
            title: 'Memproses CSV…',
            html: `File sedang di-<em>stream</em> dari server ke browser Anda.<br>
                   <small class="text-slate-500">
                     Download akan dimulai otomatis.<br>
                     Jangan tutup tab ini selama proses berlangsung.
                   </small>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        const link = document.createElement('a');
        link.href = downloadUrl;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Download Dimulai!',
                html: `File <strong>"${fileName}"</strong> sedang diunduh.`,
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        }, 1500);
    });

    Livewire.on('export-error', (event) => {
        const data = event[0] || event;
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memulai Export',
            html: `<p>${data.message ?? 'Terjadi kesalahan pada server.'}</p>`,
            confirmButtonText: 'Tutup',
        });
    });
<<<<<<< HEAD

=======
>>>>>>> 98b3f57 (update 4)
</script>
@endscript
