@extends('layouts.app')

@section('title', 'Persetujuan')

@include('layouts.partials.datatables')

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h5 class="card-title mb-0">Pengajuan menunggu</h5>
        <small class="text-muted">Diurutkan dari waktu mulai paling dekat. Pengajuan yang tidak diproses sampai waktunya
            akan ditolak otomatis sesuai pengaturan.</small>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-persetujuan" data-url="{{ route('admin.persetujuan.index') }}">
            <thead>
                <tr>
                    <th>Peminjam</th>
                    <th>Waktu</th>
                    <th>Ruang / Alat</th>
                    <th>Keperluan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-persetujuan.js') }}"></script>
@endpush