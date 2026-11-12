@extends('layouts.app')

@section('title', 'Peran & Hak Akses')

@section('content')
<p class="mb-4">Peran menentukan menu dan fitur yang bisa dipakai pengguna. Centang hak akses untuk setiap peran;
    perubahan langsung berlaku tanpa mengubah kode.</p>

<div class="row g-4">
    @foreach ($peran as $pr)
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="fw-normal mb-0">{{ $pr->pengguna_count }} pengguna</h6>
                    <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                        @foreach ($pr->pengguna as $u)
                        <li class="avatar avatar-sm pull-up" title="{{ $u->name }}"><span
                                class="avatar-initial rounded-circle bg-label-primary">{{ $u->inisial }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="d-flex justify-content-between align-items-end mt-1">
                    <div class="role-heading">
                        <h4 class="mb-1">{{ $pr->nama }}</h4>
                        <small class="text-muted d-block mb-2">{{ $pr->keterangan }}</small>
                        <a href="javascript:;" class="ubah-peran"
                            data-peran="{{ json_encode(['id' => $pr->id, 'kode' => $pr->kode, 'nama' => $pr->nama, 'keterangan' => $pr->keterangan, 'hak_akses' => $pr->hakAkses->pluck('id'), 'url' => route('admin.peran.update', $pr), 'url_hapus' => route('admin.peran.destroy', $pr)]) }}">
                            <span>Ubah peran</span>
                        </a>
                    </div>
                    <span class="badge bg-label-secondary">{{ $pr->hakAkses->count() }} hak</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="row h-100">
                <div class="col-sm-5">
                    <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-3">
                        <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}"
                            class="img-fluid mt-sm-4 mt-md-0" alt="" width="83" />
                    </div>
                </div>
                <div class="col-sm-7">
                    <div class="card-body text-sm-end text-center ps-sm-0">
                        <button type="button" class="btn btn-primary mb-2 text-nowrap" id="tambah-peran">Tambah
                            Peran</button>
                        <p class="mb-0 mt-1">Buat peran baru dengan hak akses terbatas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-peran" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
        <div class="modal-content p-3 p-md-5">
            <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal"></button>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h3 class="role-title mb-2" id="judul-peran">Tambah Peran</h3>
                    <p class="text-muted">Atur hak akses peran</p>
                </div>
                <form id="form-peran" class="row g-3" data-url-tambah="{{ route('admin.peran.store') }}" novalidate>
                    <div class="col-md-4">
                        <label class="form-label" for="kode">Kode</label>
                        <input type="text" id="kode" name="kode" class="form-control" placeholder="petugas-lab" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="nama">Nama peran</label>
                        <input type="text" id="nama" name="nama" class="form-control"
                            placeholder="Petugas Laboratorium" />
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="keterangan">Keterangan</label>
                        <input type="text" id="keterangan" name="keterangan" class="form-control" />
                    </div>
                    <div class="col-12">
                        <h5>Hak akses</h5>
                        <div class="table-responsive">
                            <table class="table table-flush-spacing">
                                <tbody>
                                    <tr>
                                        <td class="text-nowrap fw-semibold">Semua hak akses</td>
                                        <td>
                                            <div class="form-check"><input class="form-check-input" type="checkbox"
                                                    id="pilih-semua" /><label class="form-check-label"
                                                    for="pilih-semua">Pilih semua</label></div>
                                        </td>
                                    </tr>
                                    @foreach ($hakAkses as $grup => $daftar)
                                    <tr>
                                        <td class="text-nowrap fw-semibold">{{ $grup }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach ($daftar as $h)
                                                <div class="form-check">
                                                    <input class="form-check-input hak" type="checkbox"
                                                        value="{{ $h->id }}" id="hak-{{ $h->id }}" />
                                                    <label class="form-check-label" for="hak-{{ $h->id }}"
                                                        title="{{ $h->kode }}">{{ $h->nama }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
                        <button type="button" class="btn btn-label-danger d-none" id="hapus-peran">Hapus peran</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-peran.js') }}"></script>
@endpush