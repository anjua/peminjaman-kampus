<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use App\Models\TemplateNotifikasi;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifikasiTemplate extends Notification
{
    public function __construct(public TemplateNotifikasi $t, public Peminjaman $p) {}

    public function via(object $notifiable): array
    {
        return $this->t->kanal;
    }

    public static function nilaiPenanda(Peminjaman $p): array
    {
        return [
            '{nama_peminjam}' => $p->pengguna->name,
            '{ruang}' => $p->ruang?->nama ?? '',
            '{alat}' => $p->ringkasan_alat,
            '{waktu_mulai}' => $p->waktu_mulai->translatedFormat('d M Y H:i'),
            '{waktu_selesai}' => $p->waktu_selesai->translatedFormat('d M Y H:i'),
            '{status}' => $p->statusRef?->label ?? $p->status,
            '{catatan_admin}' => $p->catatan_admin ?? '-',
            '{denda}' => 'Rp' . number_format($p->denda, 0, ',', '.'),
        ];
    }

    public static function isiPenanda(string $teks, Peminjaman $p): string
    {
        return strtr($teks, self::nilaiPenanda($p));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => self::isiPenanda($this->t->judul, $this->p),
            'pesan' => self::isiPenanda($this->t->isi, $this->p),
            'ikon' => $this->t->ikon,
            'warna' => $this->t->warna,
            'url' => route('peminjaman.show', $this->p),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(self::isiPenanda($this->t->judul, $this->p))
            ->line(self::isiPenanda($this->t->isi, $this->p))
            ->action('Lihat peminjaman', route('peminjaman.show', $this->p));
    }
}
