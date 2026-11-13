@extends('layouts.app')

@section('title', 'Peminjaman #' . $p->id)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Peminjaman</a></li>
@endsection

@section('aksi')
@can('bukti', $p)
<a href="{{ route('peminjaman.bukti', $p) }}" class="btn btn-label-primary"><i class="ti ti-file-download me-1"></i>
    Bukti PDF</a>
@endcan
@can('batal', $p)
<button type="button" class="btn btn-label-danger" id="tombol-batal" data-url="{{ route('peminjaman.batal', $p) }}"><i
        class="ti ti-x me-1"></i> Batalkan</button>
@endcan
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body d-flex flex-wrap justify-content-between gap-3 border-bottom">
                <div>
                    <h5 class="mb-1">{{ $p->ruang?->nama ?? 'Peminjaman alat' }}</h5>
                    <p class="mb-0 text-muted">{{ $p->ruang?->lokasi }}</p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-label-{{ $p->statusRef->warna }} fs-6">{{ $p->statusRef->label }}</span>
                    <p class="mb-0 mt-2"><i class="ti ti-calendar me-1"></i>{{ $p->waktu_mulai->translatedFormat('l, d F
                        Y') }}</p>
                    <p class="mb-0"><i class="ti ti-clock me-1"></i>{{ $p->waktu_mulai->format('H:i') }}–{{
                        $p->waktu_selesai->format('H:i') }}</p>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <h6 class="mb-1">Peminjam</h6>
                        <p class="mb-0">{{ $p->pengguna->name }}</p>
                        <small class="text-muted">{{ $p->pengguna->nim_nip }} · {{ $p->pengguna->peran?->nama }}</small>
                    </div>
                    <div class="col-sm-6">
                        <h6 class="mb-1">Jumlah peserta</h6>
                        <p class="mb-0">{{ $p->jumlah_peserta }} orang</p>
                    </div>
                    <div class="col-12">
                        <h6 class="mb-1">Keperluan</h6>
                        <p class="mb-0">{{ $p->keperluan }}</p>
                    </div>
                    @if ($p->catatan_admin)
                    <div class="col-12">
                        <div class="alert alert-{{ $p->status === 'ditolak' ? 'danger' : 'secondary' }} mb-0">
                            <strong>Catatan petugas:</strong> {{ $p->catatan_admin }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @if ($p->detail->isNotEmpty())
            <div class="table-responsive border-top">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>Alat</th>
                            <th class="text-center">Jumlah</th>
                            <th>Kode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($p->detail as $d)
                        <tr>
                            <td>{{ $d->alat->nama }}</td>
                            <td class="text-center">{{ $d->jumlah }}</td>
                            <td>{{ $d->alat->kode }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            @if ($p->dikembalikan_pada)
            <div class="card-body border-top">
                <div class="row">
                    <div class="col-sm-4">
                        <h6 class="mb-1">Dikembalikan</h6>{{ $p->dikembalikan_pada->translatedFormat('d M Y H:i') }}
                    </div>
                    <div class="col-sm-4">
                        <h6 class="mb-1">Kondisi</h6>{{ $kondisi[$p->kondisi_kembali] ?? $p->kondisi_kembali }}
                    </div>
                    <div class="col-sm-4">
                        <h6 class="mb-1">Denda</h6>
                        Rp{{ number_format($p->denda, 0, ',', '.') }}
                        @if ($p->denda > 0)
                        <span class="badge bg-label-{{ $p->denda_lunas ? 'success' : 'danger' }}">{{ $p->denda_lunas ?
                            'Lunas' : 'Belum lunas' }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Riwayat status</h5>
            </div>
            <div class="card-body pb-0">
                <ul class="timeline mb-0">
                    <li class="timeline-item timeline-item-transparent">
                        <span class="timeline-point timeline-point-primary"></span>
                        <div class="timeline-event">
                            <div class="timeline-header">
                                <h6 class="mb-0">Diajukan</h6>
                            </div>
                            <small class="text-muted">{{ $p->created_at->translatedFormat('d M Y H:i') }}</small>
                        </div>
                    </li>
                    @if ($p->disetujui_pada)
                    <li class="timeline-item timeline-item-transparent">
                        <span
                            class="timeline-point timeline-point-{{ $p->status === 'ditolak' ? 'danger' : 'success' }}"></span>
                        <div class="timeline-event">
                            <div class="timeline-header">
                                <h6 class="mb-0">{{ $p->status === 'ditolak' ? 'Ditolak' : 'Disetujui' }}</h6>
                            </div>
                            <small class="text-muted">{{ $p->disetujui_pada->translatedFormat('d M Y H:i') }} oleh {{
                                $p->penyetuju?->name ?? 'sistem' }}</small>
                        </div>
                    </li>
                    @endif
                    @if ($p->dikembalikan_pada)
                    <li class="timeline-item timeline-item-transparent">
                        <span class="timeline-point timeline-point-info"></span>
                        <div class="timeline-event">
                            <div class="timeline-header">
                                <h6 class="mb-0">Alat dikembalikan</h6>
                            </div>
                            <small class="text-muted">{{ $p->dikembalikan_pada->translatedFormat('d M Y H:i') }}</small>
                        </div>
                    </li>
                    @endif
                    @if (in_array($p->status, ['dibatalkan', 'selesai']))
                    <li class="timeline-item timeline-item-transparent">
                        <span class="timeline-point timeline-point-{{ $p->statusRef->warna }}"></span>
                        <div class="timeline-event">
                            <div class="timeline-header">
                                <h6 class="mb-0">{{ $p->statusRef->label }}</h6>
                            </div>
                            <small class="text-muted">{{ $p->updated_at->translatedFormat('d M Y H:i') }}</small>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    document.getElementById('tombol-batal')?.addEventListener('click', async e => {
      const tombol = e.currentTarget;
      if (!(await konfirmasi('Batalkan peminjaman ini?', 'Slot ruang dan alat akan dilepas untuk orang lain.', 'Ya, batalkan', 'warning'))) return;
      await kirim(tombol.dataset.url, 'PATCH', null, tombol);
      setTimeout(() => location.reload(), 800);
    });
</script>
@endpush