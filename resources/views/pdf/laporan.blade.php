<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman</title>
    @include('pdf._gaya')
</head>

<body>
    @include('pdf._kop')
    <h2>LAPORAN PEMINJAMAN RUANG DAN ALAT</h2>
    <p class="tengah">Periode {{ $periode }}</p>

    <table class="data">
        <thead>
            <tr>@foreach ($judul as $j)<th>{{ $j }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @forelse ($baris as $b)
            <tr>@foreach ($b as $sel)<td>{{ $sel }}</td>@endforeach</tr>
            @empty
            <tr>
                <td colspan="{{ count($judul) }}" class="tengah">Tidak ada data pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 12px">Dicetak {{ now()->translatedFormat('d F Y H:i') }} · {{ count($baris) }} data</p>
</body>

</html>