@extends('layouts.app')

@section('title', 'Pengembalian & Denda')

@include('layouts.partials.datatables')

@section('content')
<div class="nav-align-top">
    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item"><button type="button" class="nav-link active" data-tab="belum"><i
                    class="ti ti-package me-1"></i> Belum dikembalikan</button></li>
        <li class="nav-item"><button type="button" class="nav-link" data-tab="denda"><i class="ti ti-cash me-1"></i>
                Denda belum lunas</button></li>
        <li class="nav-item"><button type="button" class="nav-link" data-tab="riwayat"><i
                    class="ti ti-history me-1"></i> Riwayat pengembalian</button></li>
    </ul>
</div>
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-pengembalian" data-url="{{ route('admin.pengembalian.index') }}">
            <thead>
                <tr>
                    <th>Peminjam</th>
                    <th>Alat</th>
                    <th>Batas kembali</th>
                    <th>Keterangan</th>
                    <th>Denda</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Modal catat pengembalian --}}
<div class="modal fade" id="modal-kembali" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="form-kembali" novalidate>
            <div class="modal-header">
                <h5 class="modal-title">Catat pengembalian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" id="info-kembali"></p>
                <label class="form-label">Kondisi alat saat kembali</label>
                <div class="row g-2 mb-3">
                    @foreach ($kondisi as $kode => $label)
                    <div class="col">
                        <div class="form-check custom-option custom-option-basic">
                            <label class="form-check-label custom-option-content" for="kondisi-{{ $kode }}">
                                <input name="kondisi_kembali" class="form-check-input" type="radio" value="{{ $kode }}"
                                    id="kondisi-{{ $kode }}" @checked($loop->first) />
                                <span class="custom-option-header"><span class="fw-semibold">{{ $label }}</span></span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                <label class="form-label" for="catatan-kembali">Catatan (opsional)</label>
                <textarea class="form-control mb-3" id="catatan-kembali" name="catatan_admin" rows="2"></textarea>
                <div class="alert mb-0" id="perkiraan-denda"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
    const KONDISI = @json($kondisi), BOLEH_LUNAS = @json(auth()->user()->can('kelola-denda'));
</script>
<script src="{{ asset('js/admin-pengembalian.js') }}"></script>
@endpush