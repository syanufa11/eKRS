<div class="h-screen w-full bg-[#f8fafc] flex flex-col overflow-hidden font-sans relative">

    {{-- Overlay Loading --}}
    <div wire:loading.flex class="absolute inset-0 z-50 bg-white/60 backdrop-blur-sm items-center justify-center">
        <div class="flex items-center gap-3 px-8 py-4 bg-white shadow-2xl rounded-3xl border border-slate-100">
            <div class="w-6 h-6 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm font-black text-slate-800 tracking-widest uppercase">Memuat Data...</span>
        </div>
    </div>

    {{-- Header --}}
    <header class="h-[10%] px-7 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
        <div>
            <h1 class="text-2xl font-black text-slate-900 leading-none">HOME</h1>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex flex-col items-end mr-2">
                <span class="text-[10px] font-black text-slate-400 uppercase">Periode Aktif</span>
                <span class="text-sm font-bold text-indigo-600 uppercase">{{ $academicYear === 'all' ? 'Semua Tahun' : 'TA '.$academicYear }}</span>
            </div>
            <select wire:model.live="academicYear" class="bg-slate-100 border-none rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 py-3 px-6 cursor-pointer">
                <option value="all">Semua Tahun Ajaran</option>
                @foreach ($academicYears as $yr)
                <option value="{{ $yr }}">{{ $yr }}</option>
                @endforeach
            </select>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-grow p-6 grid grid-cols-12 gap-6 h-[88%] overflow-hidden">

        {{-- Kolom Kiri --}}
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6 h-full">

            {{-- Stats Cards (Top) --}}
            <div class="grid grid-cols-2 gap-4 shrink-0">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Total Masuk</p>
                    <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Disetujui</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['approved']) }}</p>
                </div>
            </div>

            {{--
                === CHART SECTION (TEMPLATE TAILADMIN ADAPTED) ===
                Structure: Header -> Border-t -> Body -> Chart
            --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col flex-grow overflow-hidden relative">
                {{-- TailAdmin Header Style --}}
                <div class="px-6 py-5 shrink-0">
                    <h3 class="text-base font-bold text-slate-800 uppercase tracking-tight">
                        Status Distribusi
                    </h3>
                </div>

                {{-- TailAdmin Body Style with Chart Wrapper --}}
                <div class="border-t border-slate-100 p-6 flex-grow relative w-full h-full">
                    {{--
                       Menggunakan 'absolute inset-0' di dalam body agar chart
                       mengisi sisa ruang kartu TailAdmin dengan sempurna
                    --}}
                    <div id="chartSixteen" wire:ignore class="absolute inset-0 flex items-center justify-center p-4"></div>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Tabel --}}
        <div class="col-span-12 lg:col-span-8 rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col overflow-hidden h-full">
            <div class="px-8 py-6 flex items-center justify-between border-b border-slate-100 shrink-0">
                <h3 class="text-lg font-black text-slate-800 tracking-tighter uppercase">Pendaftaran Terbaru</h3>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama mahasiswa..."
                        class="bg-slate-100 border-none rounded-2xl text-xs font-bold pl-10 focus:ring-2 focus:ring-indigo-500 w-64 py-3 text-slate-600 outline-none">
                    <svg class="w-4 h-4 absolute left-4 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex-grow overflow-y-auto scrollbar-hide">
                <table class="w-full text-left">
                    <thead class="sticky top-0 bg-white z-10 border-b border-slate-50">
                        <tr class="text-slate-400 text-[10px] uppercase font-black tracking-widest">
                            <th class="px-8 py-5">Mahasiswa</th>
                            <th class="px-8 py-5">Mata Kuliah</th>
                            <th class="px-8 py-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($enrollments as $row)
                        <tr class="hover:bg-indigo-50/40 transition-colors group">
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 text-xs group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        {{ strtoupper(substr($row->student->name ?? 'NA', 0, 2)) }}
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm tracking-tight">{{ $row->student->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <span class="text-slate-500 font-bold text-xs uppercase">{{ $row->course->name ?? '-' }}</span>
                            </td>
                            <td class="px-8 py-4 text-center">
                                @php
                                $statusColor = match(strtolower($row->status)) {
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'submitted' => 'bg-indigo-100 text-indigo-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                'rejected' => 'bg-rose-100 text-rose-700',
                                default => 'bg-slate-100 text-slate-600'
                                };
                                @endphp
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tight {{ $statusColor }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-8 py-20 text-center text-slate-300 font-bold italic uppercase">Data Tidak Ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex justify-between shrink-0">
                <span>Menampilkan {{ $enrollments->count() }} aktivitas terakhir</span>
                <span>Total Data: {{ $stats['total'] }}</span>
            </div>
        </div>
    </main>

    {{-- Script Section --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let chartInstance = null;

        function renderStatusChart(data) {
            // ID disesuaikan dengan snippet TailAdmin
            const chartElement = document.querySelector("#chartSixteen");
            if (!chartElement) return;

            const seriesData = [
                parseInt(data.approved),
                parseInt(data.submitted),
                parseInt(data.draft),
                parseInt(data.rejected)
            ];
            const totalKrs = parseInt(data.total);

            const options = {
                series: seriesData,
                chart: {
                    type: 'donut',
                    width: '90%',
                    height: '90%',
                    fontFamily: 'Plus Jakarta Sans, sans-serif',
                    animations: {
                        enabled: true
                    },
                    parentHeightOffset: 0,
                },
                labels: ['Approved', 'Submitted', 'Draft', 'Rejected'],
                colors: ['#10b981', '#6366f1', '#94a3b8', '#f43f5e'],
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['#ffffff']
                },
                plotOptions: {
                    pie: {
                        // Custom scale agar pas di dalam card TailAdmin
                        customScale: 0.8,
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '10px',
                                    fontWeight: 700,
                                    color: '#64748b',
                                    offsetY: -5
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 800,
                                    color: '#1e293b',
                                    offsetY: 5,
                                    formatter: (val) => val
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'TOTAL',
                                    fontSize: '10px',
                                    fontWeight: 700,
                                    color: '#94a3b8',
                                    formatter: function(w) {
                                        return totalKrs
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'top',
                    fontSize: '11px',
                    fontWeight: 600,
                    markers: {
                        radius: 12
                    },
                    itemMargin: {
                        horizontal: 5,
                        vertical: 0
                    }
                },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: (val) => val + " Data"
                    }
                }
            };

            if (chartInstance) {
                chartInstance.updateSeries(seriesData);
                chartInstance.updateOptions({
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    total: {
                                        formatter: () => totalKrs
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                chartInstance = new ApexCharts(chartElement, options);
                chartInstance.render();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const initialStats = @json($stats);
            renderStatusChart(initialStats);
        });

        window.addEventListener('update-chart', event => {
            renderStatusChart(event.detail.stats);
        });
    </script>
</div>
