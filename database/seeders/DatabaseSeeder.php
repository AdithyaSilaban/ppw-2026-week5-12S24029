<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Buku;
use App\Models\User;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pustakawan Uji
        User::firstOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'     => 'Pustakawan Del',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Data Master Kategori (5 Kategori)
        $kat1 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-01'], ['nama_kategori' => 'Teknologi & Komputer']);
        $kat2 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-02'], ['nama_kategori' => 'Sains & Rekayasa']);
        $kat3 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-03'], ['nama_kategori' => 'Sastra & Fiksi']);
        $kat4 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-04'], ['nama_kategori' => 'Bisnis & Manajemen']);
        $kat5 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-05'], ['nama_kategori' => 'Sosial & Humaniora']);

        $kategoris = collect([$kat1, $kat2, $kat3, $kat4, $kat5]);

        // 3. Generate 20 Data Buku Dummy Acak
        Buku::factory(20)->recycle($kategoris)->create();

        // 4. Data Uji Khusus Transaksi Sirkulasi
        $habis = Buku::firstOrCreate(
            ['isbn' => '978-602-03-8881-0'],
            [
                'judul'        => 'Algoritma Pemrograman Tingkat Lanjut (Buku Habis)',
                'penulis'      => 'Informatika Del',
                'penerbit'     => 'Del Press',
                'tahun_terbit' => 2024,
                'kategori_id'  => $kat1->id,
                'stok'         => 0,
                'sinopsis'     => 'Buku sampel dengan stok 0 untuk pengujian rollback transaksi ACID.',
            ]
        );

        $clean = Buku::firstOrCreate(
            ['isbn' => '978-602-03-8881-1'],
            [
                'judul'        => 'Clean Architecture: A Craftsman\'s Guide to Software Structure',
                'penulis'      => 'Robert C. Martin',
                'penerbit'     => 'Prentice Hall',
                'tahun_terbit' => 2017,
                'kategori_id'  => $kat1->id,
                'stok'         => 1,
                'sinopsis'     => 'Panduan esensial membangun arsitektur perangkat lunak modular.',
            ]
        );

        // 5. Sampel Data Sirkulasi Peminjaman Konsisten
        Peminjaman::firstOrCreate(
            ['kode_pinjam' => 'PJ-SEED-0001'],
            [
                'buku_id'             => $habis->id,
                'nim_peminjam'        => '12S22001',
                'nama_peminjam'       => 'Budi Siregar',
                'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ]
        );

        Peminjaman::firstOrCreate(
            ['kode_pinjam' => 'PJ-SEED-0002'],
            [
                'buku_id'             => $clean->id,
                'nim_peminjam'        => '14S22002',
                'nama_peminjam'       => 'Rani Simanjuntak',
                'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
                'tanggal_kembali'     => now()->subDays(4)->toDateString(),
                'status'              => 'dikembalikan',
            ]
        );
    }
}