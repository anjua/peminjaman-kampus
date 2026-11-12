@extends('layouts.app')

@section('title', 'Ruang')

@include('layouts.partials.datatables')

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-ruang" data-url="{{ route('admin.ruang.index') }}">
            <thead>
                <tr>
                    <th>Ruang</th>
                    <th>Lokasi</th>
                    <th>Kapasitas</th>
                    <th>Dipinjam</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-ruang">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="judul-form">Tambah Ruang</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
        <form id="form-ruang" data-url-tambah="{{ route('admin.ruang.store') }}" novalidate>
            <div class="mb-3">
                <label class="form-label" for="kode">Kode</label>
                <input type="text" class="form-control" id="kode" name="kode" placeholder="GD-A-301" maxlength="20"
                    required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="nama">Nama ruang</label>
                <input type="text" class="form-control" id="nama" name="nama" maxlength="100" required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="lokasi">Lokasi</label>
                <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Gedung A Lt. 3"
                    maxlength="150" required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="kapasitas">Kapasitas (orang)</label>
                <input type="number" class="form-control" id="kapasitas" name="kapasitas" min="1" required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="fasilitas">Fasilitas</label>
                <textarea class="form-control" id="fasilitas" name="fasilitas" rows="2"
                    placeholder="Proyektor, AC, ..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="foto">Foto (opsional, maks 2 MB)</label>
                <input type="file" class="form-control" id="foto" name="foto" accept="image/*" />
                <img id="pratinjau-foto" class="img-fluid rounded mt-2 d-none" alt="" />
            </div>
            <div class="mb-4 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="aktif" name="aktif" checked />
                <label class="form-check-label" for="aktif">Aktif (bisa dipinjam)</label>
            </div>
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-ruang.js') }}"></script>
@endpush