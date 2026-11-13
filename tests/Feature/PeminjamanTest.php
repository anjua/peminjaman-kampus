<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\HariLibur;
use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\Ruang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true; 

    private User $mhs;
    private Ruang $ruang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mhs = User::factory()->peran('peminjam')->create();
        $this->ruang = Ruang::where('aktif', true)->orderByDesc('kapasitas')->first();
    }

    private function hariKerja(int $mulaiDari = 3): Carbon
    {
        $tgl = now()->addDays($mulaiDari)->setTime(9, 0);
        while ($tgl->isSunday() || HariLibur::whereDate('tanggal', $tgl)->exists()) {
            $tgl->addDay();
        }

        return $tgl;
    }

    private function ajukan(User $user, Carbon $mulai, int $jam = 2, array $tambahan = [])
    {
        return $this->actingAs($user)->postJson('/peminjaman', array_merge([
            'ruang_id' => $this->ruang->id,
            'waktu_mulai' => $mulai->format('Y-m-d H:i'),
            'waktu_selesai' => $mulai->copy()->addHours($jam)->format('Y-m-d H:i'),
            'jumlah_peserta' => 10,
            'keperluan' => 'Rapat himpunan',
        ], $tambahan));
    }

    public function test_pengajuan_valid_berhasil(): void
    {
        $this->ajukan($this->mhs, $this->hariKerja())->assertCreated();
        $this->assertDatabaseHas('peminjaman', ['pengguna_id' => $this->mhs->id, 'status' => 'menunggu']);
    }

    public function test_jadwal_bentrok_ditolak(): void
    {
        $mulai = $this->hariKerja();
        $this->ajukan($this->mhs, $mulai)->assertCreated();

        $lain = User::factory()->peran('peminjam')->create();
        $this->ajukan($lain, $mulai->copy()->addHour())->assertStatus(422);
    }

    public function test_jadwal_berurutan_boleh(): void
    {
        $mulai = $this->hariKerja();
        $this->ajukan($this->mhs, $mulai)->assertCreated();

        $lain = User::factory()->peran('peminjam')->create();
        $this->ajukan($lain, $mulai->copy()->addHours(2))->assertCreated();
    }

    public function test_slot_bisa_dipakai_lagi_setelah_ditolak_atau_dibatalkan(): void
    {
        $mulai = $this->hariKerja();
        $this->ajukan($this->mhs, $mulai)->assertCreated();
        Peminjaman::latest('id')->first()->update(['status' => 'ditolak']);

        $lain = User::factory()->peran('peminjam')->create();
        $this->ajukan($lain, $mulai)->assertCreated();
        Peminjaman::latest('id')->first()->update(['status' => 'dibatalkan']);

        $this->ajukan(User::factory()->peran('peminjam')->create(), $mulai)->assertCreated();
    }

    public function test_stok_alat_tidak_boleh_terlampaui(): void
    {
        $alat = Alat::where('aktif', true)->first();
        $alat->update(['stok' => 2]);
        $mulai = $this->hariKerja();

        $this->ajukan($this->mhs, $mulai, 2, ['ruang_id' => null, 'detail' => [['alat_id' => $alat->id, 'jumlah' => 2]]])->assertCreated();

        $lain = User::factory()->peran('peminjam')->create();
        $this->ajukan($lain, $mulai, 2, ['ruang_id' => null, 'detail' => [['alat_id' => $alat->id, 'jumlah' => 1]]])
            ->assertStatus(422);
    }

    public function test_hari_minggu_ditolak_sesuai_jam_operasional(): void
    {
        $minggu = now()->addDays(3)->next(Carbon::SUNDAY)->setTime(9, 0);
        $this->ajukan($this->mhs, $minggu)->assertStatus(422)->assertJsonValidationErrors('waktu_mulai');
    }

    public function test_batas_pengajuan_aktif_dibaca_dari_pengaturan(): void
    {
        Pengaturan::where('kunci', 'peminjaman.maks_aktif')->first()->update(['nilai' => '1']);
        $mulai = $this->hariKerja();

        $this->ajukan($this->mhs, $mulai)->assertCreated();
        $this->ajukan($this->mhs, $mulai->copy()->addHours(3))->assertStatus(422)->assertJsonValidationErrors('ruang_id');
    }

    public function test_peminjam_tidak_bisa_membuka_halaman_admin(): void
    {
        $this->actingAs($this->mhs)->get('/admin/ruang')->assertForbidden();
        $this->actingAs($this->mhs)->get('/admin/pengaturan')->assertForbidden();
    }

    public function test_admin_bisa_membuka_halaman_admin(): void
    {
        $admin = User::factory()->peran('admin')->create();
        $this->actingAs($admin)->get('/admin/ruang')->assertOk();
        $this->actingAs($admin)->get('/admin/pengaturan')->assertOk();
    }

    public function test_peminjam_hanya_melihat_peminjaman_sendiri(): void
    {
        $milikLain = Peminjaman::factory()->create(['ruang_id' => $this->ruang->id]);
        $this->actingAs($this->mhs)->get("/peminjaman/{$milikLain->id}")->assertForbidden();

        $json = $this->actingAs($this->mhs)->getJson('/peminjaman')->assertOk()->json('data');
        $this->assertEmpty(collect($json)->where('id', $milikLain->id));
    }

    public function test_petugas_menyetujui_dan_peminjam_dapat_notifikasi(): void
    {
        $this->ajukan($this->mhs, $this->hariKerja())->assertCreated();
        $p = Peminjaman::latest('id')->first();
        $petugas = User::factory()->peran('petugas')->create();

        $this->actingAs($petugas)->patchJson("/admin/persetujuan/{$p->id}/setujui")->assertOk();
        $this->assertSame('disetujui', $p->fresh()->status);
        $this->assertSame(1, $this->mhs->notifications()->count());
    }

    public function test_template_dimatikan_tidak_mengirim_notifikasi(): void
    {
        \App\Models\TemplateNotifikasi::where('kode', 'disetujui')->update(['aktif' => false]);
        $this->ajukan($this->mhs, $this->hariKerja())->assertCreated();
        $p = Peminjaman::latest('id')->first();

        $this->actingAs(User::factory()->peran('petugas')->create())->patchJson("/admin/persetujuan/{$p->id}/setujui")->assertOk();
        $this->assertSame(0, $this->mhs->notifications()->count());
    }

    public function test_denda_memakai_tarif_alat_jika_ada(): void
    {
        $alat = Alat::where('aktif', true)->first();
        $alat->update(['denda_per_jam' => 10000]);
        $p = Peminjaman::factory()->create([
            'pengguna_id' => $this->mhs->id,
            'status' => 'disetujui',
            'waktu_mulai' => now()->subHours(6),
            'waktu_selesai' => now()->subHours(3),
        ]);
        $p->detail()->create(['alat_id' => $alat->id, 'jumlah' => 2]);

        $denda = app(\App\Services\DendaService::class)->hitung($p->fresh('detail.alat'), now());
        $this->assertSame(60000, $denda);
    }
}
