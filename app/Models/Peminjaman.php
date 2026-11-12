<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    public const AKTIF = ['menunggu', 'disetujui'];

    protected $fillable = [
        'pengguna_id',
        'ruang_id',
        'waktu_mulai',
        'waktu_selesai',
        'jumlah_peserta',
        'keperluan',
        'status',
        'catatan_admin',
        'disetujui_oleh',
        'disetujui_pada',
        'dikembalikan_pada',
        'kondisi_kembali',
        'denda',
        'denda_lunas',
        'pengingat_dikirim_pada',
        'terakhir_diingatkan_pada',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'disetujui_pada' => 'datetime',
        'dikembalikan_pada' => 'datetime',
        'pengingat_dikirim_pada' => 'datetime',
        'terakhir_diingatkan_pada' => 'datetime',
        'denda_lunas' => 'boolean',
        'denda' => 'integer',
        'jumlah_peserta' => 'integer',
    ];

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'ruang_id');
    }

    public function detail()
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_id');
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function statusRef()
    {
        return $this->belongsTo(StatusPeminjaman::class, 'status', 'kode');
    }

    public function scopeBeririsan($q, $mulai, $selesai)
    {
        return $q->whereIn('status', self::AKTIF)
            ->where('waktu_mulai', '<', $selesai)
            ->where('waktu_selesai', '>', $mulai);
    }

    public function scopeMemakaiAlatPada($q, $mulai, $selesai)
    {
        return $q->where(fn($w) => $w->beririsan($mulai, $selesai))
            ->orWhere(fn($w) => $w->where('status', 'disetujui')
                ->whereNull('dikembalikan_pada')
                ->where('waktu_selesai', '<', now())
                ->whereHas('detail'));
    }

    public function getPunyaAlatAttribute(): bool
    {
        return $this->detail->isNotEmpty();
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->status === 'disetujui' && $this->punya_alat
            && !$this->dikembalikan_pada && $this->waktu_selesai->isPast();
    }

    public function getRingkasanAlatAttribute(): string
    {
        return $this->detail->map(fn($d) => "{$d->jumlah} {$d->alat->nama}")->join(', ');
    }
}
