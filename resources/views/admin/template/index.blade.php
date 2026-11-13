@extends('layouts.app')

@section('title', 'Template Notifikasi')

@include('layouts.partials.datatables')

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabel-template" data-url="{{ route('admin.template.index') }}">
            <thead>
                <tr>
                    <th>Notifikasi</th>
                    <th>Isi</th>
                    <th>Kanal</th>
                    <th>Penerima</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-template" style="width: 480px">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="judul-form">Ubah template</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
        <form id="form-template" novalidate>
            <div class="mb-3">
                <label class="form-label" for="judul">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" />
            </div>
            <div class="mb-2">
                <label class="form-label" for="isi">Isi pesan</label>
                <textarea class="form-control" id="isi" name="isi" rows="4"></textarea>
            </div>
            <div class="mb-3 d-flex flex-wrap gap-1" id="tombol-penanda">
                @foreach (array_keys($contoh ?? []) as $penanda)
                <button type="button" class="btn btn-xs btn-label-secondary" data-penanda="{{ $penanda }}">{{ $penanda
                    }}</button>
                @endforeach
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label" for="ikon">Ikon (Tabler)</label>
                    <input type="text" class="form-control" id="ikon" name="ikon" placeholder="ti ti-bell" />
                </div>
                <div class="col-6">
                    <label class="form-label" for="warna">Warna</label>
                    <select class="form-select" id="warna" name="warna">
                        @foreach (['primary', 'secondary', 'success', 'danger', 'warning', 'info'] as $w)<option
                            value="{{ $w }}">{{ $w }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label d-block">Kanal</label>
                <div class="form-check form-check-inline"><input class="form-check-input kanal" type="checkbox"
                        value="database" id="kanal-db" /><label class="form-check-label" for="kanal-db">Di aplikasi
                        (lonceng)</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input kanal" type="checkbox"
                        value="mail" id="kanal-mail" /><label class="form-check-label" for="kanal-mail">Email</label>
                </div>
                <div data-error="kanal"></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="penerima">Penerima</label>
                <select class="form-select" id="penerima" name="penerima">
                    @foreach ($penerima as $kode => $label)<option value="{{ $kode }}">{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="aktif" name="aktif" />
                <label class="form-check-label" for="aktif">Aktif</label>
            </div>
            <label class="form-label">Pratinjau</label>
            <div class="border rounded p-3 mb-4 d-flex" id="pratinjau"></div>
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
    const PENERIMA = @json($penerima);
    const CONTOH = @json($contoh); 
</script>
<script src="{{ asset('js/admin-template.js') }}"></script>
@endpush