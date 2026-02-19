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
                Sampah Mahasiswa
            </h1>
        </div>
    </div>

    {{-- ─── Filter & Search Bar ─── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search Input --}}

            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari NIM, Nama, atau Email..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            {{-- Custom Filters & Reset --}}
            <div class="w-full lg:w-48 flex gap-2">
                <select wire:model.live="filterKrs" class="w-full py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Semua Status KRS</option>
                    <option value="active">Mahasiswa Ber-KRS</option>
                    <option value="none">Belum Isi KRS</option>
                </select>

                {{-- Reset Button (Hanya muncul jika filter aktif) --}}
                @if($search || $filterKrs || $sortField !== 'deleted_at')
                <button wire:click="resetFilters"
                    class="p-3 bg-red-50 text-red-600 rounded-2xl hover:bg-red-100 transition-all shadow-sm"
                    title="Reset Semua Filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" />
                    </svg>
                </button>
                @endif
            </div>

            {{-- Bulk Actions --}}
            <div class="flex items-center gap-2 flex-wrap">
                @if(count($selectedRows) > 0)
                <button wire:click="confirmRestore()" class="px-5 py-3 bg-emerald-600 text-white text-sm font-bold rounded-2xl hover:bg-emerald-700 transition shadow-md flex items-center gap-2">
                    Pulihkan ({{ count($selectedRows) }})
                </button>
                <button wire:click="confirmForceDelete()" class="px-5 py-3 bg-red-600 text-white text-sm font-bold rounded-2xl hover:bg-red-700 transition shadow-md">
                    Hapus Permanen
                </button>
                @elseif($students->total() > 0)
                <button wire:click="confirmRestore('all')" class="px-5 py-3 bg-indigo-50 text-indigo-700 text-sm font-bold rounded-2xl hover:bg-indigo-100 transition">
                    Pulihkan Semua
                </button>
                <button wire:click="confirmForceDelete('all')" class="px-5 py-3 bg-red-50 text-red-600 text-sm font-bold rounded-2xl hover:bg-red-100 transition">
                    Kosongkan Sampah
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-red-50/50 dark:bg-red-900/10 border-b border-gray-100 dark:border-gray-800">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-5 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-red-500 focus:ring-red-400 size-4">
                        </th>
                        <th class="p-5 cursor-pointer group" wire:click="sortBy('nim')">
                            <div class="flex items-center gap-1 group-hover:text-indigo-600 transition">
                                NIM @if($sortField === 'nim') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </div>
                        </th>
                        <th class="p-5 cursor-pointer group" wire:click="sortBy('name')">
                            <div class="flex items-center gap-1 group-hover:text-indigo-600 transition">
                                Nama Mahasiswa @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </div>
                        </th>
                        <th class="p-5">Email</th>
                        <th class="p-5 text-center">Total KRS</th>
                        <th class="p-5 cursor-pointer group" wire:click="sortBy('deleted_at')">
                            <div class="flex items-center gap-1 group-hover:text-indigo-600 transition">
                                Waktu Hapus @if($sortField === 'deleted_at') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </div>
                        </th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($students as $student)
                    <tr wire:key="trashed-{{ $student->id }}" class="hover:bg-red-50/20 transition-colors opacity-80">
                        <td class="p-5 text-center">
                            <input type="checkbox" wire:model.live="selectedRows" value="{{ $student->id }}" class="rounded border-gray-300 text-red-500 size-4">
                        </td>
                        <td class="p-5 font-bold text-slate-400 font-mono line-through tracking-tighter">{{ $student->nim }}</td>
                        <td class="p-5 font-semibold text-slate-400 line-through">{{ $student->name }}</td>
                        <td class="p-5 text-slate-400 italic line-through">{{ $student->email }}</td>
                        <td class="p-5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                {{ $student->enrollments_count }} Matkul
                            </span>
                        </td>
                        <td class="p-5">
                            <span class="text-xs text-slate-400 font-medium block">{{ $student->deleted_at->diffForHumans() }}</span>
                            <span class="text-[9px] text-slate-300">{{ $student->deleted_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="p-5 text-right">
                            <div class="flex justify-end gap-1">
                                <button wire:click="confirmRestore({{ $student->id }})" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="Pulihkan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" />
                                    </svg>
                                </button>
                                <button wire:click="confirmForceDelete({{ $student->id }})" class="p-2 text-red-400 hover:bg-red-50 rounded-xl transition" title="Hapus Permanen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-slate-50 p-6 rounded-full mb-4">
                                    <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-400">Data Tidak Ditemukan</h3>
                                <p class="text-slate-400 text-sm mt-1">Coba sesuaikan pencarian atau bersihkan filter.</p>
                                <button wire:click="resetFilters" class="mt-4 px-6 py-2 bg-indigo-600 text-white text-xs font-bold rounded-full hover:bg-indigo-700 transition">
                                    Bersihkan Semua Filter
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="p-6 border-t border-slate-100 bg-slate-50/40">
            {{ $students->links() }}
        </div>
    </div>
</div>
