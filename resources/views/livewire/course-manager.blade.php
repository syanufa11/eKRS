<div class="p-6 antialiased text-gray-800 dark:text-gray-200" x-data="{ open: $wire.entangle('isOpen') }">

    {{-- ─── Header ─────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Course Management</h1>
            <p class="text-sm text-gray-500">Kelola kurikulum dan bobot SKS mahasiswa Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Trash link with badge --}}
            @if($trashedCount > 0)
            <a href="{{ route('courses.trashed') }}"
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

            <button wire:click="create" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-sm hover:shadow-indigo-200 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Mata Kuliah
            </button>
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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode atau nama mata kuliah..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
            </div>

            <div class="flex items-center gap-3">
                <select wire:model.live="filterCredits"
                    class="bg-gray-50 dark:bg-gray-800 border-none rounded-xl text-sm py-2.5 px-4 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua SKS</option>
                    @foreach(range(1, 6) as $sks)
                    <option value="{{ $sks }}">{{ $sks }} SKS</option>
                    @endforeach
                </select>

                @if($search || $filterCredits || $sortBy !== 'code')
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
                <div wire:key="bulk-action-course" class="flex gap-2 items-center">
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
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer group" wire:click="setSort('code')">
                            <div class="flex items-center">
                                Kode MK
                                <span class="ml-2 text-gray-400 group-hover:text-indigo-500">
                                    @if($sortBy === 'code') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @else ↕ @endif
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer group" wire:click="setSort('name')">
                            <div class="flex items-center">
                                Nama Mata Kuliah
                                <span class="ml-2 text-gray-400 group-hover:text-indigo-500">
                                    @if($sortBy === 'name') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @else ↕ @endif
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bobot SKS</th>
                        <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($courses as $course)
                    <tr wire:key="row-{{ $course->id }}" class="hover:bg-gray-50/80 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="p-4 text-center" wire:key="td-chk-{{ $course->id }}">
                            <input type="checkbox" wire:model.live="selectedRows" value="{{ $course->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 size-4">
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 rounded-lg">
                                {{ $course->code }}
                            </span>
                        </td>
                        <td class="p-4 font-medium">{{ $course->name }}</td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <span class="font-bold mr-1">{{ $course->credits }}</span>
                                <span class="text-xs text-gray-400 uppercase">SKS</span>
                            </div>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Edit --}}
                                <button wire:click="edit({{ $course->id }})"
                                    class="p-2 hover:bg-white dark:hover:bg-gray-700 rounded-lg text-gray-500 hover:text-indigo-600 shadow-sm transition-all border border-transparent hover:border-gray-200"
                                    title="Edit">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                {{-- Move to Trash --}}
                                <button wire:click="confirmTrash({{ $course->id }})"
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
                    <tr wire:key="empty-row-course">
                        <td colspan="5" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">Tidak ada data ditemukan</h3>
                                <p class="text-gray-500 text-sm max-w-xs mx-auto">Coba ubah kata kunci pencarian atau bersihkan filter yang ada.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-800">
            {{ $courses->links() }}
        </div>
    </div>

    {{-- ─── Modal Form ──────────────────────────────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="open"
            x-cloak
            @keydown.window.escape="open = false"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">

            {{-- Overlay klik luar untuk menutup --}}
            <div x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                @click="open = false"
                class="absolute inset-0"></div>

            {{-- Kontainer Modal --}}
            <div x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-gray-800 overflow-hidden">

                <div class="p-8 lg:p-12">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                                {{ $courseId ? 'Edit Course' : 'Add New Course' }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Silakan isi form akademik berikut dengan benar.
                            </p>
                        </div>
                        {{-- Tombol Close --}}
                        <button @click="open = false" class="p-2 bg-gray-50 dark:bg-gray-800 rounded-xl text-gray-400 hover:text-rose-500 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="store" class="space-y-6">
                        {{-- Input Kode Mata Kuliah --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider text-xs">Kode MK (Maks. 6 Karakter)</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="code"
                                maxlength="6"
                                placeholder="Contoh: IF101"
                                class="dark:bg-gray-800 h-12 w-full rounded-xl border {{ $errors->has('code') ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-indigo-500' }} bg-transparent px-4 text-sm text-gray-800 dark:text-white uppercase transition shadow-sm" />
                            @error('code') <span class="text-xs text-red-500 mt-2 block font-medium italic">⚠️ {{ $message }}</span> @enderror
                        </div>

                        {{-- Input Nama Mata Kuliah --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider text-xs">Nama Mata Kuliah</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="name"
                                placeholder="Masukkan nama mata kuliah..."
                                class="dark:bg-gray-800 h-12 w-full rounded-xl border {{ $errors->has('name') ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-indigo-500' }} bg-transparent px-4 text-sm text-gray-800 dark:text-white transition shadow-sm" />
                            @error('name') <span class="text-xs text-red-500 mt-2 block font-medium italic">⚠️ {{ $message }}</span> @enderror
                        </div>

                        {{-- Input SKS --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider text-xs">Bobot SKS (1-6)</label>
                            <input type="number"
                                wire:model.live="credits"
                                min="1" max="6"
                                class="dark:bg-gray-800 h-12 w-full rounded-xl border {{ $errors->has('credits') ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-indigo-500' }} bg-transparent px-4 text-sm text-gray-800 dark:text-white transition shadow-sm" />
                            @error('credits') <span class="text-xs text-red-500 mt-2 block font-medium italic">⚠️ {{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center gap-4 mt-8 justify-end">
                            <button @click="open = false" type="button"
                                class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-400 hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-bold text-white shadow-lg shadow-indigo-200 dark:shadow-none transition active:scale-95 disabled:opacity-50">
                                <span wire:loading.remove wire:target="store">{{ $courseId ? 'Update Course' : 'Create Course' }}</span>
                                <span wire:loading wire:target="store">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
