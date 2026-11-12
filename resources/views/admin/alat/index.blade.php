@extends('layouts.app')

@section('title', 'Alat')

@include('layouts.partials.datatables')

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-alat" data-url="{{ route('admin.alat.index') }}">
            <thead>
                <tr>
                    <th>Alat</th>
                    <th>Stok</th>
                    <th>Dipinjam sekarang</th>
                    <th>Kondisi</th>
                    <th>Denda/jam</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-alat">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="judul-form">Tambah Alat</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
        <form id="form-alat" data-url-tambah="{{ route('admin.alat.store') }}" novalidate>
            <div class="mb-3">
                <label class="form-label" for="kode">Kode</label>
                <input type="text" class="form-control" id="kode" name="kode" placeholder="PRY-01" maxlength="20"
                    required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="nama">Nama alat</label>
                <input type="text" class="form-control" id="nama" name="nama" maxlength="100" required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="stok">Stok (unit)</label>
                <input type="number" class="form-control" id="stok" name="stok" min="0" required />
            </div>
            <div class="mb-3">
                <label class="form-label" for="kondisi">Kondisi</label>
                <select class="form-select" id="kondisi" name="kondisi">
                    @foreach ($kondisi as $kode => $label)
                    <option value="{{ $kode }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="denda_per_jam">Denda per jam per unit (Rp)</label>
                <input type="number" class="form-control" id="denda_per_jam" name="denda_per_jam" min="0"
                    placeholder="Kosongkan = tarif umum Rp{{ number_format($tarifUmum, 0, ',', '.') }}" />
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
<script>
    const KONDISI = @json($kondisi), TARIF_UMUM = {{ $tarifUmum }};
</script>
<script src="{{ asset('js/admin-alat.js') }}"></script>
@endpush