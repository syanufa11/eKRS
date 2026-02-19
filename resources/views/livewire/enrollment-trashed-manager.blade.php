<div class="p-6 antialiased text-gray-800 dark:text-gray-200" x-data>

    {{-- ─── Header Section ─── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="#" wire:click.prevent="goBack"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Daftar Aktif
                </a>
            </div>
            <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Sampah Pengambilan KRS
            </h1>
        </div>
    </div>

    {{-- ─── Filter & Bulk Actions Bar ─── --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.500ms="search"
                    type="text"
                    placeholder="Cari Apapun (NIM, Nama, Kode MK, Tahun)..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
            </div>

            {{-- Custom Filters --}}
            <div class="flex items-center gap-2">
                <select wire:model.live="filterYear" class="py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($academicYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterSemester" class="py-2.5 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Semua Sem</option>
                    @foreach([1,2,3,4,5,6,7,8] as $s)
                    <option value="{{ $s }}">Sem {{ $s }}</option>
                    @endforeach
                </select>

                {{-- Global Reset Button --}}
                @if($search || $filterYear || $filterSemester || $sortField !== 'deleted_at')
                <button wire:click="resetFilters" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition shadow-sm" title="Bersihkan Filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" />
                    </svg>
                </button>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3">
                @if(count($selectedRows) > 0)
                <button wire:click="confirmRestore()" class="px-4 py-2.5 bg-emerald-50 text-emerald-700 text-sm font-medium rounded-xl hover:bg-emerald-100 transition flex items-center gap-2">
                    Pulihkan ({{ count($selectedRows) }})
                </button>
                <button wire:click="confirmForceDelete()" class="px-4 py-2.5 bg-red-50 text-red-600 text-sm font-medium rounded-xl hover:bg-red-100 transition flex items-center gap-2">
                    Hapus Permanen
                </button>
                @elseif($enrollments->total() > 0)
                <button wire:click="confirmRestore('all')" class="px-4 py-2.5 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-xl hover:bg-indigo-100 transition">Pulihkan Semua</button>
                <button wire:click="confirmForceDelete('all')" class="px-4 py-2.5 bg-red-50 text-red-600 text-sm font-medium rounded-xl hover:bg-red-100 transition">Kosongkan Sampah</button>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Table Section ─── --}}
    <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">

        {{-- Loading Overlay --}}
        <div wire:loading.flex class="absolute inset-0 z-10 bg-white/50 dark:bg-gray-900/50 backdrop-blur-[1px] items-center justify-center">
            <div class="animate-spin h-8 w-8 text-indigo-600 border-4 border-slate-200 border-t-indigo-600 rounded-full"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-red-50/50 dark:bg-red-900/10 border-b border-gray-200 dark:border-gray-800">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-red-500 size-4">
                        </th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600" wire:click="sortBy('student_id')">
                            Mahasiswa @if($sortField === 'student_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600" wire:click="sortBy('course_id')">
                            Mata Kuliah @if($sortField === 'course_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 text-center">Semester/Tahun</th>
                        <th class="p-4 cursor-pointer hover:text-indigo-600" wire:click="sortBy('deleted_at')">
                            Dihapus @if($sortField === 'deleted_at') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                        </th>
                        <th class="p-4 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($enrollments as $enrollment)
                    <tr wire:key="trash-{{ $enrollment->id }}" class="hover:bg-red-50/20 dark:hover:bg-red-900/5 transition-colors opacity-80">
                        <td class="p-4 text-center">
                            <input type="checkbox" wire:model.live="selectedRows" value="{{ $enrollment->id }}" class="rounded border-gray-300 text-red-500 size-4">
                        </td>
                        <td class="p-4">
                            <p class="font-bold text-gray-400 line-through">{{ $enrollment->student->name ?? 'Mahasiswa Dihapus' }}</p>
                            <p class="text-[10px] text-gray-400 font-mono">{{ $enrollment->student->nim ?? '-' }}</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-400 rounded-lg line-through uppercase">{{ $enrollment->course->code ?? '???' }}</span>
                            <p class="text-xs text-gray-400 mt-1 line-through">{{ $enrollment->course->name ?? 'Mata Kuliah Dihapus' }}</p>
                        </td>
                        <td class="p-4 text-center text-gray-400 line-through text-sm">
                            Sem {{ $enrollment->semester }}<br>
                            <span class="text-[10px]">{{ $enrollment->academic_year }}</span>
                        </td>
                        <td class="p-4">
                            <span class="text-xs text-gray-400 block">{{ $enrollment->deleted_at->diffForHumans() }}</span>
                            <span class="text-[9px] text-gray-300">{{ $enrollment->deleted_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="p-4 text-right flex justify-end gap-1">
                            <button wire:click="confirmRestore({{ $enrollment->id }})" class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl transition" title="Pulihkan">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" />
                                </svg>
                            </button>
                            <button wire:click="confirmForceDelete({{ $enrollment->id }})" class="p-2 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition" title="Hapus Permanen">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-full mb-4">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-400">Sampah Kosong</h3>
                                <p class="text-gray-500 text-sm">Tidak ada data pengambilan KRS yang ditemukan.</p>
                                <button wire:click="resetFilters" class="mt-4 text-indigo-600 text-sm font-semibold underline">Bersihkan semua filter</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
            {{ $enrollments->links() }}
        </div>
    </div>
</div>
