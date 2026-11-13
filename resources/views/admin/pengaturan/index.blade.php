@extends('layouts.app')

@section('title', 'Pengaturan')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush
@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endpush

@section('content')
<ul class="nav nav-pills flex-column flex-md-row mb-4" role="tablist">
    @foreach ($grup as $nama => $isi)
    <li class="nav-item">
        <button class="nav-link @if ($loop->first) active @endif" data-bs-toggle="pill"
            data-bs-target="#tab-{{ $nama }}" type="button">
            <i class="ti ti-adjustments me-1"></i>{{ ucfirst($nama) }}
        </button>
    </li>
    @endforeach
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-jam" type="button"><i
                class="ti ti-clock me-1"></i>Jam Operasional</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-libur" type="button"><i
                class="ti ti-beach me-1"></i>Hari Libur</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-status" type="button"><i
                class="ti ti-tag me-1"></i>Status</button></li>
</ul>

<div class="tab-content p-0">
    @foreach ($grup as $nama => $isi)
    <div class="tab-pane fade @if ($loop->first) show active @endif" id="tab-{{ $nama }}">
        <form class="card form-pengaturan" data-url="{{ route('admin.pengaturan.simpan') }}" novalidate>
            <div class="card-body">
                <div class="row g-4">
                    @foreach ($isi as $p)
                    <div class="col-md-6">
                        @if ($p->tipe === 'boolean')
                        <input type="hidden" name="nilai[{{ $p->id }}]" value="0" />
                        <div class="form-check form-switch mt-md-4">
                            <input class="form-check-input" type="checkbox" id="p-{{ $p->id }}"
                                name="nilai[{{ $p->id }}]" value="1" @checked(filter_var($p->nilai,
                            FILTER_VALIDATE_BOOLEAN)) />
                            <label class="form-check-label" for="p-{{ $p->id }}">{{ $p->label }}</label>
                        </div>
                        @else
                        <label class="form-label" for="p-{{ $p->id }}">{{ $p->label }}</label>
                        @if ($p->tipe === 'integer')
                        <input type="number" min="0" class="form-control" id="p-{{ $p->id }}" name="nilai[{{ $p->id }}]"
                            value="{{ $p->nilai }}" />
                        @elseif ($p->tipe === 'time')
                        <input type="text" class="form-control jam" id="p-{{ $p->id }}" name="nilai[{{ $p->id }}]"
                            value="{{ $p->nilai }}" />
                        @elseif ($p->tipe === 'json')
                        <textarea class="form-control font-monospace" rows="3" id="p-{{ $p->id }}"
                            name="nilai[{{ $p->id }}]">{{ $p->nilai }}</textarea>
                        @elseif ($p->tipe === 'file')
                        <input type="file" accept="image/*" class="form-control" id="p-{{ $p->id }}"
                            name="nilai[{{ $p->id }}]" />
                        @if ($p->nilai)<img src="{{ asset('storage/' . $p->nilai) }}" alt="" class="mt-2 rounded"
                            height="48" />@endif
                        @elseif ($p->kunci === 'aplikasi.peran_registrasi')
                        <select class="form-select" id="p-{{ $p->id }}" name="nilai[{{ $p->id }}]">
                            @foreach ($peran as $pr)<option value="{{ $pr->kode }}" @selected($pr->kode ===
                                $p->nilai)>{{ $pr->nama }}</option>@endforeach
                        </select>
                        @else
                        <input type="text" class="form-control" id="p-{{ $p->id }}" name="nilai[{{ $p->id }}]"
                            value="{{ $p->nilai }}" />
                        @endif
                        @endif
                        @if ($p->keterangan)<div class="form-text">{{ $p->keterangan }}</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer border-top pt-4"><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
    @endforeach

    <div class="tab-pane fade" id="tab-jam">
        <form class="card" id="form-jam" data-url="{{ route('admin.jam-operasional.simpan') }}" novalidate>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Buka</th>
                            <th>Jam buka</th>
                            <th>Jam tutup</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jam as $j)
                        <tr data-hari="{{ $j->hari }}">
                            <td class="fw-semibold">{{ $j->nama_hari }}</td>
                            <td>
                                <div class="form-check form-switch"><input class="form-check-input aktif"
                                        type="checkbox" @checked($j->aktif) /></div>
                            </td>
                            <td><input type="text" class="form-control jam buka" name="jam[{{ $j->hari }}][buka]"
                                    value="{{ substr($j->buka, 0, 5) }}" /></td>
                            <td><input type="text" class="form-control jam tutup" name="jam[{{ $j->hari }}][tutup]"
                                    value="{{ substr($j->tutup, 0, 5) }}" /></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer border-top pt-4"><button type="submit" class="btn btn-primary">Simpan jam
                    operasional</button></div>
        </form>
    </div>

    <div class="tab-pane fade" id="tab-libur">
        <div class="card">
            <form class="card-body border-bottom row g-3 align-items-end" id="form-libur"
                data-url="{{ route('admin.hari-libur.store') }}" novalidate>
                <div class="col-md-4">
                    <label class="form-label" for="tanggal-libur">Tanggal</label>
                    <input type="text" class="form-control" id="tanggal-libur" name="tanggal"
                        placeholder="Pilih tanggal" />
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="keterangan-libur">Keterangan</label>
                    <input type="text" class="form-control" id="keterangan-libur" name="keterangan"
                        placeholder="Dies Natalis" />
                </div>
                <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i
                            class="ti ti-plus me-1"></i>Tambah</button></div>
            </form>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="daftar-libur">
                        @forelse ($libur as $l)
                        <tr>
                            <td>{{ $l->tanggal->translatedFormat('l, d F Y') }}</td>
                            <td>{{ $l->keterangan }}</td>
                            <td class="text-end"><button type="button"
                                    class="btn btn-sm btn-icon btn-label-danger hapus-libur"
                                    data-url="{{ route('admin.hari-libur.destroy', $l) }}"><i
                                        class="ti ti-trash"></i></button></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="kosong"><i class="ti ti-beach"></i>Belum ada hari libur</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-status">
        <form class="card" id="form-status" data-url="{{ route('admin.status.simpan') }}" novalidate>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode sistem</th>
                            <th>Label</th>
                            <th>Warna</th>
                            <th>Pratinjau</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($status as $s)
                        <tr>
                            <td><code>{{ $s->kode }}</code></td>
                            <td><input type="text" class="form-control label-status"
                                    name="status[{{ $s->kode }}][label]" value="{{ $s->label }}" /></td>
                            <td>
                                <select class="form-select warna-status" name="status[{{ $s->kode }}][warna]">
                                    @foreach (['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark']
                                    as $w)
                                    <option value="{{ $w }}" @selected($w===$s->warna)>{{ $w }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><span class="badge bg-label-{{ $s->warna }} pratinjau">{{ $s->label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer border-top pt-4"><button type="submit" class="btn btn-primary">Simpan
                    status</button></div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{ asset('js/admin-pengaturan.js') }}"></script>
@endpush