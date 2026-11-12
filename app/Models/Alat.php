<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';

    protected $fillable = ['kode', 'nama', 'stok', 'kondisi', 'denda_per_jam', 'aktif'];

    protected $casts = ['aktif' => 'boolean', 'stok' => 'integer', 'denda_per_jam' => 'integer'];

    public function detail()
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id');
    }

    public function terpakaiPada($mulai, $selesai, ?int $kecualiPeminjamanId = null): int
    {
        return (int) $this->detail()
            ->whereHas('peminjaman', fn($q) => $q->memakaiAlatPada($mulai, $selesai)
                ->when($kecualiPeminjamanId, fn($q2) => $q2->where('id', '!=', $kecualiPeminjamanId)))
            ->sum('jumlah');
    }
}
