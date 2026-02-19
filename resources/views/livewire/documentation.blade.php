<div class="h-screen w-full bg-[#f8fafc] flex flex-col overflow-hidden font-sans">

    {{-- Header --}}
    <header class="h-[10%] px-7 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
        <div>
            <h1 class="text-2xl font-black text-slate-900 leading-none uppercase tracking-tighter">Dokumentasi Teknis</h1>
            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Implementasi Dataset 5 Juta Baris</p>
        </div>
        <div class="flex gap-2">
            @foreach($tasks as $key => $task)
            <button wire:click="setTab('{{ $key }}')"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase transition-all {{ $activeTab === $key ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}">
                <i class="fas {{ $task['icon'] }} mr-2"></i>{{ $task['title'] }}
            </button>
            @endforeach
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-grow p-6 overflow-hidden">
        <div class="h-full w-full bg-white rounded-3xl border border-slate-200 shadow-sm overflow-y-auto p-8 scrollbar-hide">

            {{-- Tab: Setup & Seeding --}}
            @if($activeTab === 'setup')
            <div x-transition>
                <h2 class="text-2xl font-black text-slate-800 mb-6 uppercase tracking-tighter">TS-01: Setup & Seed 5 Juta Data</h2>
                <div class="grid grid-cols-12 gap-8 italic">
                    <div class="col-span-12 lg:col-span-7 space-y-6">
                        <p class="text-slate-600 leading-relaxed">Untuk menangani 5 juta data, saya menggunakan strategi <strong>PostgreSQL COPY</strong>. Data digenerate menjadi CSV di storage, kemudian diimport langsung ke database untuk performa maksimal.</p>
                        <div class="bg-slate-900 rounded-2xl p-6 font-mono text-sm text-indigo-300 shadow-xl">
                            <p class="text-slate-500 mb-2"># Terminal Command</p>
                            <p>psql -U postgres -d db_krs</p>
                            <p class="text-emerald-400">COPY enrollments FROM '/path/to/enrollments_unique.csv' DELIMITER ',' CSV; </p>
                        </div>
                    </div>
                    <div class="col-span-12 lg:col-span-5">
                        <div class="rounded-2xl border-4 border-slate-100 shadow-inner overflow-hidden">
                            {{-- Ganti dengan path gambar screenshot count data kamu --}}
                            <img src="{{ asset('img/screenshots/ts-01-count.png') }}" class="w-full hover:scale-105 transition-transform duration-500">
                        </div>
                        <p class="text-[10px] font-black text-slate-400 mt-3 text-center uppercase tracking-widest italic">Screenshot: Verifikasi 5.000.000 Data</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tab: CRUD & Transaction --}}
            @if($activeTab === 'crud')
            <div class="space-y-10" x-transition>
                <div class="grid grid-cols-2 gap-6 italic">
                    <div class="p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
                        <h4 class="font-black text-indigo-900 uppercase text-sm mb-2">TS-02: Atomic Transaction</h4>
                        <p class="text-xs text-indigo-700">Implementasi `DB::transaction()` untuk memastikan insert ke tabel <em>students, courses,</em> dan <em>enrollments</em> berjalan utuh atau gagal sama sekali (rollback).</p>
                    </div>
                    <div class="p-6 bg-rose-50 rounded-2xl border border-rose-100 italic">
                        <h4 class="font-black text-rose-900 uppercase text-sm mb-2">TS-04: Validasi Backend</h4>
                        <p class="text-xs text-rose-700">Validasi ketat pada API menggunakan status code 422 untuk menolak data invalid atau duplikat.</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50">
                    <img src="{{ asset('img/screenshots/ts-04-api.png') }}" class="w-full rounded-xl shadow-sm">
                </div>
            </div>
            @endif

            {{-- Tab: Server-Side Performance --}}
            @if($activeTab === 'server-side')
            <div class="space-y-8 italic" x-transition>
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight italic border-b pb-4">Optimasi Query 5 Juta Data</h2>
                <div class="grid grid-cols-3 gap-4 italic">
                    @foreach(['Pagination (TS-05)', 'Sorting (TS-06)', 'Live Search (TS-08)'] as $perf)
                    <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-sm italic">
                        <span class="text-[10px] font-black text-indigo-600 uppercase">{{ $perf }}</span>
                        <p class="text-[11px] text-slate-500 mt-1 italic">Diproses di sisi server untuk menjaga responsivitas UI.</p>
                    </div>
                    @endforeach
                </div>
                <div class="p-6 bg-slate-900 rounded-2xl italic">
                    <p class="text-emerald-400 font-mono text-xs italic">// Backend menggunakan ILIKE (Postgres) untuk pencarian case-insensitive</p>
                    <p class="text-slate-300 font-mono text-xs mt-2 italic">$q->where('name', 'ILIKE', '%' . $this->search . '%');</p>
                </div>
            </div>
            @endif

            {{-- Tab: PDF View --}}
            @if($activeTab === 'pdf')
            <div class="h-full flex flex-col" x-transition>
                <div class="flex items-center justify-between mb-4 italic">
                    <h2 class="text-lg font-black text-slate-800 uppercase italic underline">Instruksi Tes Teknis</h2>
                    <a href="{{ asset('docs/Instruksi_Tes.pdf') }}" target="_blank" class="text-xs font-black text-indigo-600 uppercase hover:underline">
                        Buka di Tab Baru <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
                <div class="flex-grow rounded-2xl border border-slate-200 overflow-hidden shadow-inner italic">
                    {{-- Pastikan file PDF ada di folder public/docs/ --}}
                    <iframe src="{{ asset('docs/Instruksi_Tes.pdf') }}" class="w-full h-full border-none"></iframe>
                </div>
            </div>
            @endif

            {{-- Tab: Export --}}
            @if($activeTab === 'export')
            <div class="space-y-6 italic" x-transition>
                <div class="bg-slate-800 text-white p-8 rounded-3xl shadow-xl italic relative overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="text-2xl font-black uppercase mb-4 tracking-tighter italic">TS-13: Streaming Export 5jt Data</h2>
                        <p class="text-slate-300 text-sm max-w-2xl leading-relaxed italic">Strategi ekspor menggunakan <strong>StreamedResponse</strong> untuk menghindari limit memori server. Format CSV digunakan untuk dataset penuh, dan XLSX dengan strategi chunking/zip</p>
                    </div>
                    <i class="fas fa-file-csv absolute -right-10 -bottom-10 text-[200px] text-white/5 italic"></i>
                </div>
                <div class="grid grid-cols-2 gap-6 italic">
                    <div class="border border-slate-200 rounded-2xl p-6 italic">
                        <h4 class="font-black text-slate-800 uppercase text-xs mb-3 italic">TS-12: Soft Deletes</h4>
                        <p class="text-[11px] text-slate-500 italic">Menerapkan sistem sampah (trash) sehingga data dapat dipulihkan sebelum dihapus permanen.</p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </main>
</div>