<div class="p-3 sm:p-6 antialiased text-gray-800 dark:text-gray-200"
    x-data="{
         open: $wire.entangle('isOpen'),
         editMode: $wire.entangle('editMode'),
         detailMode: $wire.entangle('isDetailOpen'),
         openCsv: false,
         openXlsx: false
     }">

    {{-- ═══════════════════════════════════════════════════════════════════════════
         HEADER SECTION
         Mobile  : judul atas, baris tombol di bawah (wrap otomatis)
         Desktop : judul kiri, semua tombol kanan 1 baris
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-6">
        <div class="mb-4">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Enrollment Management
            </h1>
            <div class="flex items-center gap-2 mt-1.5">
                <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Manajemen Data Enrollment &amp; Filter Lanjutan</p>
            </div>
        </div>

        {{-- Baris tombol — wrap otomatis di mobile --}}
        <div class="flex flex-wrap items-center gap-2">

            {{-- ── Export CSV (dropdown scope) ──────────────────────────────── --}}
            <div class="relative" @click.outside="openCsv = false">
                <button @click="openCsv = !openCsv"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="prepareExportCsv"
                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5
                           bg-emerald-600 hover:bg-emerald-700 active:scale-95
                           text-white rounded-xl font-bold text-xs sm:text-sm
                           transition-all shadow-md shadow-emerald-200 dark:shadow-none whitespace-nowrap">
                    <svg wire:loading.remove wire:target="prepareExportCsv"
                        class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <svg wire:loading wire:target="prepareExportCsv"
                        class="w-3.5 h-3.5 sm:w-4 sm:h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span wire:loading.remove wire:target="prepareExportCsv">CSV</span>
                    <span wire:loading wire:target="prepareExportCsv">…</span>
                    <svg wire:loading.remove wire:target="prepareExportCsv"
                        class="w-3 h-3 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openCsv"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 mt-2 w-52 bg-white dark:bg-gray-800
                           border border-slate-200 dark:border-gray-700 rounded-2xl shadow-xl z-50 overflow-hidden">
                    <div class="px-3 py-2 border-b border-slate-100 dark:border-gray-700">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ekspor CSV</p>
                    </div>
                    <button @click="openCsv=false" wire:click="prepareExportCsv('all')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors text-left">
                        <span class="text-base leading-none">🌐</span>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Semua Data</div>
                            <div class="text-[10px] text-slate-400">Sesuai filter aktif · streaming langsung</div>
                        </div>
                    </button>
                    <button @click="openCsv=false" wire:click="prepareExportCsv('current_page')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors text-left">
                        <span class="text-base leading-none">📄</span>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Halaman Ini</div>
                            <div class="text-[10px] text-slate-400">{{ $perPage == 0 ? 'Semua' : $perPage }} data ditampilkan</div>
                        </div>
                    </button>
                    @if(count($selectedRows) > 0)
                    <button @click="openCsv=false" wire:click="prepareExportCsv('selected')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors text-left">
                        <span class="text-base leading-none">☑️</span>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Data Terpilih</div>
                            <div class="text-[10px] text-slate-400">{{ count($selectedRows) }} baris dicentang</div>
                        </div>
                    </button>
                    @endif
                </div>
            </div>

            {{-- ── Export XLSX (Async Queue untuk 'all', Direct untuk halaman/terpilih) ── --}}
            <div class="relative" @click.outside="openXlsx = false">
                <button @click="openXlsx = !openXlsx"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="prepareExportXlsx,exportXlsxDirect"
                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5
                           bg-violet-600 hover:bg-violet-700 active:scale-95
                           text-white rounded-xl font-bold text-xs sm:text-sm
                           transition-all shadow-md shadow-violet-200 dark:shadow-none whitespace-nowrap">

                    <svg wire:loading.remove wire:target="prepareExportXlsx,exportXlsxDirect"
                        class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V7l-5-5H5a1 1 0 00-1 1v16a1 1 0 001 1z" />
                    </svg>

                    <svg wire:loading wire:target="prepareExportXlsx,exportXlsxDirect"
                        class="w-3.5 h-3.5 sm:w-4 sm:h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>

                    <span wire:loading.remove wire:target="prepareExportXlsx,exportXlsxDirect">XLSX</span>
                    <span wire:loading wire:target="prepareExportXlsx,exportXlsxDirect">Proses...</span>

                    <svg wire:loading.remove wire:target="prepareExportXlsx,exportXlsxDirect"
                        class="w-3 h-3 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openXlsx"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 mt-2 w-56 bg-white dark:bg-gray-800
                           border border-slate-200 dark:border-gray-700 rounded-2xl shadow-xl z-50 overflow-hidden">
                    <div class="px-3 py-2 border-b border-slate-100 dark:border-gray-700">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ekspor XLSX</p>
                    </div>

                    {{-- Tombol: Semua Data — Async via Queue Job --}}
                    <button @click="openXlsx=false" wire:click="prepareExportXlsx('all')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors text-left">
                        <span class="text-base leading-none">🌐</span>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Semua Data</div>
                            <div class="text-[10px] text-slate-400">Background queue · split per 1 juta baris · ZIP</div>
                        </div>
                    </button>

                    {{-- Tombol: Halaman Ini — Direct download --}}
                    <button @click="openXlsx=false" wire:click="exportXlsxDirect('current_page')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors text-left">
                        <span class="text-base leading-none">📄</span>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Halaman Ini</div>
                            <div class="text-[10px] text-slate-400">{{ $perPage == 0 ? 'Semua' : $perPage }} data · langsung unduh</div>
                        </div>
                    </button>

                    {{-- Tombol: Data Terpilih — Direct download --}}
                    @if(count($selectedRows) > 0)
                    <button @click="openXlsx=false" wire:click="exportXlsxDirect('selected')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors text-left">
                        <span class="text-base leading-none">☑️</span>
                        <div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Data Terpilih</div>
                            <div class="text-[10px] text-slate-400">{{ count($selectedRows) }} baris · langsung unduh</div>
                        </div>
                    </button>
                    @endif
                </div>
            </div>

            {{-- ── Tambah Enrollment ─────────────────────────────────────────── --}}
            <button wire:click="create"
                class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-5 py-2 sm:py-2.5
                       bg-indigo-600 hover:bg-indigo-700 active:scale-95
                       text-white rounded-xl font-bold text-xs sm:text-sm
                       transition-all shadow-lg shadow-indigo-200 dark:shadow-none whitespace-nowrap">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden sm:inline">Tambah Enrollment</span>
                <span class="sm:hidden">Tambah</span>
            </button>

            {{-- ── Lihat Sampah ──────────────────────────────────────────────── --}}
            <a href="{{ route('enrollments.trashed') }}"
                class="relative inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5
                       bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700
                       text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700
                       rounded-xl font-bold text-xs sm:text-sm transition-all whitespace-nowrap">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Sampah
                @if($trashedCount > 0)
                <span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[9px] font-bold
                             min-w-[18px] h-[18px] flex items-center justify-center rounded-full shadow-sm px-1">
                    {{ $trashedCount > 99 ? '99+' : $trashedCount }}
                </span>
                @endif
            </a>

        </div>
    </div>{{-- /header --}}


    {{-- ═══════════════════════════════════════════════════════════════════════════
         FILTER SECTION
    ═══════════════════════════════════════════════════════════════════════════ --}}
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

        {{-- Filter grid: 2 kolom di mobile, 5 di layar sedang ke atas --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-3 items-center">
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
            $perPage != 15 ||
            $sortBy !== 'enrollments.created_at' ||
            $sortDir !== 'desc' ||
            count($selectedRows) > 0
            )
            <div class="col-span-2 sm:col-span-1">
                {{-- Gunakan wire:click.prevent jika tombol berada di dalam form, atau pastikan type="button" --}}
                <button type="button"
                    wire:click="resetFilters"
                    wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-500/10 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all border border-rose-100 dark:border-rose-500/20">

                    <svg wire:loading.remove wire:target="resetFilters" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>

                    {{-- Indikator Loading saat proses reset --}}
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
                {{-- Export terpilih CSV --}}
                <button wire:click="prepareExportCsv('selected')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5
                           bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400
                           border border-emerald-200 dark:border-emerald-800
                           text-xs font-bold rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    CSV
                </button>

                {{-- Export terpilih XLSX (Direct) --}}
                <button wire:click="exportXlsxDirect('selected')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5
                           bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-400
                           border border-violet-200 dark:border-violet-800
                           text-xs font-bold rounded-xl hover:bg-violet-100 dark:hover:bg-violet-500/20 transition-all">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V7l-5-5H5a1 1 0 00-1 1v16a1 1 0 001 1z" />
                    </svg>
                    XLSX (Direct)
                </button>

                <div class="w-px h-5 bg-indigo-200 dark:bg-indigo-700 self-center"></div>

                {{-- Buang ke Sampah --}}
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

                {{-- Batal --}}
                <button wire:click="resetSelection"
                    class="text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-300
                           px-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-700 transition-all">
                    Batal
                </button>
            </div>
        </div>
        @endif

    </div>{{-- /filter section --}}


    {{-- ═══════════════════════════════════════════════════════════════════════════
         TABLE SECTION
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="relative bg-white dark:bg-gray-900 rounded-[2rem] shadow-sm
                border border-slate-200/60 dark:border-gray-800 overflow-hidden min-h-[400px]">

        {{-- Loading overlay --}}
        <div wire:loading.flex
            class="absolute inset-0 z-10 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[1px]
                   items-center justify-center">
            <div class="flex items-center px-4 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-full border dark:border-gray-700">
                <svg class="animate-spin h-4 w-4 text-indigo-600 mr-2.5" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-xs sm:text-sm font-medium">Memuat data…</span>
            </div>
        </div>

        {{-- Tabel — scroll horizontal di mobile, min-width agar kolom tidak remuk --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" style="min-width:560px">
                <thead class="bg-slate-50 dark:bg-gray-800/50 border-b border-slate-200/60 dark:border-gray-800 text-slate-500">
                    <tr>
                        <th class="p-3 sm:p-4 w-9 sm:w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </th>
                        {{-- No. — tersembunyi di mobile (< sm) --}}
                        <th class="hidden sm:table-cell p-3 sm:p-4 w-10 text-center text-xs font-bold uppercase tracking-wider">
                            No.
                        </th>
                        <th class="p-3 sm:p-4 text-xs font-bold uppercase tracking-wider cursor-pointer group"
                            wire:click="setSort('students.nim')">
                            <div class="flex items-center gap-1.5">
                                Mahasiswa
                                <span class="text-slate-300 group-hover:text-indigo-500 transition-colors">
                                    {{ $sortBy === 'students.nim' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </div>
                        </th>
                        <th class="p-3 sm:p-4 text-xs font-bold uppercase tracking-wider cursor-pointer group"
                            wire:click="setSort('courses.code')">
                            <div class="flex items-center gap-1.5">
                                Mata Kuliah
                                <span class="text-slate-300 group-hover:text-indigo-500 transition-colors">
                                    {{ $sortBy === 'courses.code' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </div>
                        </th>
                        {{-- Status — tersembunyi di mobile, status tampil inline di baris Mahasiswa --}}
                        <th class="hidden sm:table-cell p-3 sm:p-4 text-xs font-bold uppercase tracking-wider">
                            Status
                        </th>
                        <th class="p-3 sm:p-4 text-xs font-bold uppercase tracking-wider text-right">
                            Opsi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                    @forelse($enrollments as $enrollment)
                    <tr wire:key="row-{{ $enrollment->id }}"
                        class="hover:bg-slate-50/60 dark:hover:bg-gray-800/30 transition-colors">

                        {{-- Checkbox --}}
                        <td class="p-3 sm:p-4 text-center" wire:key="td-chk-{{ $enrollment->id }}">
                            <input type="checkbox" wire:model.live="selectedRows"
                                value="{{ $enrollment->id }}"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </td>

                        {{-- No. --}}
                        <td class="hidden sm:table-cell p-3 sm:p-4 text-center text-xs font-medium text-slate-400">
                            {{ $enrollments->firstItem() + $loop->index }}
                        </td>

                        {{-- Mahasiswa --}}
                        <td class="p-3 sm:p-4">
                            <div class="font-bold text-sm text-slate-700 dark:text-gray-200 leading-snug">
                                {{ $enrollment->student_name }}
                            </div>
                            <div class="flex flex-wrap items-center gap-1 mt-0.5">
                                <span class="bg-slate-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-[10px] text-slate-500 dark:text-gray-400">
                                    {{ $enrollment->academic_year }}
                                </span>
                                <span class="text-slate-300 text-[10px]">•</span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $enrollment->semester == 1 ? 'Ganjil' : 'Genap' }}
                                </span>
                                {{-- Badge status tampil di mobile (kolom Status disembunyikan) --}}
                                <span class="sm:hidden px-1.5 py-0.5 text-[9px] font-black tracking-wider rounded
                                    {{ $enrollment->status === 'APPROVED'  ? 'bg-emerald-100 text-emerald-700' :
                                       ($enrollment->status === 'REJECTED'  ? 'bg-rose-100 text-rose-700' :
                                       ($enrollment->status === 'SUBMITTED' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600')) }}">
                                    {{ $enrollment->status }}
                                </span>
                            </div>
                        </td>

                        {{-- Mata Kuliah --}}
                        <td class="p-3 sm:p-4">
                            <span class="inline-block px-2 py-0.5 text-xs font-bold
                                         bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400
                                         rounded-lg">
                                {{ $enrollment->course_code }}
                            </span>
                            <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-0.5 leading-tight">
                                {{ $enrollment->course_name }}
                            </p>
                        </td>

                        {{-- Status (kolom penuh, hanya muncul di sm+) --}}
                        <td class="hidden sm:table-cell p-3 sm:p-4">
                            <span class="px-2.5 py-1 text-[10px] font-black tracking-wider rounded-md
                                {{ $enrollment->status === 'APPROVED'  ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' :
                                   ($enrollment->status === 'REJECTED'  ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' :
                                   ($enrollment->status === 'SUBMITTED' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400'
                                                                        : 'bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300')) }}">
                                {{ $enrollment->status }}
                            </span>
                        </td>

                        {{-- Opsi --}}
                        <td class="p-3 sm:p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="edit({{ $enrollment->id }})"
                                    class="p-1.5 sm:p-2 text-slate-400 hover:text-indigo-600
                                           hover:bg-indigo-50 dark:hover:bg-gray-700 rounded-lg transition-all"
                                    title="Edit">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="confirmTrash({{ $enrollment->id }})"
                                    class="p-1.5 sm:p-2 text-slate-400 hover:text-rose-600
                                           hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all"
                                    title="Ke Sampah">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                                    <svg class="w-7 h-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-gray-300">Tidak ada data Enrollment</h3>
                                <p class="text-xs text-slate-400 mt-1">Gunakan filter pencarian atau tambahkan data baru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Footer: per page + info total + pagination ────────────────────── --}}
        <div class="px-4 sm:px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                {{-- Kiri: per page + info record --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-medium text-slate-500 dark:text-gray-400 whitespace-nowrap">
                            Tampilkan
                        </label>
                        <select wire:model.live="perPage"
                            class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700
                                   rounded-xl text-xs py-1.5 px-2.5 font-bold text-slate-700 dark:text-gray-300
                                   focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 cursor-pointer">
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="60">60</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                            <option value="0">Semua</option>
                        </select>
                    </div>

                    <div class="hidden sm:block h-4 w-px bg-slate-200 dark:bg-gray-700"></div>

                    <p class="text-xs text-slate-400 dark:text-gray-500">
                        @if($enrollments->total() > 0)
                        @if($perPage == 0)
                        Semua
                        <span class="font-bold text-slate-600 dark:text-gray-300">{{ number_format($enrollments->total()) }}</span>
                        data
                        @else
                        <span class="font-bold text-slate-600 dark:text-gray-300">
                            {{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }}
                        </span>
                        dari
                        <span class="font-bold text-slate-600 dark:text-gray-300">
                            {{ number_format($enrollments->total()) }}
                        </span>
                        data
                        @endif
                        @else
                        Tidak ada data
                        @endif
                    </p>
                </div>

                {{-- Kanan: pagination links — hanya muncul kalau ada lebih dari 1 halaman --}}
                @if($perPage != 0 && $enrollments->hasPages())
                <div class="overflow-x-auto pb-1 sm:pb-0">
                    {{ $enrollments->links() }}
                </div>
                @endif

            </div>
        </div>

    </div>{{-- /table section --}}

    @include('livewire.partials.enrollment-modals')

</div>

<script>
    // ==========================================================================
    // EXPORT — TS-13: Export 5 Juta Data (CSV / XLSX Direct / XLSX Async)
    // ==========================================================================

    /**
     * triggerDownload(url)
     * Buat elemen <a> sementara, klik, lalu hapus.
     * Cara ini melewati blokir popup browser pada window.location / window.open.
     */
    function triggerDownload(url) {
        const a = document.createElement('a');
        a.href = url;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        setTimeout(() => a.remove(), 2000);
    }

    // ── 1. CSV READY — streaming langsung ke browser ────────────────────────
    //
    // Dipicu setelah prepareExportCsv() menyimpan filter ke Cache.
    // Controller akan stream CSV langsung via response()->streamDownload().
    // Tidak ada batas ukuran file — cocok untuk 5 juta baris.
    window.addEventListener('csv-ready', function(e) {
        const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
        triggerDownload(detail.url);

        Swal.fire({
            title: '⬇️ Download Dimulai',
            html: `File <strong>${detail.file ?? 'export'}</strong> sedang diunduh.<br>
                   <small class="text-slate-500 block mt-1">
                       Untuk data sangat besar (jutaan baris), browser mungkin
                       menampilkan ukuran file 0 KB dulu — ini normal karena
                       streaming. Tunggu hingga unduhan benar-benar selesai.
                   </small>`,
            icon: 'success',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#059669',
            timer: 10000,
            timerProgressBar: true,
        });
    });

    // ── 2. XLSX ASYNC — dispatch job via POST (CSRF), lalu polling status ────
    //
    // MENGAPA POST?
    // Laravel melindungi semua route non-GET/non-HEAD dengan CSRF token.
    // Jika menggunakan GET, server akan mengembalikan 419 Page Expired.
    // Frontend HARUS menyertakan header X-CSRF-TOKEN yang diambil dari meta tag.
    window.addEventListener('xlsx-job-ready', function(e) {
        const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
        const token = detail.token;
        const dispatchUrl = detail.dispatch_url;
        const statusUrl = detail.status_url;

        // Tampilkan modal awal agar user tahu proses dimulai
        Swal.fire({
            title: '🚀 Memulai Export XLSX',
            html: `<p class="text-sm text-slate-600">Mengirim permintaan ke server…</p>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        // POST ke /enrollments/export/xlsx-dispatch
        fetch(dispatchUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token
                }),
            })
            .then(r => {
                if (!r.ok) {
                    return r.text().then(text => {
                        throw new Error(`Server error ${r.status}: ${text.substring(0, 300)}`);
                    });
                }
                return r.json();
            })
            .then(data => {
                if (data.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memulai Export',
                        text: data.error
                    });
                    return;
                }
                // Job berhasil di-dispatch — mulai polling status
                startXlsxPolling(token, statusUrl);
            })
            .catch(err => {
                console.error('[xlsx-job-ready] dispatch error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memulai Export',
                    text: err.message || 'Tidak dapat menghubungi server.',
                });
            });
    });

    /**
     * startXlsxPolling(token, statusUrl)
     * ─────────────────────────────────────────────────────────────────────────
     * Poll /enrollments/export/xlsx-status?token=... setiap 3 detik.
     *
     * Tiga kemungkinan respons:
     *   status = 'processing' → update progress bar + counter baris
     *   status = 'ready'      → tampilkan tombol download
     *   status = 'error'      → tampilkan pesan error
     *
     * Progress bar: karena kita tidak tahu total baris di awal (COUNT bisa
     * lambat untuk 5 juta baris), kita gunakan pendekatan "growing bar":
     *   - Setiap 10.000 baris yang diproses = +0.5% lebar bar (up to 90%)
     *   - Saat selesai = 100%
     * Ini memberikan indikasi visual yang cukup akurat tanpa query COUNT.
     */
    function startXlsxPolling(token, statusUrl) {
        let rowsProcessed = 0;
        let consecutiveErrors = 0;
        const MAX_ERRORS = 5; // Hentikan polling setelah 5 error jaringan berturut-turut

        Swal.fire({
            title: '⏳ Menyiapkan File XLSX',
            html: `
                <div id="swal-progress-text" class="text-sm text-slate-600 font-medium">
                    Memulai proses export…
                </div>
                <div class="mt-3 w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div id="swal-progress-bar"
                         class="bg-violet-500 h-2.5 rounded-full transition-all duration-700 ease-out"
                         style="width: 3%"></div>
                </div>
                <div id="swal-rows-info" class="text-[11px] text-slate-400 mt-2">
                    Membaca data dari database…
                </div>
                <p class="text-[11px] text-slate-400 mt-3 border-t border-slate-100 pt-2">
                    💡 Halaman ini tidak perlu ditutup — notifikasi muncul otomatis saat siap.
                    File tersedia selama <strong>24 jam</strong>.
                </p>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: '✕ Batalkan Polling',
            cancelButtonColor: '#94a3b8',
            didOpen: () => Swal.showLoading(Swal.getCancelButton()),
        }).then(result => {
            if (result.dismiss === Swal.DismissReason.cancel) {
                clearInterval(window._xlsxPollInterval);
            }
        });

        window._xlsxPollInterval = setInterval(() => {
            fetch(`${statusUrl}?token=${token}`, {
                    headers: {
                        'Accept': 'application/json'
                    },
                })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    consecutiveErrors = 0; // Reset error counter jika berhasil

                    const bar = document.getElementById('swal-progress-bar');
                    const text = document.getElementById('swal-progress-text');
                    const rowsInfo = document.getElementById('swal-rows-info');

                    if (data.status === 'ready') {
                        // ── Selesai ────────────────────────────────────────────
                        clearInterval(window._xlsxPollInterval);

                        if (bar) bar.style.width = '100%';
                        if (text) text.textContent = `✅ Selesai! ${Number(data.rows).toLocaleString('id-ID')} baris diproses.`;

                        const filesInfo = data.files && data.files > 1 ?
                            `<br><small class="text-slate-500">File dibagi menjadi ${data.files} XLSX (maks. 1 juta baris/file) dalam satu ZIP.</small>` :
                            '';

                        setTimeout(() => {
                            Swal.fire({
                                title: '✅ File XLSX Siap!',
                                html: `<strong>${Number(data.rows).toLocaleString('id-ID')}</strong> baris berhasil diexport.
                                   ${filesInfo}
                                   <br><small class="text-slate-400">Link tersedia selama 24 jam.</small>`,
                                icon: 'success',
                                confirmButtonText: '⬇️ Download Sekarang',
                                confirmButtonColor: '#7c3aed',
                                showCancelButton: true,
                                cancelButtonText: 'Nanti',
                            }).then(result => {
                                if (result.isConfirmed) triggerDownload(data.download_url);
                            });
                        }, 400);

                    } else if (data.status === 'error') {
                        // ── Error dari Job ─────────────────────────────────────
                        clearInterval(window._xlsxPollInterval);
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Gagal',
                            text: data.message ?? 'Terjadi kesalahan pada server. Silakan coba lagi.',
                        });

                    } else {
                        // ── Masih processing ───────────────────────────────────
                        rowsProcessed = data.rows_processed ?? rowsProcessed;

                        if (text) {
                            text.textContent = `Sedang memproses… ${Number(rowsProcessed).toLocaleString('id-ID')} baris`;
                        }
                        if (rowsInfo && rowsProcessed > 0) {
                            rowsInfo.textContent = `Batch terakhir: baris ke-${Number(rowsProcessed).toLocaleString('id-ID')}`;
                        }

                        // Growing bar: setiap 10.000 baris ≈ +0.5%, maksimum 90%
                        if (bar) {
                            const current = parseFloat(bar.style.width) || 3;
                            const target = Math.min(3 + (rowsProcessed / 10000) * 0.5, 90);
                            bar.style.width = Math.max(current, target) + '%';
                        }
                    }
                })
                .catch(err => {
                    consecutiveErrors++;
                    console.warn(`[xlsx-polling] Error ke-${consecutiveErrors}: ${err.message}`);

                    if (consecutiveErrors >= MAX_ERRORS) {
                        clearInterval(window._xlsxPollInterval);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Koneksi Terputus',
                            html: `Tidak dapat menghubungi server setelah ${MAX_ERRORS} percobaan.<br>
                               <small class="text-slate-500">Proses export di server mungkin masih berjalan.
                               Refresh halaman dan cek kembali nanti.</small>`,
                            confirmButtonText: 'Refresh Halaman',
                            confirmButtonColor: '#7c3aed',
                            showCancelButton: true,
                            cancelButtonText: 'Tutup',
                        }).then(r => {
                            if (r.isConfirmed) window.location.reload();
                        });
                    }
                });
        }, 3000); // Poll setiap 3 detik
    }

    // ── 3. EXPORT ERROR GLOBAL ───────────────────────────────────────────────
    //
    // Dipicu dari sisi Livewire (PHP) saat terjadi error sebelum URL dibuat.
    window.addEventListener('export-error', function(e) {
        const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
        Swal.fire({
            icon: 'error',
            title: 'Gagal Export',
            text: detail.message ?? 'Terjadi kesalahan. Silakan coba lagi.',
        });
    });
</script>
