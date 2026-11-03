<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Memasukkan data master alat
        DB::table('alat')->insert([
            [
                'id' => 1,
                'nama_alat' => 'Kamera',
            ],
            [
                'id' => 2,
                'nama_alat' => 'Proyektor',
            ],
        ]);

        // Memasukkan data awal peminjaman
        DB::table('peminjaman_alat')->insert([
            [
                'kode_peminjaman' => 'PM00000001',
                'nama_peminjam' => 'Rina Kurnia',
                'nomor_identitas' => 'MHS-2201001',
                'jumlah_alat' => 2,
                'alat_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_peminjaman' => 'PM00000002',
                'nama_peminjam' => 'Dedi Pranata',
                'nomor_identitas' => 'MHS-2201002',
                'jumlah_alat' => 1,
                'alat_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_peminjaman' => 'PM00000003',
                'nama_peminjam' => 'Mira Ananda',
                'nomor_identitas' => 'MHS-2201003',
                'jumlah_alat' => 3,
                'alat_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
