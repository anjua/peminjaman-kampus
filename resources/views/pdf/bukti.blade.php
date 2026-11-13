<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Bukti Peminjaman #{{ $p->id }}</title>
    @include('pdf._gaya')
</head>

<body>
    @include('pdf._kop')
    <h2>BUKTI PEMINJAMAN</h2>
    <p class="tengah">Nomor: {{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}/PMJ/{{ $p->created_at->format('Y') }}</p>

    <table class="info">
        <tr>
            <td style="width: 140px">Peminjam</td>
            <td>: {{ $p->pengguna->name }} ({{ $p->pengguna->nim_nip }})</td>
        </tr>
        <tr>
            <td>Ruang</td>
            <td>: {{ $p->ruang ? $p->ruang->nama . ' — ' . $p->ruang->lokasi : '-' }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>: {{ $p->waktu_mulai->translatedFormat('l, d F Y H:i') }} s.d. {{ $p->waktu_selesai->format('H:i') }}
            </td>
        </tr>
        <tr>
            <td>Jumlah peserta</td>
            <td>: {{ $p->jumlah_peserta }} orang</td>
        </tr>
        <tr>
            <td>Keperluan</td>
            <td>: {{ $p->keperluan }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: {{ $p->statusRef->label }}</td>
        </tr>
    </table>

    @if ($p->detail->isNotEmpty())
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px">No</th>
                <th>Alat</th>
                <th style="width: 80px">Kode</th>
                <th style="width: 60px">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($p->detail as $d)
            <tr>
                <td class="tengah">{{ $loop->iteration }}</td>
                <td>{{ $d->alat->nama }}</td>
                <td>{{ $d->alat->kode }}</td>
                <td class="tengah">{{ $d->jumlah }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <table class="ttd">
        <tr>
            <td>Peminjam,<br><br><br><br>{{ $p->pengguna->name }}</td>
            <td>Disetujui oleh,<br><br><br><br>{{ $p->penyetuju?->name ?? '-' }}<br><small>{{
                    $p->disetujui_pada?->translatedFormat('d F Y H:i') }}</small></td>
        </tr>
    </table>
</body>

</html>