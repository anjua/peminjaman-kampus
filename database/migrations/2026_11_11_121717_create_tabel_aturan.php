<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 100)->unique();
            $table->text('nilai')->nullable();
            $table->string('tipe', 20);      
            $table->string('grup', 30);      
            $table->string('label', 100);
            $table->text('keterangan')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('jam_operasional', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('hari')->unique(); 
            $table->string('nama_hari', 10);
            $table->time('buka');
            $table->time('tutup');
            $table->boolean('aktif')->default(true);
        });

        Schema::create('hari_libur', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan', 150);
        });

        Schema::create('status_peminjaman', function (Blueprint $table) {
            $table->string('kode', 20)->primary();
            $table->string('label', 50);
            $table->string('warna', 20);     
            $table->unsignedSmallInteger('urutan')->default(0);
        });

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->foreign('status')->references('kode')->on('status_peminjaman')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', fn(Blueprint $table) => $table->dropForeign(['status']));
        Schema::dropIfExists('status_peminjaman');
        Schema::dropIfExists('hari_libur');
        Schema::dropIfExists('jam_operasional');
        Schema::dropIfExists('pengaturan');
    }
};
