
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_alat', function (Blueprint $table) {

            $table->id();

            $table->char('kode_peminjaman', 10)->unique();

            $table->string('nama_peminjam', 100);

            $table->string('nomor_identitas', 30)->unique();

            $table->unsignedTinyInteger('jumlah_alat');

            $table->foreignId('alat_id')
                ->constrained('alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();

        });

        DB::statement(
            'ALTER TABLE peminjaman_alat
             ADD CONSTRAINT chk_jumlah_alat
             CHECK (jumlah_alat BETWEEN 1 AND 10)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_alat');
    }
};
