<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramStudi;
use App\Models\Mahasiswa;
use App\Models\Kategori;
use App\Models\Buku;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Praktikum Sebelumnya (SIM-Del)
        $prodiSI = ProgramStudi::firstOrCreate(['kode' => '12S'], ['nama' => 'Sistem Informasi', 'jenjang' => 'S1']);
        $prodiIF = ProgramStudi::firstOrCreate(['kode' => '14S'], ['nama' => 'Informatika', 'jenjang' => 'S1']);

        Mahasiswa::firstOrCreate(['nim' => '12S22001'], ['nama' => 'Budi Siregar', 'email' => 'budi@del.ac.id', 'program_studi_id' => $prodiSI->id, 'ipk' => 3.75]);
        Mahasiswa::firstOrCreate(['nim' => '14S22002'], ['nama' => 'Rani Simanjuntak', 'email' => 'rani@del.ac.id', 'program_studi_id' => $prodiIF->id, 'ipk' => 3.85]);

        // 2. Data Tugas Mandiri (SIPUS-Del: 5 Kategori)
        $kat1 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-01'], ['nama_kategori' => 'Teknologi & Komputer']);
        $kat2 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-02'], ['nama_kategori' => 'Sains & Rekayasa']);
        $kat3 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-03'], ['nama_kategori' => 'Sastra & Fiksi']);
        $kat4 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-04'], ['nama_kategori' => 'Bisnis & Manajemen']);
        $kat5 = Kategori::firstOrCreate(['kode_kategori' => 'KAT-05'], ['nama_kategori' => 'Sosial & Humaniora']);

        // Buat 20 data buku dummy otomatis menggunakan BukuFactory
        Buku::factory(20)->create();

        // Data Buku
        Buku::firstOrCreate(
            ['isbn' => '978-602-03-8881-1'],
            [
                'judul'        => 'Clean Architecture: A Craftsman\'s Guide to Software Structure',
                'penulis'      => 'Robert C. Martin',
                'penerbit'     => 'Prentice Hall',
                'tahun_terbit' => 2017,
                'kategori_id'  => $kat1->id,
                'stok'         => 7,
                'sinopsis'     => 'Panduan esensial membangun arsitektur perangkat lunak yang kokoh, teruji, dan mudah dipelihara.',
            ]
        );

        Buku::firstOrCreate(
            ['isbn' => '978-602-03-8881-2'],
            [
                'judul'        => 'Database System Concepts (7th Edition)',
                'penulis'      => 'Abraham Silberschatz',
                'penerbit'     => 'McGraw-Hill',
                'tahun_terbit' => 2019,
                'kategori_id'  => $kat1->id,
                'stok'         => 4,
                'sinopsis'     => 'Konsep fundamental sistem basis data relasional, pemrosesan transaksi, dan optimasi kueri.',
            ]
        );
    }
}