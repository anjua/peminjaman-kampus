@extends('layouts.app')

@section('title', 'Semua Peminjaman')

@include('layouts.partials.datatables')
@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush
@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endpush

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
        <table class="table border-top" id="tabel-riwayat" data-url="{{ route('admin.riwayat.index') }}">
            <thead>
                <tr>
                    <th>Peminjam</th>
                    <th>Waktu</th>
                    <th>Ruang / Alat</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-riwayat.js') }}"></script>
@endpush