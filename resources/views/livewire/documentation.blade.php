<div class="h-screen w-full bg-[#f8fafc] flex flex-col overflow-hidden font-sans">

    {{-- TOPBAR --}}
    <header class="h-14 px-6 flex items-center justify-between border-b border-slate-200 bg-white shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-600 rounded-lg text-white shadow-md shadow-indigo-200">
                <i class="fas fa-microchip text-sm"></i>
            </div>
            <div>
                <a href="{{ route('enrollments.index') }}"><span class="text-sm font-black text-slate-900 uppercase tracking-tighter">eKRS</span></a>
                <span class="text-sm font-light text-slate-400 ml-1">/ Dokumentasi</span>
            </div>
            {{-- Tombol Kembali ke Enrollment --}}
            <a href="{{ route('enrollments.index') }}"
                class="group flex items-center gap-2 ml-2 text-[11px] font-black text-indigo-600 uppercase tracking-widest
                      border border-indigo-200 bg-indigo-50 hover:bg-indigo-600 hover:text-white hover:border-indigo-600
                      px-4 py-1.5 rounded-xl shadow-sm hover:shadow-md hover:shadow-indigo-200
                      transition-all duration-200">
                <i class="fas fa-arrow-left text-[10px] group-hover:-translate-x-0.5 transition-transform duration-200"></i>
                Kembali ke Enrollment
            </a>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden md:block">
                Developer: <span class="text-indigo-600">Tasya Nurul Fadila</span>
            </span>
            <div class="flex gap-2">
                <span class="text-[10px] font-black text-emerald-500 uppercase bg-emerald-50 px-2 py-1 rounded-md">Laravel 12</span>
                <span class="text-[10px] font-black text-indigo-500 uppercase bg-indigo-50 px-2 py-1 rounded-md">PostgreSQL 14</span>
                <span class="text-[10px] font-black text-rose-500 uppercase bg-rose-50 px-2 py-1 rounded-md">Livewire 4</span>
            </div>
        </div>
    </header>

    {{-- BODY: SIDEBAR + CONTENT --}}
    <div class="flex flex-grow overflow-hidden">

        {{-- SIDEBAR --}}
        <aside class="w-64 bg-white border-r border-slate-100 flex flex-col shrink-0 overflow-y-auto scrollbar-hide py-4">
            @foreach($nav as $group)
            <div class="mb-1">
                {{-- Group Label --}}
                <div class="flex items-center gap-2 px-5 py-2 mb-1">
                    <i class="fas {{ $group['icon'] }} text-slate-300 text-xs"></i>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        {{ $group['group'] }}
                    </span>
                </div>

                {{-- Nav Items --}}
                @foreach($group['items'] as $item)
                <button
                    wire:click="setSection('{{ $item['key'] }}')"
                    class="w-full text-left px-4 py-2.5 text-xs transition-all duration-200 flex items-center gap-2.5 relative
                           {{ $activeSection === $item['key']
                               ? 'text-white font-black'
                               : 'text-slate-500 font-medium hover:bg-indigo-50 hover:text-indigo-700' }}"
                    style="{{ $activeSection === $item['key']
                        ? 'background: linear-gradient(90deg, #4f46e5, #6366f1); box-shadow: inset -3px 0 0 #312e81, 2px 0 12px rgba(99,102,241,0.25); border-right: 3px solid #312e81;'
                        : '' }}">
                    @if($activeSection === $item['key'])
                    {{-- Glow dot --}}
                    <span style="width:6px;height:6px;border-radius:50%;background:#a5b4fc;box-shadow:0 0 6px #a5b4fc;flex-shrink:0"></span>
                    @else
                    <i class="fas fa-circle text-slate-200" style="font-size:5px; flex-shrink:0; margin-top:1px"></i>
                    @endif
                    {{ $item['label'] }}
                </button>
                @endforeach
            </div>

            @if(!$loop->last)
            <div class="mx-5 my-2 border-t border-slate-100"></div>
            @endif
            @endforeach
        </aside>

        {{-- KONTEN UTAMA --}}
        <main class="flex-grow overflow-y-auto bg-[#f8fafc] scrollbar-hide">
            <div class="max-w-4xl mx-auto px-10 py-10">

                {{-- ══════════ OVERVIEW ══════════ --}}
                @if($activeSection === 'overview')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Pengantar</span>
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter leading-tight mb-2">
                        Laporan Teknis: Pengembangan Sistem Akademik (eKRS)
                    </h1>
                    <p class="text-sm text-slate-400 mb-8">
                        <i class="fas fa-user-circle mr-1"></i>
                        Tasya Nurul Fadila &nbsp;·&nbsp; Web Developer (Full Stack) &nbsp;·&nbsp;
                        <a href="mailto:tasyanufa02@gmail.com" class="text-indigo-500 hover:underline">tasyanufa02@gmail.com</a>
                    </p>
                    <div class="prose-sm text-slate-600 leading-relaxed space-y-4 mb-8">
                        <p>Sistem ini dibangun untuk menangani proses manajemen <strong class="text-slate-800">Kartu Rencana Studi (KRS)</strong> dengan performa tinggi, mampu mengelola dataset hingga <strong class="text-indigo-700">5.000.000 baris</strong> secara stabil dan efisien.</p>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-8">
                        <h3 class="text-xs font-black text-indigo-800 uppercase tracking-widest mb-4">Fitur Unggulan</h3>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach([
                            ['icon'=>'fa-atom', 'text'=>'Atomic Transactions (All-or-Nothing)'],
                            ['icon'=>'fa-table-list', 'text'=>'Server-Side Pagination (LIMIT/OFFSET)'],
                            ['icon'=>'fa-trash-undo', 'text'=>'Soft Deletes & Data Recovery'],
                            ['icon'=>'fa-stream', 'text'=>'Streaming Export 5 Juta Baris CSV'],
                            ['icon'=>'fa-magnifying-glass','text'=>'Live Search + Debounce 300ms'],
                            ['icon'=>'fa-filter', 'text'=>'Advanced Multi-Filter AND/OR'],
                            ] as $f)
                            <div class="flex items-start gap-2">
                                <i class="fas {{ $f['icon'] }} text-indigo-500 mt-0.5 text-xs w-4 shrink-0"></i>
                                <span class="text-xs text-indigo-800">{{ $f['text'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Cakupan Skenario Pengujian</h3>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach([
                        ['ts'=>'TS-01', 'desc'=>'Setup & Seed 5 Juta Data menggunakan PostgreSQL COPY Command'],
                        ['ts'=>'TS-02', 'desc'=>'Atomic Transaction (Create 3 tabel: Students, Courses, Enrollments)'],
                        ['ts'=>'TS-03', 'desc'=>'Frontend Validation — real-time feedback via Livewire'],
                        ['ts'=>'TS-04', 'desc'=>'Backend Validation — Regex, unique check, business logic rules'],
                        ['ts'=>'TS-05', 'desc'=>'Server-Side Pagination dengan LIMIT & OFFSET PostgreSQL'],
                        ['ts'=>'TS-06', 'desc'=>'Sorting dinamis ASC/DESC di sisi database'],
                        ['ts'=>'TS-07', 'desc'=>'Quick Filter berdasarkan Status & Semester'],
                        ['ts'=>'TS-08', 'desc'=>'Live Search dengan Debounce 300ms'],
                        ['ts'=>'TS-09', 'desc'=>'Advanced Filter dengan logika AND'],
                        ['ts'=>'TS-10', 'desc'=>'Advanced Filter dengan logika OR'],
                        ['ts'=>'TS-11', 'desc'=>'Update Data dengan validasi & atomic transaction'],
                        ['ts'=>'TS-12', 'desc'=>'Soft Deletes — Trash, Restore, Force Delete'],
                        ['ts'=>'TS-13', 'desc'=>'Streaming Export 5 juta baris ke format CSV'],
                        ] as $ts)
                        <div class="flex items-start gap-3 py-2 border-b border-slate-100">
                            <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded shrink-0 w-14 text-center">{{ $ts['ts'] }}</span>
                            <span class="text-xs text-slate-600">{{ $ts['desc'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ══════════ TECH STACK ══════════ --}}
                @if($activeSection === 'stack')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mb-2">Tech Stack</h1>
                    <p class="text-sm text-slate-400 mb-8">Teknologi yang digunakan dalam pembangunan sistem eKRS skala besar.</p>
                    <div class="grid grid-cols-1 gap-5">
                        @foreach([
                        ['name'=>'Laravel 12.x', 'color'=>'bg-rose-500', 'icon'=>'fa-layer-group', 'role'=>'Backend Framework',
                        'desc'=>'Framework PHP untuk routing, Eloquent ORM, Livewire, Queues, dan manajemen transaksi database.'],
                        ['name'=>'PostgreSQL 14', 'color'=>'bg-indigo-600', 'icon'=>'fa-database', 'role'=>'Database',
                        'desc'=>'Database relasional handal dengan dukungan COPY Command, composite index, dan query optimizer untuk 5 juta baris.'],
                        ['name'=>'Livewire 4.x', 'color'=>'bg-pink-500', 'icon'=>'fa-bolt', 'role'=>'Reactive Frontend',
                        'desc'=>'Komponen reaktif Laravel untuk real-time validation, live search, debounce, dan update tabel tanpa page reload.'],
                        ['name'=>'Tailwind CSS', 'color'=>'bg-sky-500', 'icon'=>'fa-palette', 'role'=>'Styling',
                        'desc'=>'Utility-first CSS framework untuk membangun antarmuka yang responsif dan konsisten.'],
                        ['name'=>'Alpine.js', 'color'=>'bg-emerald-600', 'icon'=>'fa-mountain', 'role'=>'UI Interactivity',
                        'desc'=>'Library JavaScript ringan untuk animasi transisi, toggle UI, dan interaksi kecil tanpa Vue/React.'],
                        ] as $tech)
                        <div class="flex gap-5 p-5 bg-white border border-slate-200 rounded-2xl shadow-sm">
                            <div class="w-10 h-10 {{ $tech['color'] }} rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $tech['icon'] }} text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-black text-slate-800 text-sm">{{ $tech['name'] }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $tech['role'] }}</span>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">{{ $tech['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ══════════ CRUD & FORM ══════════ --}}
                @if($activeSection === 'crud')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Panduan Fitur</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">CRUD & Form</h1>
                    <p class="text-sm text-slate-400 mb-8">Panduan penggunaan fitur Create, Read, Update, dan Delete pada sistem eKRS.</p>
                    @foreach([
                    ['icon'=>'fa-plus-circle','color'=>'text-emerald-600','title'=>'Tambah Data (Create)',
                    'steps'=>['Klik tombol Tambah / + di pojok kanan atas tabel.','Isi form: NIM, Nama, Email Mahasiswa, Kode Mata Kuliah, Tahun Ajaran, Semester, dan Status.','Sistem melakukan validasi real-time saat Anda mengetik (frontend).','Klik Simpan — sistem memvalidasi ulang di backend dan menyimpan melalui Atomic Transaction.','Notifikasi sukses/gagal muncul otomatis.']],
                    ['icon'=>'fa-eye','color'=>'text-indigo-600','title'=>'Lihat Data (Read)',
                    'steps'=>['Data ditampilkan dalam tabel dengan server-side pagination.','Gunakan kontrol halaman di bawah tabel untuk navigasi antar halaman.','Setiap halaman hanya memuat 10–25 baris — tidak membebani browser meski data 5 juta baris.']],
                    ['icon'=>'fa-pen-to-square','color'=>'text-amber-600','title'=>'Edit Data (Update)',
                    'steps'=>['Klik ikon Edit (✏️) pada baris data yang ingin diubah.','Form akan terbuka dengan data yang sudah terisi.','Lakukan perubahan, lalu klik Simpan.','Sistem memvalidasi ulang untuk mencegah duplikasi KRS atau data tidak valid.','Perubahan disimpan melalui Atomic Transaction — aman dari data "setengah jadi".']],
                    ['icon'=>'fa-trash','color'=>'text-rose-600','title'=>'Hapus Data (Soft Delete)',
                    'steps'=>['Klik ikon Hapus (🗑) pada baris data.','Data tidak langsung dihapus — dipindahkan ke menu Trash.','Untuk melihat data terhapus, buka menu Trash.','Restore: klik tombol Pulihkan untuk mengembalikan data.','Force Delete: klik Hapus Permanen untuk benar-benar menghapus dari database.']],
                    ] as $section)
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fas {{ $section['icon'] }} {{ $section['color'] }}"></i>
                            <h3 class="font-black text-slate-800 text-sm uppercase tracking-wide">{{ $section['title'] }}</h3>
                        </div>
                        <ol class="space-y-2">
                            @foreach($section['steps'] as $i => $step)
                            <li class="flex items-start gap-3">
                                <span class="text-[10px] font-black text-white bg-indigo-600 rounded-full w-5 h-5 flex items-center justify-center shrink-0 mt-0.5">{{ $i+1 }}</span>
                                <span class="text-sm text-slate-600 leading-relaxed">{{ $step }}</span>
                            </li>
                            @endforeach
                        </ol>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- ══════════ FILTER & PENCARIAN ══════════ --}}
                @if($activeSection === 'filter')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Panduan Fitur</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Filter & Pencarian</h1>
                    <p class="text-sm text-slate-400 mb-8">Cara efisien menemukan data di antara 5 juta baris menggunakan fitur pencarian dan filter.</p>
                    <div class="space-y-6">
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fas fa-magnifying-glass text-indigo-500"></i> Live Search
                            </h3>
                            <p class="text-sm text-slate-600 leading-relaxed mb-3">Ketik kata kunci di kolom pencarian — hasil tabel akan diperbarui otomatis <strong>tanpa reload halaman</strong>. Dapat mencari berdasarkan:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['NIM Mahasiswa','Nama Mahasiswa','Kode Mata Kuliah'] as $p)
                                <span class="text-xs font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full">{{ $p }}</span>
                                @endforeach
                            </div>
                            <div class="mt-3 bg-amber-50 border border-amber-100 rounded-xl p-3">
                                <p class="text-xs text-amber-800"><i class="fas fa-stopwatch mr-1"></i> <strong>Debounce 300ms:</strong> Sistem menunggu 300ms setelah Anda berhenti mengetik sebelum mengirim query — mencegah overload database.</p>
                            </div>
                        </div>
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fas fa-sliders text-indigo-500"></i> Quick Filter
                            </h3>
                            <p class="text-sm text-slate-600 leading-relaxed mb-3">Filter cepat satu klik berdasarkan parameter yang paling sering dibutuhkan:</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-50 rounded-xl p-3">
                                    <span class="text-[10px] font-black text-slate-500 uppercase">Status</span>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach(['Draft','Submitted','Approved','Rejected'] as $s)
                                        <span class="text-[10px] font-bold bg-white border border-slate-200 text-slate-600 px-2 py-0.5 rounded">{{ $s }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-3">
                                    <span class="text-[10px] font-black text-slate-500 uppercase">Semester</span>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach(['Ganjil','Genap'] as $s)
                                        <span class="text-[10px] font-bold bg-white border border-slate-200 text-slate-600 px-2 py-0.5 rounded">{{ $s }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fas fa-filter text-indigo-500"></i> Advanced Filter (AND / OR)
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-indigo-50 rounded-xl p-4">
                                    <span class="text-[10px] font-black text-indigo-800 uppercase block mb-1">Logika AND</span>
                                    <p class="text-xs text-indigo-700">Semua kondisi harus terpenuhi. Contoh: Status=Approved <strong>DAN</strong> Semester=Ganjil <strong>DAN</strong> Tahun=2025.</p>
                                </div>
                                <div class="bg-rose-50 rounded-xl p-4">
                                    <span class="text-[10px] font-black text-rose-800 uppercase block mb-1">Logika OR</span>
                                    <p class="text-xs text-rose-700">Salah satu kondisi cukup terpenuhi. Contoh: Status=Draft <strong>ATAU</strong> Status=Rejected.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ══════════ EXPORT CSV ══════════ --}}
                @if($activeSection === 'export')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Panduan Fitur</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Export CSV</h1>
                    <p class="text-sm text-slate-400 mb-8">Cara mengunduh data dalam jumlah masif tanpa membebani server.</p>
                    <div class="bg-indigo-600 text-white p-7 rounded-2xl mb-6 relative overflow-hidden">
                        <i class="fas fa-file-csv absolute -right-6 -bottom-6 text-[100px] text-white/10 rotate-12"></i>
                        <h3 class="font-black uppercase tracking-tight text-lg mb-2">Streaming Export</h3>
                        <p class="text-indigo-100 text-sm leading-relaxed">Data dikirim <strong class="text-white">bit demi bit</strong> ke browser menggunakan <code class="bg-indigo-700 px-1 rounded">StreamedResponse</code> — tidak perlu memuat 5 juta baris ke RAM server sekaligus.</p>
                    </div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Cara Export</h3>
                    <ol class="space-y-4 mb-8">
                        @foreach([
                        ['title'=>'Pilih Mode Export','desc'=>'Export Keseluruhan: mengunduh seluruh 5 juta baris mentah dari database.'],
                        ['title'=>'(Opsional) Terapkan Filter','desc'=>'Export Hasil Filter: terapkan filter terlebih dahulu (status, semester, tahun ajaran), lalu klik Export — hanya data yang tersaring yang diunduh.'],
                        ['title'=>'Klik Tombol Export','desc'=>'Unduhan dimulai secara instan. File CSV akan tersimpan di folder Downloads browser Anda.'],
                        ] as $i => $step)
                        <li class="flex gap-4 items-start">
                            <span class="text-[10px] font-black text-white bg-indigo-600 rounded-full w-6 h-6 flex items-center justify-center shrink-0 mt-0.5">{{ $i+1 }}</span>
                            <div>
                                <span class="text-sm font-black text-slate-800 block mb-0.5">{{ $step['title'] }}</span>
                                <span class="text-sm text-slate-500">{{ $step['desc'] }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-500 mb-2">// Controller — StreamedResponse</p>
                        <p class="text-indigo-300">public function <span class="text-emerald-400">exportCsv</span>(): StreamedResponse</p>
                        <p class="text-indigo-300">{</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;return response()-><span class="text-emerald-400">streamDownload</span>(function () {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;$handle = fopen(<span class="text-amber-300">'php://output'</span>, <span class="text-amber-300">'w'</span>);</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;Enrollment::<span class="text-emerald-400">cursor</span>()-><span class="text-emerald-400">each</span>(function ($row) use ($handle) {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">fputcsv</span>($handle, $row-><span class="text-emerald-400">toArray</span>());</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;});</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;fclose($handle);</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;}, <span class="text-amber-300">'enrollments_export.csv'</span>);</p>
                        <p class="text-indigo-300">}</p>
                    </div>
                </div>
                @endif

                {{-- ══════════ SOFT DELETE ══════════ --}}
                @if($activeSection === 'softdelete')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Panduan Fitur</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Soft Deletes & Trash</h1>
                    <p class="text-sm text-slate-400 mb-8">Sistem pengamanan data agar tidak hilang secara permanen akibat penghapusan tidak disengaja.</p>
                    <div class="space-y-5">
                        @foreach([
                        ['icon'=>'fa-trash', 'color'=>'bg-rose-100 text-rose-600', 'title'=>'Hapus (Soft Delete)', 'desc'=>'Saat tombol Hapus ditekan, data tidak langsung hilang dari database. Kolom deleted_at diisi dengan timestamp penghapusan. Data tersebut tersembunyi dari tabel utama namun masih aman tersimpan.'],
                        ['icon'=>'fa-box-archive', 'color'=>'bg-amber-100 text-amber-600', 'title'=>'Trash (Keranjang Sampah)', 'desc'=>'Menu Trash menampilkan semua data yang sudah dihapus (soft delete). Administrator dapat melihat riwayat penghapusan beserta waktu penghapusannya.'],
                        ['icon'=>'fa-rotate-left', 'color'=>'bg-emerald-100 text-emerald-600','title'=>'Restore (Pulihkan)', 'desc'=>'Klik tombol Pulihkan pada data di menu Trash. Data akan kembali muncul di tabel utama dengan seluruh relasi antar tabel tetap utuh.'],
                        ['icon'=>'fa-circle-xmark', 'color'=>'bg-slate-100 text-slate-600', 'title'=>'Force Delete (Hapus Permanen)','desc'=>'Hanya tersedia di menu Trash. Data benar-benar dihapus dari database dan tidak dapat dipulihkan. Gunakan hanya jika Anda yakin 100%.'],
                        ] as $item)
                        <div class="flex gap-4 p-5 bg-white border border-slate-200 rounded-2xl">
                            <div class="w-9 h-9 {{ $item['color'] }} rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $item['icon'] }} text-sm"></i>
                            </div>
                            <div>
                                <span class="font-black text-slate-800 text-sm block mb-1">{{ $item['title'] }}</span>
                                <p class="text-sm text-slate-500 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ══════════ TS-01 ══════════ --}}
                @if($activeSection === 'ts01')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-01</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Setup & Seed 5 Juta Data</h1>
                    <p class="text-sm text-slate-400 mb-8">Strategi seeding data masif menggunakan PostgreSQL COPY Command.</p>
                    <div class="p-5 bg-indigo-50 border-l-4 border-indigo-600 rounded-r-2xl mb-6">
                        <p class="text-sm text-indigo-800 leading-relaxed">Untuk menangani <em>seeding</em> data dalam jumlah masif (5 juta baris), sistem tidak menggunakan <em>looping Eloquent</em> standar yang lambat. Pendekatan <strong>Generate CSV + PostgreSQL COPY Command</strong> dipilih karena jauh lebih cepat dan hemat memori.</p>
                    </div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Langkah Eksekusi</h3>
                    <ol class="space-y-3 mb-8">
                        @foreach(['git clone https://github.com/syanufa11/eKRS','composer update','php artisan key:generate','php artisan migrate:fresh --seed','Generate file CSV enrollments_unique.csv','Jalankan SQL COPY Command (lihat di bawah)','Verifikasi: SELECT COUNT(*) FROM enrollments; → 5.000.000'] as $i => $step)
                        <li class="flex items-start gap-3">
                            <span class="text-[10px] font-black text-white bg-slate-700 rounded-full w-5 h-5 flex items-center justify-center shrink-0 mt-0.5">{{ $i+1 }}</span>
                            <code class="text-xs text-slate-700 font-mono bg-slate-50 px-2 py-1 rounded border border-slate-200 leading-relaxed">{{ $step }}</code>
                        </li>
                        @endforeach
                    </ol>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">SQL COPY Command</h3>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs mb-6 overflow-x-auto">
                        <p class="text-slate-500 mb-2">-- Import data CSV langsung ke PostgreSQL</p>
                        <p class="text-emerald-400">COPY enrollments(student_id, course_id, academic_year, semester, status, created_at, updated_at)</p>
                        <p class="text-emerald-400">FROM <span class="text-amber-300">'storage/app/enrollments_unique.csv'</span></p>
                        <p class="text-emerald-400">DELIMITER <span class="text-amber-300">','</span> CSV HEADER;</p>
                        <br>
                        <p class="text-slate-500">-- Verifikasi</p>
                        <p class="text-emerald-400">SELECT COUNT(*) FROM enrollments;</p>
                        <p class="text-slate-400">-- Result: <span class="text-emerald-400 font-bold">5000000</span></p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-circle-check text-emerald-600"></i>
                            <span class="font-black text-emerald-800 text-xs uppercase tracking-wide">Verification Result</span>
                        </div>
                        <p class="text-sm text-emerald-700">Query <code>SELECT COUNT(*) FROM enrollments;</code> menunjukkan total data mencapai <strong>5.000.000 baris</strong>.</p>
                    </div>
                </div>
                @endif

                {{-- ══════════ TS-02 ══════════ --}}
                @if($activeSection === 'ts02')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-02</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Atomic Transaction (Create)</h1>
                    <p class="text-sm text-slate-400 mb-8">Menjamin konsistensi data 3 tabel dengan prinsip All-or-Nothing.</p>
                    <div class="p-5 bg-indigo-50 border-l-4 border-indigo-600 rounded-r-2xl mb-8">
                        <p class="text-sm text-indigo-800 leading-relaxed">Fitur ini menggunakan mekanisme <strong>Database Transaction</strong> untuk menjamin konsistensi data pada tabel <em>Students</em>, <em>Courses</em>, dan <em>Enrollments</em>. Jika salah satu operasi gagal, <strong>semua perubahan dibatalkan (Rollback)</strong>.</p>
                    </div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">1. Transaksi pada Modul Mata Kuliah (Course)</h3>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs mb-6 overflow-x-auto">
                        <p class="text-slate-500 mb-2">// store() — CourseManagement.php</p>
                        <p class="text-indigo-300">DB::<span class="text-emerald-400">beginTransaction</span>();</p>
                        <p class="text-indigo-300">try {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;Course::<span class="text-emerald-400">updateOrCreate</span>([<span class="text-amber-300">'id'</span> => $this->courseId], [...]);</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;DB::<span class="text-emerald-400">commit</span>();</p>
                        <p class="text-indigo-300">} catch (\Throwable $e) {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;DB::<span class="text-rose-400">rollBack</span>();</p>
                        <p class="text-indigo-300">}</p>
                    </div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">2. Transaksi pada Modul KRS (Enrollment)</h3>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs mb-6 overflow-x-auto">
                        <p class="text-slate-500 mb-2">// store() — EnrollmentManagement.php</p>
                        <p class="text-indigo-300">DB::<span class="text-emerald-400">transaction</span>(function () {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;$student = Student::<span class="text-emerald-400">updateOrCreate</span>([<span class="text-amber-300">'nim'</span> => $this->student_nim], [...]);</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;Enrollment::<span class="text-emerald-400">create</span>([<span class="text-amber-300">'student_id'</span> => $student->id, ...]);</p>
                        <p class="text-indigo-300">}); <span class="text-slate-500">// Auto rollback jika exception</span></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                            <span class="font-black text-emerald-800 text-xs block mb-1"><i class="fas fa-check-circle mr-1"></i>Commit</span>
                            <p class="text-xs text-emerald-700">Semua operasi berhasil → data tersimpan secara permanen.</p>
                        </div>
                        <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                            <span class="font-black text-rose-800 text-xs block mb-1"><i class="fas fa-xmark-circle mr-1"></i>Rollback</span>
                            <p class="text-xs text-rose-700">Salah satu gagal → semua perubahan dibatalkan otomatis.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TS-03 & 04 --}}
                @if($activeSection === 'ts0304')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-03 & TS-04</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Validasi Ketat (Frontend & Backend)</h1>
                    <p class="text-sm text-slate-400 mb-8">Validasi berlapis untuk menjamin kualitas data sebelum masuk ke database.</p>
                    <div class="space-y-6">
                        <div>
                            <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-3 flex items-center gap-2"><i class="fas fa-display text-indigo-500"></i> TS-03: Frontend Validation</h3>
                            <div class="space-y-3">
                                <div class="p-4 bg-white border border-slate-200 rounded-xl">
                                    <p class="text-xs text-slate-500"><strong class="text-slate-700">Real-time Feedback:</strong> Livewire menampilkan peringatan instan saat pengguna mengetik — meminimalisir kesalahan sebelum tombol Simpan ditekan.</p>
                                </div>
                                <div class="p-4 bg-white border border-slate-200 rounded-xl">
                                    <p class="text-xs text-slate-500"><strong class="text-slate-700">Format Checking:</strong> Format angka pada NIM dan panjang karakter minimal sudah divalidasi di sisi klien.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-3 flex items-center gap-2"><i class="fas fa-server text-rose-500"></i> TS-04: Backend Validation</h3>
                            <div class="space-y-3">
                                @foreach([
                                ['title'=>'Data Uniqueness', 'desc'=>'NIM dan Email mahasiswa tidak boleh duplikat. Sistem mengembalikan error jika data sudah terdaftar.'],
                                ['title'=>'Business Logic', 'desc'=>'Mencegah duplikasi enrollment — mahasiswa tidak bisa mengambil MK yang sama di tahun ajaran dan semester yang sama.'],
                                ['title'=>'Pattern Matching', 'desc'=>'Kode Mata Kuliah divalidasi menggunakan Regex: harus diawali 2–3 huruf kapital diikuti 1–4 angka (contoh: CS204, IF301).'],
                                ] as $v)
                                <div class="p-4 bg-white border border-slate-200 rounded-xl">
                                    <span class="font-black text-slate-800 text-xs block mb-1">{{ $v['title'] }}</span>
                                    <p class="text-xs text-slate-500">{{ $v['desc'] }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                            <p class="text-slate-500 mb-2">// Validation Rules</p>
                            <p class="text-indigo-300">$rules = [</p>
                            <p class="text-emerald-400">&nbsp;&nbsp;<span class="text-amber-300">'nim'</span> => <span class="text-amber-300">'required|unique:students,nim|regex:/^[0-9]{10}$/'</span>,</p>
                            <p class="text-emerald-400">&nbsp;&nbsp;<span class="text-amber-300">'email'</span> => <span class="text-amber-300">'required|unique:students,email|email:rfc,dns'</span>,</p>
                            <p class="text-emerald-400">&nbsp;&nbsp;<span class="text-amber-300">'kode_mk'</span> => <span class="text-amber-300">'required|regex:/^[a-zA-Z]{2,3}[0-9]{1,4}$/'</span>,</p>
                            <p class="text-indigo-300">];</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TS-05 --}}
                @if($activeSection === 'ts05')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-05</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Server-Side Pagination</h1>
                    <p class="text-sm text-slate-400 mb-8">Menampilkan 5 juta data secara efisien dengan pengambilan data parsial.</p>
                    <div class="p-5 bg-indigo-50 border-l-4 border-indigo-600 rounded-r-2xl mb-6">
                        <p class="text-sm text-indigo-800 leading-relaxed">Sistem tidak memuat semua data sekaligus ke browser. Database hanya mengirimkan data dalam jumlah kecil (10–25 baris) sesuai halaman yang sedang dibuka, menjaga waktu <em>page load</em> di bawah <strong>1 detik</strong>.</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        @foreach([['val'=>'< 1s','label'=>'Page Load Time'],['val'=>'25','label'=>'Baris per Halaman'],['val'=>'OFFSET','label'=>'PostgreSQL Method']] as $m)
                            <div class="bg-white border border-slate-200 rounded-2xl p-5 text-center">
                                <div class="text-2xl font-black text-indigo-600 mb-1">{{ $m['val'] }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $m['label'] }}</div>
                            </div>
                            @endforeach
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-500 mb-2">// Eloquent Pagination</p>
                        <p class="text-indigo-300">$data = Enrollment::<span class="text-emerald-400">with</span>(<span class="text-amber-300">'student'</span>, <span class="text-amber-300">'course'</span>)-><span class="text-emerald-400">paginate</span>(<span class="text-amber-300">25</span>);</p>
                        <br>
                        <p class="text-slate-500">-- SQL PostgreSQL</p>
                        <p class="text-emerald-400">SELECT * FROM enrollments LIMIT <span class="text-amber-300">25</span> OFFSET <span class="text-amber-300">0</span>;</p>
                    </div>
                </div>
                @endif

                {{-- TS-06 --}}
                @if($activeSection === 'ts06')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-06</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Sorting (Server-Side)</h1>
                    <p class="text-sm text-slate-400 mb-8">Pengurutan dinamis yang dieksekusi langsung di sisi database PostgreSQL.</p>
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-5 bg-indigo-50 border border-indigo-100 rounded-2xl">
                            <span class="font-black text-indigo-800 text-xs uppercase block mb-2"><i class="fas fa-arrow-up-a-z mr-1"></i>Ascending (ASC)</span>
                            <p class="text-xs text-indigo-700">Urutan terkecil ke terbesar: A→Z, 1→9.</p>
                        </div>
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl">
                            <span class="font-black text-slate-800 text-xs uppercase block mb-2"><i class="fas fa-arrow-down-z-a mr-1"></i>Descending (DESC)</span>
                            <p class="text-xs text-slate-600">Urutan terbesar ke terkecil: Z→A, 9→1.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach(['NIM','Nama Mahasiswa','Kode MK','Semester','Status','Tahun Ajaran'] as $col)
                        <span class="text-xs font-bold bg-white border border-slate-200 text-slate-700 px-3 py-1.5 rounded-lg">{{ $col }}</span>
                        @endforeach
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-emerald-400">SELECT e.*, s.nim, s.name, c.code FROM enrollments e</p>
                        <p class="text-emerald-400">JOIN students s ON e.student_id = s.id</p>
                        <p class="text-emerald-400">ORDER BY <span class="text-amber-300">s.nim</span> <span class="text-rose-400">ASC</span> LIMIT 25 OFFSET 0;</p>
                    </div>
                </div>
                @endif

                {{-- TS-07 --}}
                @if($activeSection === 'ts07')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-07</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Quick Filter</h1>
                    <p class="text-sm text-slate-400 mb-8">Filter satu klik berdasarkan parameter paling sering dibutuhkan.</p>
                    <div class="grid grid-cols-2 gap-5 mb-6">
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <span class="font-black text-slate-800 text-xs uppercase block mb-3"><i class="fas fa-circle-dot text-indigo-500 mr-1"></i>Filter Status</span>
                            <div class="space-y-2">
                                @foreach(['Draft','Submitted','Approved','Rejected'] as $s)
                                <div class="flex items-center gap-2 text-xs text-slate-600 bg-slate-50 rounded-lg px-3 py-2"><i class="fas fa-circle text-slate-300" style="font-size:6px"></i>{{ $s }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <span class="font-black text-slate-800 text-xs uppercase block mb-3"><i class="fas fa-circle-dot text-indigo-500 mr-1"></i>Filter Semester</span>
                            <div class="space-y-2">
                                @foreach(['Ganjil','Genap'] as $s)
                                <div class="flex items-center gap-2 text-xs text-slate-600 bg-slate-50 rounded-lg px-3 py-2"><i class="fas fa-circle text-slate-300" style="font-size:6px"></i>{{ $s }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                        <p class="text-xs text-amber-800"><i class="fas fa-info-circle mr-1"></i> Setiap perubahan filter memicu query baru ke PostgreSQL dengan klausa <code>WHERE</code>. Bekerja <em>real-time</em> via Livewire tanpa page reload.</p>
                    </div>
                </div>
                @endif

                {{-- TS-08 --}}
                @if($activeSection === 'ts08')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-08</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Live Searching</h1>
                    <p class="text-sm text-slate-400 mb-8">Pencarian real-time yang dioptimasi untuk kecepatan di tengah 5 juta data.</p>
                    <div class="space-y-5 mb-6">
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <span class="font-black text-slate-800 text-xs uppercase block mb-3">Parameter Pencarian</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['NIM Mahasiswa','Nama Mahasiswa','Kode Mata Kuliah'] as $p)
                                <span class="text-xs font-bold bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg">{{ $p }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="p-5 bg-amber-50 border border-amber-100 rounded-2xl">
                            <span class="font-black text-amber-800 text-xs uppercase block mb-2"><i class="fas fa-stopwatch mr-1"></i>Debounce 300ms</span>
                            <p class="text-xs text-amber-700">Sistem menunggu 300ms setelah pengguna berhenti mengetik sebelum mengirim query — mengurangi request yang tidak perlu.</p>
                        </div>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-500 mb-2">// Query builder</p>
                        <p class="text-indigo-300">$query-><span class="text-emerald-400">where</span>(function ($q) {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;$q-><span class="text-emerald-400">where</span>(<span class="text-amber-300">'s.nim'</span>, <span class="text-amber-300">'LIKE'</span>, "%{$this->search}%")</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;-><span class="text-emerald-400">orWhere</span>(<span class="text-amber-300">'s.name'</span>, <span class="text-amber-300">'LIKE'</span>, "%{$this->search}%")</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;&nbsp;&nbsp;-><span class="text-emerald-400">orWhere</span>(<span class="text-amber-300">'c.code'</span>, <span class="text-amber-300">'LIKE'</span>, "%{$this->search}%");</p>
                        <p class="text-indigo-300">});</p>
                    </div>
                </div>
                @endif

                {{-- TS-09 & 10 --}}
                @if($activeSection === 'ts0910')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-09 & TS-10</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Advanced Filter (AND / OR)</h1>
                    <p class="text-sm text-slate-400 mb-8">Pencarian multi-kondisi menggunakan logika kueri kompleks.</p>
                    <div class="grid grid-cols-2 gap-5 mb-6">
                        <div class="p-5 bg-indigo-50 border border-indigo-100 rounded-2xl">
                            <span class="font-black text-indigo-800 text-xs uppercase block mb-2">Logika AND (TS-09)</span>
                            <p class="text-xs text-indigo-700 mb-3">Semua kondisi harus terpenuhi secara bersamaan.</p>
                            <div class="bg-white/60 rounded-xl p-3 text-xs text-indigo-800 font-mono">Status=Approved<br><strong>AND</strong> Semester=Ganjil<br><strong>AND</strong> Tahun=2025</div>
                        </div>
                        <div class="p-5 bg-rose-50 border border-rose-100 rounded-2xl">
                            <span class="font-black text-rose-800 text-xs uppercase block mb-2">Logika OR (TS-10)</span>
                            <p class="text-xs text-rose-700 mb-3">Salah satu kondisi sudah cukup memunculkan data.</p>
                            <div class="bg-white/60 rounded-xl p-3 text-xs text-rose-800 font-mono">Status=Draft<br><strong>OR</strong> Status=Rejected</div>
                        </div>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-500 mb-2">// AND</p>
                        <p class="text-indigo-300">if ($this->filterYear) $query-><span class="text-emerald-400">where</span>(<span class="text-amber-300">'academic_year'</span>, $this->filterYear);</p>
                        <p class="text-indigo-300">if ($this->filterStatus) $query-><span class="text-emerald-400">where</span>(<span class="text-amber-300">'status'</span>, $this->filterStatus);</p>
                        <br>
                        <p class="text-slate-500">// OR</p>
                        <p class="text-indigo-300">$query-><span class="text-emerald-400">where</span>(function ($q) {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;foreach ($this->selectedStatuses as $s) $q-><span class="text-emerald-400">orWhere</span>(<span class="text-amber-300">'status'</span>, $s);</p>
                        <p class="text-indigo-300">});</p>
                    </div>
                </div>
                @endif

                {{-- TS-11 --}}
                @if($activeSection === 'ts11')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-11</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Update Data</h1>
                    <p class="text-sm text-slate-400 mb-8">Pembaruan data enrollment dengan validasi ketat dan jaminan keamanan transaksi.</p>
                    <div class="space-y-4">
                        @foreach([
                        ['icon'=>'fa-layer-group', 'color'=>'text-indigo-500', 'title'=>'Validasi Berlapis', 'desc'=>'Setiap perubahan wajib melewati skema validasi ulang untuk mencegah duplikasi KRS atau data tidak valid.'],
                        ['icon'=>'fa-shield-halved', 'color'=>'text-emerald-500','title'=>'Keamanan Transaksi', 'desc'=>'Menggunakan Atomic Transaction — jika pembaruan gagal di tengah jalan, sistem otomatis Rollback.'],
                        ['icon'=>'fa-link', 'color'=>'text-amber-500', 'title'=>'Integritas Relasi', 'desc'=>'Memastikan riwayat data tetap akurat tanpa merusak relasi antar tabel Students, Courses, dan Enrollments.'],
                        ] as $item)
                        <div class="flex gap-4 p-5 bg-white border border-slate-200 rounded-2xl">
                            <i class="fas {{ $item['icon'] }} {{ $item['color'] }} mt-0.5 w-4 shrink-0"></i>
                            <div>
                                <span class="font-black text-slate-800 text-xs block mb-1">{{ $item['title'] }}</span>
                                <p class="text-xs text-slate-500 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- TS-12 --}}
                @if($activeSection === 'ts12')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-12</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Delete (Soft Deletes)</h1>
                    <p class="text-sm text-slate-400 mb-8">Melindungi integritas data dari penghapusan tidak disengaja.</p>
                    <div class="p-5 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl mb-6">
                        <p class="text-sm text-rose-800 leading-relaxed">Saat dihapus, data <strong>tidak hilang permanen</strong> — hanya ditandai dengan kolom <code>deleted_at</code>. Data dipindahkan ke menu <strong>Trash</strong> dan dapat dipulihkan kapan saja.</p>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-500 mb-2">// SoftDeletes trait</p>
                        <p class="text-indigo-300">class Enrollment extends Model { use <span class="text-emerald-400">SoftDeletes</span>; }</p>
                        <br>
                        <p class="text-indigo-300">$enrollment-><span class="text-rose-400">delete</span>(); <span class="text-slate-500">// sets deleted_at</span></p>
                        <p class="text-indigo-300">$enrollment-><span class="text-emerald-400">restore</span>(); <span class="text-slate-500">// clears deleted_at</span></p>
                        <p class="text-indigo-300">$enrollment-><span class="text-rose-400">forceDelete</span>(); <span class="text-slate-500">// permanent</span></p>
                    </div>
                </div>
                @endif

                {{-- TS-13 --}}
                @if($activeSection === 'ts13')
                <div x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">TS-13</span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-3 mb-2">Export Data (5 Juta Baris)</h1>
                    <p class="text-sm text-slate-400 mb-8">Mengunduh data masif ke format CSV tanpa membebani memori server.</p>
                    <div class="p-5 bg-indigo-50 border-l-4 border-indigo-600 rounded-r-2xl mb-6">
                        <p class="text-sm text-indigo-800 leading-relaxed">Data dikirimkan <strong>bit demi bit</strong> menggunakan <code>StreamedResponse</code> — tanpa memuat 5 juta baris ke RAM. Mencegah <em>Memory Exhaustion</em> dan <em>Server Timeout</em>.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <span class="font-black text-slate-800 text-xs uppercase block mb-2"><i class="fas fa-database text-indigo-500 mr-1"></i>Export Keseluruhan</span>
                            <p class="text-xs text-slate-500">Mengunduh seluruh 5 juta baris mentah dari database dalam satu file CSV.</p>
                        </div>
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl">
                            <span class="font-black text-slate-800 text-xs uppercase block mb-2"><i class="fas fa-filter text-emerald-500 mr-1"></i>Export Hasil Filter</span>
                            <p class="text-xs text-slate-500">Terapkan filter terlebih dahulu, lalu export — hanya data tersaring yang diunduh.</p>
                        </div>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-500 mb-2">// StreamedResponse Export</p>
                        <p class="text-indigo-300">return response()-><span class="text-emerald-400">streamDownload</span>(function () {</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;$handle = fopen(<span class="text-amber-300">'php://output'</span>, <span class="text-amber-300">'w'</span>);</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;Enrollment::<span class="text-emerald-400">cursor</span>()-><span class="text-emerald-400">each</span>(fn($row) => <span class="text-emerald-400">fputcsv</span>($handle, $row-><span class="text-emerald-400">toArray</span>()));</p>
                        <p class="text-indigo-300">&nbsp;&nbsp;fclose($handle);</p>
                        <p class="text-indigo-300">}, <span class="text-amber-300">'enrollments_export.csv'</span>);</p>
                    </div>
                </div>
                @endif

                {{-- LAPORAN TEKNIS --}}
                @if($activeSection === 'report')
                <div class="h-[calc(100vh-8rem)] flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Laporan</span>
                            <h1 class="text-2xl font-black text-slate-900 tracking-tighter mt-2">Laporan Teknis</h1>
                        </div>
                        <a href="https://docs.google.com/document/d/1fAwluqN60qymQJml4SoKel0-9pn96gRs9J1T2eg4txw/preview"
                            target="_blank"
                            class="flex items-center gap-2 text-xs font-black text-indigo-600 uppercase bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-all">
                            Buka di Tab Baru <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    <div class="flex-grow rounded-2xl border border-slate-200 overflow-hidden shadow-inner">
                        <iframe src="https://docs.google.com/document/d/1fAwluqN60qymQJml4SoKel0-9pn96gRs9J1T2eg4txw/preview" class="w-full h-full border-none"></iframe>
                    </div>
                </div>
                @endif

            </div>{{-- /max-w-4xl --}}
        </main>
    </div>{{-- /body flex --}}

    {{-- FOOTER --}}
    <footer class="h-10 px-8 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
        <p class="text-[10px] text-slate-400 font-bold uppercase italic">&copy; 2026 eKRS Documentation &mdash; Tasya Nurul Fadila</p>
        <div class="flex gap-4">
            <span class="text-[10px] font-black text-emerald-500 uppercase">Laravel 12.x</span>
            <span class="text-[10px] font-black text-indigo-500 uppercase">PostgreSQL 14</span>
            <span class="text-[10px] font-black text-rose-500 uppercase">Livewire 4.x</span>
        </div>
    </footer>
</div>
