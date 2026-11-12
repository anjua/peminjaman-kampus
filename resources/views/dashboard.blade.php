@extends('layouts.app')

@section('title', 'Dashboard')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('aksi')
@can('ajukan-peminjaman')
<a href="{{ route('peminjaman.create') }}" class="btn btn-primary"><i class="ti ti-calendar-plus me-1"></i> Ajukan
    Peminjaman</a>
@endcan
@endsection

@section('content')
@isset($admin)
<div class="row g-4 mb-4">
    @foreach ([
    ['Menunggu persetujuan', $admin['menunggu'], 'ti-hourglass', 'warning', route('admin.persetujuan.index')],
    ['Disetujui hari ini', $admin['hari_ini'], 'ti-calendar-check', 'success', route('kalender.index')],
    ['Ruang sedang dipakai', $admin['ruang_dipakai'] . ' / ' . $admin['ruang_total'], 'ti-door', 'primary',
    route('kalender.index')],
    ['Alat terlambat', $admin['terlambat'], 'ti-alert-triangle', 'danger', route('admin.pengembalian.index')],
    ] as [$judul, $nilai, $ikon, $warna, $tautan])
    <div class="col-sm-6 col-xl-3">
        <a href="{{ $tautan }}" class="card h-100 text-body">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted">{{ $judul }}</span>
                    <h3 class="mb-0 mt-2">{{ $nilai }}</h3>
                </div>
                <span class="badge bg-label-{{ $warna }} rounded p-2"><i class="ti {{ $ikon }} ti-md"></i></span>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <h5 class="card-title mb-0">Peminjaman 6 bulan terakhir</h5>
                    <small class="text-muted">Per status, berdasarkan tanggal mulai</small>
                </div>
            </div>
            <div class="card-body">
                <div id="grafik-bulanan"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Ruang paling sering dipinjam</h5>
                <small class="text-muted">Denda belum lunas: <span class="fw-semibold text-danger">Rp{{
                        number_format($admin['denda_belum_lunas'], 0, ',', '.') }}</span></small>
            </div>
            <div class="card-body">
                <ul class="p-0 m-0">
                    @forelse ($ruang_populer as $r)
                    <li class="d-flex align-items-center mb-3">
                        <div class="avatar flex-shrink-0 me-3"><span class="avatar-initial rounded bg-label-primary"><i
                                    class="ti ti-door"></i></span></div>
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $r->nama }}</h6><small class="text-muted">{{ $r->kode }}</small>
                            </div>
                            <span class="badge bg-label-secondary">{{ $r->peminjaman_count }}×</span>
                        </div>
                    </li>
                    @empty
                    <li class="kosong"><i class="ti ti-chart-bar"></i>Belum ada data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endisset

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Peminjaman saya</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($status as $s)
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-{{ $s->warna }} rounded p-2"><i class="ti ti-point"></i></span>
                            <div>
                                <h5 class="mb-0">{{ $milikku[$s->kode] ?? 0 }}</h5><small class="text-muted">{{
                                    $s->label }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if ($dendaku > 0)
                <div class="alert alert-danger mt-4 mb-0 d-flex align-items-center" role="alert">
                    <i class="ti ti-cash me-2"></i>
                    Kamu punya denda belum lunas Rp{{ number_format($dendaku, 0, ',', '.') }}. Selesaikan di bagian
                    sarpras.
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Jadwal terdekat</h5>
                <a href="{{ route('peminjaman.index') }}" class="small">Lihat semua</a>
            </div>
            <div class="card-body pb-0">
                @if ($terdekat->isEmpty())
                <div class="kosong"><i class="ti ti-calendar-off"></i>Belum ada peminjaman aktif.</div>
                @else
                <ul class="timeline mb-0">
                    @foreach ($terdekat as $p)
                    <li class="timeline-item timeline-item-transparent">
                        <span class="timeline-point timeline-point-{{ $p->statusRef->warna }}"></span>
                        <div class="timeline-event">
                            <div class="timeline-header mb-1">
                                <h6 class="mb-0"><a href="{{ route('peminjaman.show', $p) }}">{{ $p->ruang?->nama ??
                                        $p->ringkasan_alat }}</a></h6>
                                <small class="text-muted">{{ $p->waktu_mulai->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $p->waktu_mulai->translatedFormat('l, d M Y · H:i') }}–{{
                                $p->waktu_selesai->format('H:i') }}</p>
                            <span class="badge bg-label-{{ $p->statusRef->warna }}">{{ $p->statusRef->label }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@isset($grafik)
@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush
@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const grafik = @json($grafik);
        const warna = grafik.warna.map(w => getComputedStyle(document.documentElement).getPropertyValue(`--bs-${w}`).trim() || config.colors[w]);
        new ApexCharts(document.querySelector('#grafik-bulanan'), {
          chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
          series: grafik.seri,
          colors: warna,
          xaxis: { categories: grafik.kategori },
          plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
          dataLabels: { enabled: false },
          legend: { position: 'top', horizontalAlign: 'left' },
          noData: { text: 'Belum ada data' },
        }).render();
      });
</script>
@endpush
@endisset