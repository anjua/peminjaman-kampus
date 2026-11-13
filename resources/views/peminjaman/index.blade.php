@extends('layouts.app')

@section('title', 'Peminjaman Saya')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush

@section('aksi')
@can('ajukan-peminjaman')
<a href="{{ route('peminjaman.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Ajukan</a>
@endcan
@endsection

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="filter-status">Status</label>
                <select id="filter-status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($status as $s)
                    <option value="{{ $s->kode }}">{{ $s->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filter-tanggal">Rentang tanggal</label>
                <input type="text" id="filter-tanggal" class="form-control" placeholder="Pilih rentang" readonly />
            </div>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-peminjaman" data-url="{{ route('peminjaman.index') }}">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Ruang / Alat</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endpush

@push('page-js')
<script src="{{ asset('js/peminjaman-index.js') }}"></script>
@endpush