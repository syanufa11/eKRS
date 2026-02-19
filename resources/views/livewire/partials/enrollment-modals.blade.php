{{-- Create/Edit Modal with TomSelect (Select2 Equivalent) --}}
<template x-teleport="body">
    <div x-show="open" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-5xl bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden">

            <div class="flex flex-col md:flex-row min-h-[550px]">
                {{-- Left Panel: Profile --}}
                <div class="w-full md:w-5/12 bg-slate-50/80 p-8 lg:p-12 border-r border-slate-100">
                    <div class="mb-10">
                        <div class="inline-flex w-14 h-14 bg-indigo-600 rounded-2xl items-center justify-center text-white mb-5 shadow-xl shadow-indigo-200">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-900 tracking-tight">Profil Mahasiswa</h4>
                        <p class="text-sm text-slate-500 mt-1">Lengkapi identitas pendaftar (NIM 8-12 digit).</p>
                    </div>

                    <div class="space-y-5">
                        {{-- NIM Mahasiswa --}}
                        <div>
                            <input type="text" inputmode="numeric"
                                onkeypress="return /[0-9]/i.test(event.key)"
                                placeholder="Contoh: 2021001"
                                :value="$wire.student_nim"
                                @input="$wire.set('student_nim', $event.target.value); $wire.call('validateField', 'student_nim')"
                                class="w-full h-12 bg-white rounded-xl border-none shadow-sm transition-all px-4 text-sm font-bold
                                    @error('student_nim') ring-2 ring-rose-400 @else ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 @enderror" />
                            @error('student_nim')
                            <span class="text-[11px] text-rose-500 mt-1.5 flex items-center gap-1 font-bold">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                            @enderror
                        </div>

                        {{-- Nama Lengkap --}}
                        <div>
                            <input type="text"
                                placeholder="Nama Mahasiswa"
                                :value="$wire.student_name"
                                @input="$wire.set('student_name', $event.target.value); $wire.call('validateField', 'student_name')"
                                class="w-full h-12 bg-white rounded-xl border-none shadow-sm transition-all px-4 text-sm font-medium
                                    @error('student_name') ring-2 ring-rose-400 @else ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 @enderror" />
                            @error('student_name')
                            <span class="text-[11px] text-rose-500 mt-1.5 flex items-center gap-1 font-bold">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <input type="email"
                                placeholder="mahasiswa@pcr.ac.id"
                                :value="$wire.student_email"
                                @input="$wire.set('student_email', $event.target.value); $wire.call('validateField', 'student_email')"
                                class="w-full h-12 bg-white rounded-xl border-none shadow-sm transition-all px-4 text-sm
                                    @error('student_email') ring-2 ring-rose-400 @else ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 @enderror" />
                            @error('student_email')
                            <span class="text-[11px] text-rose-500 mt-1.5 flex items-center gap-1 font-bold">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Right Panel: Academic --}}
                <div class="w-full md:w-7/12 p-8 lg:p-12 bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-10">
                            <div>
                                <h4 class="text-2xl font-black text-slate-900 tracking-tight">Detail Akademik</h4>
                                <p class="text-sm text-slate-500 mt-1">Pilih MK dan periode pendaftaran.</p>
                            </div>
                            <button wire:click="closeModal" class="p-2 bg-slate-50 rounded-xl text-slate-400 hover:text-rose-500 transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="store" class="space-y-6">
                            {{-- TomSelect for Course --}}
                            <div wire:ignore class="w-full">
                                <label class="mb-2 block text-[10px] font-black text-slate-400 uppercase tracking-widest">Pilih Mata Kuliah</label>
                                <select x-ref="courseSelect" x-init="
                                        const ts = new TomSelect($refs.courseSelect, {
                                            placeholder: 'Cari mata kuliah...',
                                            dropdownParent: 'body',
                                            render: { option: function(data, escape) { return `<div class='p-2'><div class='text-indigo-600 font-bold text-[10px]'>${escape(data.code || '')}</div><div class='text-sm font-medium'>${escape(data.text)}</div></div>`; } }
                                        });
                                        ts.on('change', (val) => {
                                            @this.set('course_id', val);
                                            @this.call('validateField', 'course_id');
                                        });
                                        $watch('$wire.course_id', (val) => { if (!val) ts.clear(); else if (val != ts.getValue()) ts.setValue(val); });
                                        if (@js($course_id)) ts.setValue(@js($course_id));
                                    " class="w-full">
                                    <option value="">Pilih Mata Kuliah...</option>
                                    @foreach($courses_list as $course) <option value="{{ $course->id }}" data-code="{{ $course->code }}">{{ $course->code }} - {{ $course->name }}</option> @endforeach
                                </select>
                                @error('course_id') <span class="text-[11px] text-rose-500 mt-2 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4 items-end">
                                <div class="flex flex-col">

                                    {{-- Tahun Ajaran (Year Range Picker) --}}
                                    <div x-data="{
                                    yearFrom: $wire.academic_year ? $wire.academic_year.split('/')[0] : '',
                                    yearTo:   $wire.academic_year ? $wire.academic_year.split('/')[1] : '',
                                    allYears: Array.from({ length: 31 }, (_, i) => String(2000 + i)),
                                    search: '',
                                    open: false,
                                    get filtered() {
                                        return this.search
                                            ? this.allYears.filter(y => y.startsWith(this.search))
                                            : this.allYears;
                                    },
                                    get canAdd() {
                                        const s = this.search.trim();
                                        return s.length === 4 && /^\d{4}$/.test(s) && !this.allYears.includes(s);
                                    },
                                    addAndSelect(v) {
                                        this.allYears = [...this.allYears, v].sort();
                                        this.select(v);
                                    },
                                    select(v) {
                                        this.yearFrom = v;
                                        this.yearTo   = String(parseInt(v) + 1);
                                        this.open     = false;
                                        this.search   = '';
                                        $wire.set('academic_year', v + '/' + this.yearTo);
                                        $wire.call('validateField', 'academic_year');
                                    }
                                }" x-init="$watch('$wire.academic_year', v => {
                                    if (v && v.includes('/')) {
                                        yearFrom = v.split('/')[0];
                                        yearTo   = v.split('/')[1];
                                    }
                                })" @click.outside="open = false" class="relative">
                                        <label class="mb-1.5 block text-[10px] font-black text-slate-400 uppercase tracking-widest">Tahun Ajaran</label>

                                        {{-- Trigger --}}
                                        <div @click="open = !open"
                                            class="flex items-center gap-1.5 @error('academic_year') ring-2 ring-rose-400 @else ring-1 ring-slate-100 @enderror rounded-xl px-3 h-12 bg-white transition-all cursor-pointer hover:ring-indigo-300"
                                            :class="open ? 'ring-2 ring-indigo-500' : ''">
                                            <span class="flex-1 text-sm font-bold" :class="yearFrom ? 'text-slate-700' : 'text-slate-300'" x-text="yearFrom || 'Pilih tahun...'"></span>
                                            <span class="text-slate-300 font-bold text-sm select-none" x-show="yearTo">/</span>
                                            <span class="flex-1 text-sm font-bold text-slate-500 text-center" x-text="yearTo" x-show="yearTo"></span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>

                                        {{-- Dropdown --}}
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                            class="absolute z-50 mt-1.5 w-44 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                                            {{-- Search --}}
                                            <div class="p-2 border-b border-slate-100">
                                                <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-3 py-1.5">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                    <input x-model="search" @click.stop type="text" placeholder="Cari tahun..."
                                                        class="bg-transparent border-none text-xs font-bold text-slate-700 focus:ring-0 p-0 w-full placeholder-slate-300"
                                                        x-ref="searchInput" x-init="$watch('open', v => v && $nextTick(() => $refs.searchInput.focus()))" />
                                                </div>
                                            </div>
                                            {{-- List --}}
                                            <ul class="max-h-44 overflow-y-auto py-1">
                                                {{-- Tambah tahun baru --}}
                                                <template x-if="canAdd">
                                                    <li @click="addAndSelect(search.trim())"
                                                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold cursor-pointer text-indigo-600 hover:bg-indigo-50 border-b border-slate-100 transition-colors">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        <span>Tambah <strong x-text="search.trim() + '/' + (parseInt(search.trim()) + 1)"></strong></span>
                                                    </li>
                                                </template>
                                                <template x-if="filtered.length === 0 && !canAdd">
                                                    <li class="px-4 py-3 text-xs text-slate-400 text-center">Tahun tidak ditemukan</li>
                                                </template>
                                                <template x-for="y in filtered" :key="y">
                                                    <li @click="select(y)"
                                                        class="flex items-center justify-between px-4 py-2 text-sm font-bold cursor-pointer transition-colors"
                                                        :class="y == yearFrom ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700 hover:bg-slate-50'">
                                                        <span x-text="y"></span>
                                                        <span class="text-xs text-slate-400 font-normal" x-text="'/ ' + (parseInt(y) + 1)"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                        @error('academic_year')
                                        <span class="text-[11px] text-rose-500 mt-1.5 flex items-center gap-1 font-bold">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $message }}
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex flex-col">

                                    {{-- Semester --}}
                                    <div>
                                        <label class="mb-2 block text-[10px] font-black text-slate-400 uppercase tracking-widest">Semester</label>
                                        <select
                                            @change="@this.set('semester', $event.target.value); @this.call('validateField', 'semester')"
                                            :value="$wire.semester"
                                            class="w-full h-12 rounded-xl border-none px-4 text-sm font-bold transition-all
                                            @error('semester') ring-2 ring-rose-400 @else ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-500 @enderror">
                                            <option value="1">Ganjil (1)</option>
                                            <option value="2">Genap (2)</option>
                                        </select>
                                        @error('semester')
                                        <span class="text-[11px] text-rose-500 mt-1.5 flex items-center gap-1 font-bold">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $message }}
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6" x-data="{ selectedStatus: @entangle('status') }">
                                <label class="mb-3 block text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    Status Validasi
                                </label>

                                <div class="grid grid-cols-4 gap-2">
                                    @foreach([
                                    'DRAFT' => ['bgClass' => 'bg-slate-600 border-slate-600 shadow-slate-200', 'label' => 'Draft'],
                                    'SUBMITTED' => ['bgClass' => 'bg-blue-600 border-blue-600 shadow-blue-200', 'label' => 'Submitted'],
                                    'APPROVED' => ['bgClass' => 'bg-emerald-600 border-emerald-600 shadow-emerald-200', 'label' => 'Approved'],
                                    'REJECTED' => ['bgClass' => 'bg-rose-600 border-rose-600 shadow-rose-200', 'label' => 'Rejected']
                                    ] as $value => $item)
                                    <button
                                        type="button"
                                        @click="selectedStatus = '{{ $value }}'"
                                        :class="{
                                            '{{ $item['bgClass'] }} text-white shadow-lg scale-105 z-10': selectedStatus === '{{ $value }}',
                                            'bg-slate-50 border-slate-100 text-slate-400 hover:bg-slate-100 hover:text-slate-600': selectedStatus !== '{{ $value }}'
                                        }"
                                        class="py-3 rounded-xl text-[9px] font-black transition-all duration-200 border uppercase tracking-tighter relative">
                                        {{ $item['label'] }}
                                    </button>
                                    @endforeach
                                </div>

                                @error('status')
                                <span class="text-[11px] text-rose-500 mt-2 block font-bold transition-opacity animate-pulse">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </form>
                    </div>

                    <div class="mt-12 flex flex-col sm:flex-row gap-4">
                        <button wire:click="closeModal" class="flex-1 py-4 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">Batalkan</button>
                        <button wire:click="store" wire:loading.attr="disabled" class="flex-[2] py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-2xl shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 uppercase tracking-widest text-xs">
                            <span wire:loading.remove wire:target="store">Simpan Perubahan</span>
                            <span wire:loading wire:target="store">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Detail Modal --}}
<template x-teleport="body">
    <div x-show="detailMode" x-cloak class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative w-full max-w-3xl rounded-[2.5rem] bg-white p-8 md:p-10 shadow-2xl border border-gray-100">
            <div class="flex items-center gap-5 mb-8 border-b pb-6">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shrink-0"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg></div>
                <div>
                    <h3 class="text-2xl font-black text-gray-800 leading-tight">{{ $selectedEnrollment->student->name ?? '-' }}</h3>
                    <p class="text-sm font-bold text-indigo-600 tracking-[0.2em] uppercase">{{ $selectedEnrollment->student->nim ?? '-' }}</p>
                </div>
                <button @click="detailMode = false" class="ml-auto p-2 text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
            </div>
            @if($selectedEnrollment)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-5">
                    <div class="flex flex-col p-5 bg-gray-50 rounded-3xl border border-gray-100"><span class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2">Email Address</span><span class="text-sm font-semibold break-all">{{ $selectedEnrollment->student->email }}</span></div>
                    <div class="flex flex-col p-5 bg-indigo-50/30 rounded-3xl border border-indigo-100/50"><span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-2">Mata Kuliah Terdaftar</span>
                        <div class="flex flex-col gap-1"><span class="text-xs font-black text-indigo-600 uppercase font-mono">{{ $selectedEnrollment->course->code }}</span><span class="text-base font-bold text-gray-800 leading-tight">{{ $selectedEnrollment->course->name }}</span></div>
                    </div>
                </div>
                <div class="space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-5 bg-gray-50 rounded-3xl border border-gray-100 text-center"><span class="text-[10px] text-gray-400 font-black uppercase tracking-widest block mb-1">Semester</span><span class="text-sm font-bold">{{ $selectedEnrollment->semester == 1 ? 'Ganjil' : 'Genap' }}</span></div>
                        <div class="p-5 bg-gray-50 rounded-3xl border border-gray-100 text-center"><span class="text-[10px] text-gray-400 font-black uppercase tracking-widest block mb-1">Thn Ajaran</span><span class="text-sm font-bold">{{ $selectedEnrollment->academic_year }}</span></div>
                    </div>
                    <div class="p-6 rounded-[2rem] text-center shadow-inner flex flex-col items-center justify-center {{ $selectedEnrollment->status === 'APPROVED' ? 'bg-emerald-500 text-white shadow-emerald-200/50' : ($selectedEnrollment->status === 'REJECTED' ? 'bg-red-500 text-white shadow-red-200/50' : 'bg-amber-500 text-white shadow-amber-200/50') }}"><span class="text-[10px] font-black uppercase tracking-[0.3em] block mb-1 opacity-80">Status Pendaftaran</span><span class="text-xl font-black uppercase italic tracking-tighter">{{ $selectedEnrollment->status }}</span></div>
                </div>
            </div>
            @endif
            <div class="mt-10 flex gap-3"><button @click="detailMode = false" class="flex-1 py-4 bg-gray-900 text-white rounded-2xl font-bold hover:opacity-90 transition shadow-xl active:scale-95">Tutup Detail</button></div>
        </div>
    </div>
</template>