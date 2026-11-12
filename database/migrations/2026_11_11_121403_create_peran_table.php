<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peran', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('hak_akses', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->string('grup', 50);
            $table->timestamps();
        });

        Schema::create('peran_hak_akses', function (Blueprint $table) {
            $table->foreignId('peran_id')->constrained('peran')->cascadeOnDelete();
            $table->foreignId('hak_akses_id')->constrained('hak_akses')->cascadeOnDelete();
            $table->primary(['peran_id', 'hak_akses_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peran_hak_akses');
        Schema::dropIfExists('hak_akses');
        Schema::dropIfExists('peran');
    }
};
