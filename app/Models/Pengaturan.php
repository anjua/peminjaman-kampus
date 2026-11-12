<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['kunci', 'nilai', 'tipe', 'grup', 'label', 'keterangan', 'urutan'];

    public static function ambil(string $kunci): mixed
    {
        $semua = Cache::rememberForever('pengaturan', fn () => static::query()
            ->get(['kunci', 'nilai', 'tipe'])
            ->keyBy('kunci')
            ->toArray());

        $p = $semua[$kunci] ?? null;
        abort_if(!$p, 500, "Pengaturan {$kunci} belum ada. Jalankan php artisan db:seed.");

        return match ($p['tipe']) {
            'integer' => (int) $p['nilai'],
            'boolean' => filter_var($p['nilai'], FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($p['nilai'], true),
            default => $p['nilai'],
        };
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('pengaturan'));
        static::deleted(fn () => Cache::forget('pengaturan'));
    }
}
