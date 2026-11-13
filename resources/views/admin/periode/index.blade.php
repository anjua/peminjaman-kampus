@extends('layouts.app')

@section('title', 'Periode Akademik')

@include('layouts.partials.datatables')
@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush
@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endpush

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-periode" data-url="{{ route('admin.periode.index') }}">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-periode">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="judul-form">Tambah Periode</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
        <form id="form-periode" data-url-tambah="{{ route('admin.periode.store') }}" novalidate>
            <div class="mb-3">
                <label class="form-label" for="nama">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Semester Ganjil 2026/2027" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="tanggal_mulai">Tanggal mulai</label>
                <input type="text" class="form-control tanggal" id="tanggal_mulai" name="tanggal_mulai" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="tanggal_selesai">Tanggal selesai</label>
                <input type="text" class="form-control tanggal" id="tanggal_selesai" name="tanggal_selesai" />
            </div>
            <div class="mb-4 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="aktif" name="aktif" checked />
                <label class="form-check-label" for="aktif">Tampil di filter laporan</label>
            </div>
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-periode.js') }}"></script>
@endpush