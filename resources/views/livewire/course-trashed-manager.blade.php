<div class="p-6 antialiased text-gray-800 dark:text-gray-200" x-data>

    {{-- ─── Header ─── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <a href="#" wire:click.prevent="goBack"
                class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Daftar Aktif
            </a>
            <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Sampah Mata Kuliah
            </h1>
        </div>
    </div>

    {{-- ─── Filter & Search Bar ─── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode atau nama..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            {{-- Filter SKS --}}
            <div class="w-full lg:w-48 flex gap-2">
                <select wire:model.live="filterCredits" class="w-full py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Semua SKS</option>
                    @foreach([1,2,3,4,6,8] as $sks)
                    <option value="{{ $sks }}">{{ $sks }} SKS</option>
                    @endforeach
                </select>

                {{-- Tombol Reset --}}
                @if($search !== '' || $filterCredits !== '' || $sortField !== 'deleted_at')
                <button wire:click="resetFilters"
                    class="px-3 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-red-500 rounded-xl transition-colors"
                    title="Reset Filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                @endif
            </div>

            {{-- Bulk Actions --}}
            <div class="flex items-center gap-2 flex-wrap">
                @if(count($selectedRows) > 0)
                <button wire:click="confirmRestore()" class="px-4 py-2.5 bg-emerald-50 text-emerald-700 text-sm font-medium rounded-xl hover:bg-emerald-100 transition flex items-center">
                    Pulihkan ({{ count($selectedRows) }})
                </button>
                <button wire:click="confirmForceDelete()" class="px-4 py-2.5 bg-red-50 text-red-600 text-sm font-medium rounded-xl hover:bg-red-100 transition">
                    Hapus Permanen
                </button>
                @elseif($courses->total() > 0)
                <button wire:click="confirmRestore('all')" class="px-4 py-2.5 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-xl hover:bg-indigo-100 transition">
                    Pulihkan Semua
                </button>
                <button wire:click="confirmForceDelete('all')" class="px-4 py-2.5 bg-red-50 text-red-600 text-sm font-medium rounded-xl hover:bg-red-100 transition">
                    Kosongkan Sampah
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Table ─── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-red-50/50 dark:bg-red-900/10 border-b border-gray-100 dark:border-gray-800">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-red-500 focus:ring-red-400 size-4">
                        </th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600 transition" wire:click="sortBy('code')">
                            Kode @if($sortField === 'code') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600 transition" wire:click="sortBy('name')">
                            Mata Kuliah @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600 text-center" wire:click="sortBy('credits')">
                            SKS @if($sortField === 'credits') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600" wire:click="sortBy('deleted_at')">
                            Dihapus @if($sortField === 'deleted_at') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($courses as $course)
                    <tr wire:key="trash-{{ $course->id }}" class="hover:bg-red-50/30 dark:hover:bg-red-900/5 transition-colors">
                        <td class="p-4 text-center">
                            <input type="checkbox" wire:model.live="selectedRows" value="{{ $course->id }}" class="rounded border-gray-300 text-red-500 size-4">
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-400 dark:bg-gray-800 rounded line-through">
                                {{ $course->code }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-gray-400 line-through">{{ $course->name }}</td>
                        <td class="p-4 text-center text-gray-400 italic">{{ $course->credits }}</td>
                        <td class="p-4">
                            <span class="text-sm text-gray-400 block">{{ $course->deleted_at->diffForHumans() }}</span>
                            <span class="text-[10px] text-gray-300">{{ $course->deleted_at->format('d M Y, H:i') }}</span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="confirmRestore({{ $course->id }})" class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition" title="Pulihkan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                                <button wire:click="confirmForceDelete({{ $course->id }})" class="p-2 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus Permanen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">Tidak ada data</h3>
                                <p class="text-gray-500 text-sm">Sampah kosong atau tidak ada hasil untuk pencarian ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ─── Footer/Pagination ─── --}}
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
            {{ $courses->links() }}
        </div>
    </div>
</div>
