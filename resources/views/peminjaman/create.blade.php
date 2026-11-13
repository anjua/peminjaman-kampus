@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Peminjaman Saya</a></li>
@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
<form id="form-pengajuan" action="{{ route('peminjaman.store') }}" data-url-cek="{{ route('peminjaman.cek') }}"
    novalidate>
    <div class="bs-stepper wizard-icons wizard-icons-example">
        <div class="bs-stepper-header">
            <div class="step" data-target="#langkah-waktu">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-icon">
                        <svg viewBox="0 0 54 54">
                            <use xlink:href="{{ asset('assets/svg/icons/form-wizard-account.svg') }}#wizardAccount">
                            </use>
                        </svg>
                    </span>
                    <span class="bs-stepper-label">Waktu</span>
                </button>
            </div>
            <div class="line"><i class="ti ti-chevron-right"></i></div>
            <div class="step" data-target="#langkah-ruang-alat">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-icon">
                        <svg viewBox="0 0 54 54">
                            <use xlink:href="{{ asset('assets/svg/icons/form-wizard-address.svg') }}#wizardAddress">
                            </use>
                        </svg>
                    </span>
                    <span class="bs-stepper-label">Ruang &amp; Alat</span>
                </button>
            </div>
            <div class="line"><i class="ti ti-chevron-right"></i></div>
            <div class="step" data-target="#langkah-konfirmasi">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-icon">
                        <svg viewBox="0 0 54 54">
                            <use xlink:href="{{ asset('assets/svg/icons/form-wizard-submit.svg') }}#wizardSubmit"></use>
                        </svg>
                    </span>
                    <span class="bs-stepper-label">Konfirmasi</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <div id="langkah-waktu" class="content">
                <div class="content-header mb-3">
                    <h6 class="mb-0">Kapan kamu butuh?</h6>
                    <small>Hari dan jam yang tidak melayani peminjaman otomatis tidak bisa dipilih.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="text" id="tanggal" class="form-control" placeholder="Pilih tanggal"
                            data-error="waktu_mulai" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="jam_mulai">Jam mulai</label>
                        <input type="text" id="jam_mulai" class="form-control" placeholder="08:00" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="jam_selesai">Jam selesai</label>
                        <input type="text" id="jam_selesai" class="form-control" placeholder="10:00"
                            data-error="waktu_selesai" readonly />
                    </div>
                    <input type="hidden" name="waktu_mulai" id="waktu_mulai" />
                    <input type="hidden" name="waktu_selesai" id="waktu_selesai" />
                    <div class="col-12">
                        <div class="alert alert-info mb-0 status-ketersediaan" id="info-jam"></div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <button type="button" class="btn btn-label-secondary btn-prev" disabled><i
                                class="ti ti-arrow-left me-sm-1"></i><span
                                class="d-none d-sm-inline-block">Sebelumnya</span></button>
                        <button type="button" class="btn btn-primary btn-next"><span
                                class="d-none d-sm-inline-block me-sm-1">Lanjut</span><i
                                class="ti ti-arrow-right"></i></button>
                    </div>
                </div>
            </div>

            <div id="langkah-ruang-alat" class="content">
                <div class="content-header mb-3">
                    <h6 class="mb-0">Pilih ruang dan/atau alat</h6>
                    <small>Ketersediaan dicek langsung untuk jam yang kamu pilih.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="ruang_id">Ruang (opsional jika hanya pinjam alat)</label>
                        <select id="ruang_id" name="ruang_id" class="select2 form-select"
                            data-placeholder="Tanpa ruang">
                            <option value=""></option>
                            @foreach ($ruang as $r)
                            <option value="{{ $r->id }}" data-kapasitas="{{ $r->kapasitas }}"
                                data-lokasi="{{ $r->lokasi }}">{{ $r->nama }} ({{ $r->kapasitas }} orang)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="jumlah_peserta">Jumlah peserta</label>
                        <input type="number" min="1" id="jumlah_peserta" name="jumlah_peserta" class="form-control"
                            value="1" />
                    </div>
                    <div class="col-12">
                        <div class="status-ketersediaan" id="info-ruang"></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">Alat (isi jumlah yang dibutuhkan)</label>
                        {{-- Kartu pilihan ala forms-custom-options.html; sisa stok diisi oleh JavaScript --}}
                        <div class="row g-3" id="daftar-alat">
                            @foreach ($alat as $i => $a)
                            <div class="col-sm-6 col-lg-4">
                                <div class="card border shadow-none kartu-pilihan h-100" data-alat="{{ $a->id }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ $a->nama }}</h6>
                                            <span class="badge bg-label-secondary sisa-stok"
                                                data-stok="{{ $a->stok }}">stok {{ $a->stok }}</span>
                                        </div>
                                        <input type="hidden" name="detail[{{ $i }}][alat_id]" value="{{ $a->id }}" />
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary kurang" type="button"><i
                                                    class="ti ti-minus"></i></button>
                                            <input type="number" class="form-control text-center jumlah-alat"
                                                name="detail[{{ $i }}][jumlah]" value="0" min="0"
                                                max="{{ $a->stok }}" />
                                            <button class="btn btn-outline-secondary tambah" type="button"><i
                                                    class="ti ti-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <button type="button" class="btn btn-label-secondary btn-prev"><i
                                class="ti ti-arrow-left me-sm-1"></i><span
                                class="d-none d-sm-inline-block">Sebelumnya</span></button>
                        <button type="button" class="btn btn-primary btn-next"><span
                                class="d-none d-sm-inline-block me-sm-1">Lanjut</span><i
                                class="ti ti-arrow-right"></i></button>
                    </div>
                </div>
            </div>

            {{-- Langkah 3: keperluan dan ringkasan --}}
            <div id="langkah-konfirmasi" class="content">
                <div class="content-header mb-3">
                    <h6 class="mb-0">Periksa lagi sebelum mengajukan</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="keperluan">Keperluan</label>
                        <textarea id="keperluan" name="keperluan" class="form-control" rows="5" maxlength="500"
                            placeholder="Contoh: Rapat koordinasi panitia seminar"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100" id="ringkasan"></div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <button type="button" class="btn btn-label-secondary btn-prev"><i
                                class="ti ti-arrow-left me-sm-1"></i><span
                                class="d-none d-sm-inline-block">Sebelumnya</span></button>
                        <button type="submit" class="btn btn-success btn-submit"><i class="ti ti-send me-1"></i>
                            Ajukan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endpush

@push('page-js')
<script>
    const ATURAN = @json($aturan);
</script>
<script src="{{ asset('js/peminjaman-form.js') }}"></script>
@endpush