<?php

namespace App\Console\Commands;

use App\Models\Peran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TampilkanAkunDemo extends Command
{
    protected $signature = 'demo:akun';

    protected $description = 'Cetak tabel Markdown akun demo dari database/data/akun_demo.json untuk README';

    public function handle(): int
    {
        $akun = json_decode(File::get(database_path('data/akun_demo.json')), true);
        $this->line('| Peran | Email | Password | Untuk mencoba |');
        $this->line('| --- | --- | --- | --- |');
        foreach ($akun as $a) {
            $peran = Peran::where('kode', $a['peran'])->value('nama') ?? $a['peran'];
            $this->line("| {$peran} | {$a['email']} | {$a['password']} | {$a['untuk']} |");
        }

        return self::SUCCESS;
    }
}
