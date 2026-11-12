<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('ruang_id')->nullable()->constrained('ruang')->restrictOnDelete();
            $table->timestamp('waktu_mulai');
            $table->timestamp('waktu_selesai');
            $table->unsignedInteger('jumlah_peserta')->default(1);
            $table->text('keperluan');
            $table->string('status', 20)->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamp('dikembalikan_pada')->nullable();
            $table->string('kondisi_kembali', 20)->nullable();
            $table->unsignedInteger('denda')->default(0);
            $table->boolean('denda_lunas')->default(true);
            $table->timestamp('pengingat_dikirim_pada')->nullable();
            $table->timestamp('terakhir_diingatkan_pada')->nullable();
            $table->timestamps();
            $table->index(['status', 'waktu_mulai']);
        });

        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        DB::statement('ALTER TABLE peminjaman ADD CONSTRAINT peminjaman_cek_waktu CHECK (waktu_selesai > waktu_mulai)');
        DB::statement("ALTER TABLE peminjaman ADD CONSTRAINT peminjaman_ruang_tidak_bentrok
            EXCLUDE USING gist (ruang_id WITH =, tsrange(waktu_mulai, waktu_selesai, '[)') WITH &&)
            WHERE (ruang_id IS NOT NULL AND status IN ('menunggu', 'disetujui'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
