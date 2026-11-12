@extends('layouts.app')

@section('title', 'Pengguna')

@include('layouts.partials.datatables')

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="filter-peran">Peran</label>
                <select id="filter-peran" class="form-select">
                    <option value="">Semua peran</option>
                    @foreach ($peran as $pr)<option value="{{ $pr->id }}">{{ $pr->nama }}</option>@endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-pengguna" data-url="{{ route('admin.pengguna.index') }}">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>NIM/NIP</th>
                    <th>Peran</th>
                    <th>Peminjaman</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-pengguna">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="judul-form">Ubah pengguna</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
        <form id="form-pengguna" novalidate>
            <div class="mb-3">
                <label class="form-label" for="peran_id">Peran</label>
                <select class="form-select" id="peran_id" name="peran_id">
                    @foreach ($peran as $pr)<option value="{{ $pr->id }}">{{ $pr->nama }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="nim_nip">NIM/NIP</label>
                <input type="text" class="form-control" id="nim_nip" name="nim_nip" />
            </div>
            <div class="mb-3">
                <label class="form-label" for="telepon">Telepon</label>
                <input type="text" class="form-control" id="telepon" name="telepon" />
            </div>
            <div class="mb-4 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="aktif" name="aktif" />
                <label class="form-check-label" for="aktif">Akun aktif (bisa login)</label>
            </div>
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-pengguna.js') }}"></script>
@endpush