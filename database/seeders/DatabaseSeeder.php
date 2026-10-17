<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Buku;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pustakawan Uji (Sesuai Spesifikasi Praktikum)
        User::firstOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'     => 'Pustakawan Del',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Data Master Kategori
        $kat1 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-01'], ['nama_kategori' => 'Teknologi & Komputer']);
        $kat2 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-02'], ['nama_kategori' => 'Sains & Rekayasa']);
        $kat3 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-03'], ['nama_kategori' => 'Sastra & Fiksi']);
        $kat4 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-04'], ['nama_kategori' => 'Bisnis & Manajemen']);
        $kat5 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-05'], ['nama_kategori' => 'Sosial & Humaniora']);

        $kategoris = collect([$kat1, $kat2, $kat3, $kat4, $kat5]);

        // 3. Generate 20 data buku realistis
        Buku::factory(20)->recycle($kategoris)->create();

        // 4. Data Uji Khusus Transaksi Sirkulasi (Stok = 0 dan Stok = 1)
        Buku::firstOrCreate(
            ['isbn' => '978-602-03-8881-0'],
            [
                'judul'        => 'Algoritma Pemrograman Tingkat Lanjut (Buku Habis)',
                'penulis'      => 'Informatika Del',
                'penerbit'     => 'Del Press',
                'tahun_terbit' => 2024,
                'kategori_id'  => $kat1->id,
                'stok'         => 0, // Uji penolakan transaksi ketika stok habis
                'sinopsis'     => 'Buku sampel dengan stok 0 untuk pengujian rollback transaksi ACID.',
            ]
        );

        Buku::firstOrCreate(
            ['isbn' => '978-602-03-8881-1'],
            [
                'judul'        => 'Clean Architecture: A Craftsman\'s Guide to Software Structure',
                'penulis'      => 'Robert C. Martin',
                'penerbit'     => 'Prentice Hall',
                'tahun_terbit' => 2017,
                'kategori_id'  => $kat1->id,
                'stok'         => 1, // Uji transaksi sampai stok menjadi 0
                'sinopsis'     => 'Panduan esensial membangun arsitektur perangkat lunak modular.',
            ]
        );
    }
}