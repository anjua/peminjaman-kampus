@extends('layouts.app')

@section('title', 'Laporan')

@include('layouts.partials.datatables')
@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush
@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush

@section('aksi')
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-ekspor"><i
        class="ti ti-download me-1"></i> Ekspor</button>
@endsection

@section('content')
<form class="card mb-4" id="filter-laporan" data-url="{{ route('admin.laporan.index') }}">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="periode_id">Periode</label>
                <select class="form-select" id="periode_id" name="periode_id">
                    <option value="">Rentang tanggal sendiri</option>
                    @foreach ($periode as $pr)
                    <option value="{{ $pr->id }}">{{ $pr->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="rentang">Rentang tanggal</label>
                <input type="text" class="form-control" id="rentang" placeholder="Bulan ini" readonly />
                <input type="hidden" name="dari" /><input type="hidden" name="sampai" />
            </div>
            <div class="col-md-4">
                <label class="form-label" for="f-status">Status</label>
                <select class="select2 form-select" id="f-status" name="status" multiple
                    data-placeholder="Semua status">
                    @foreach ($status as $s)<option value="{{ $s->kode }}">{{ $s->label }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="f-ruang">Ruang</label>
                <select class="select2 form-select" id="f-ruang" name="ruang_id" multiple
                    data-placeholder="Semua ruang">
                    @foreach ($ruang as $r)<option value="{{ $r->id }}">{{ $r->nama }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="f-alat">Alat</label>
                <select class="select2 form-select" id="f-alat" name="alat_id" multiple data-placeholder="Semua alat">
                    @foreach ($alat as $a)<option value="{{ $a->id }}">{{ $a->nama }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="f-peran">Peran peminjam</label>
                <select class="select2 form-select" id="f-peran" name="peran_id" multiple
                    data-placeholder="Semua peran">
                    @foreach ($peran as $pr)<option value="{{ $pr->id }}">{{ $pr->nama }}</option>@endforeach
                </select>
            </div>
        </div>
    </div>
</form>

<h5 class="mb-3" id="judul-periode"></h5>
<div class="row g-4 mb-4" id="kartu-status"></div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Peminjaman per hari</h5>
            </div>
            <div class="card-body">
                <div id="grafik-harian"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">5 ruang teratas</h5>
            </div>
            <div class="card-body">
                <div id="grafik-ruang"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-laporan">
            <thead>
                <tr>
                    <th>Peminjam</th>
                    <th>Waktu</th>
                    <th>Ruang / Alat</th>
                    <th>Status</th>
                    <th>Denda</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-ekspor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ekspor laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Filter yang sedang aktif ikut diterapkan. Pilih kolom:</p>
                <div class="row" id="pilihan-kolom">
                    @foreach ($katalogKolom as $kode => $judul)
                    <div class="col-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="{{ $kode }}" id="kolom-{{ $kode }}"
                                @checked(in_array($kode, $kolomDefault)) />
                            <label class="form-check-label" for="kolom-{{ $kode }}">{{ $judul }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-label-danger" id="unduh-pdf" data-url="{{ route('admin.laporan.pdf') }}" href="#"><i
                        class="ti ti-file-text me-1"></i> PDF</a>
                <a class="btn btn-label-success" id="unduh-excel" data-url="{{ route('admin.laporan.excel') }}"
                    href="#"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-laporan.js') }}"></script>
@endpush