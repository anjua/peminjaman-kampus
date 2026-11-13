<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanPeminjamanExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private $query, private array $kolom) {}

    private static function katalog(): array
    {
        return [
            'no' => ['No', fn($p, $i) => $i + 1],
            'peminjam' => ['Peminjam', fn($p) => $p->pengguna->name],
            'nim_nip' => ['NIM/NIP', fn($p) => $p->pengguna->nim_nip],
            'peran' => ['Peran', fn($p) => $p->pengguna->peran?->nama],
            'ruang' => ['Ruang', fn($p) => $p->ruang?->nama ?? '-'],
            'alat' => ['Alat', fn($p) => $p->ringkasan_alat ?: '-'],
            'mulai' => ['Mulai', fn($p) => $p->waktu_mulai->format('d/m/Y H:i')],
            'selesai' => ['Selesai', fn($p) => $p->waktu_selesai->format('d/m/Y H:i')],
            'peserta' => ['Peserta', fn($p) => $p->jumlah_peserta],
            'keperluan' => ['Keperluan', fn($p) => $p->keperluan],
            'status' => ['Status', fn($p) => $p->statusRef->label],
            'penyetuju' => ['Diproses oleh', fn($p) => $p->penyetuju?->name ?? '-'],
            'dikembalikan' => ['Dikembalikan', fn($p) => $p->dikembalikan_pada?->format('d/m/Y H:i') ?? '-'],
            'denda' => ['Denda (Rp)', fn($p) => $p->denda],
        ];
    }

    public static function katalogJudul(): array
    {
        return array_map(fn($k) => $k[0], self::katalog());
    }

    public function headings(): array
    {
        return array_map(fn($k) => self::katalog()[$k][0], $this->kolom);
    }

    public function collection(): Collection
    {
        return $this->query->with('pengguna.peran', 'ruang', 'detail.alat', 'statusRef', 'penyetuju')
            ->orderBy('waktu_mulai')->get()->values()
            ->map(fn($p, $i) => array_map(fn($k) => (self::katalog()[$k][1])($p, $i), $this->kolom));
    }
}
