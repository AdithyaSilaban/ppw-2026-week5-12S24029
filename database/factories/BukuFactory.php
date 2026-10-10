<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    protected $model = Buku::class;

    public function definition(): array
    {
        $judulBuku = [
            'Pemrograman Web Modern dengan Laravel',
            'Dasar-Dasar Perancangan Basis Data Relasional',
            'Struktur Data dan Algoritma Lanjut',
            'Rekayasa Perangkat Lunak Berorientasi Objek',
            'Analisis Protokol Jaringan Komputer',
            'Konsep dan Arsitektur Sistem Operasi Modern',
            'Keamanan Siber dan Kriptografi Terapan',
            'Kecerdasan Buatan dan Sistem Pakar',
            'Pembelajaran Mesin untuk Analisis Data',
            'Metodologi Pengembangan Perangkat Lunak Agile',
            'Interaksi Manusia dan Komputer serta Desain UX',
            'Arsitektur Komputer dan Organisasi Sistem',
            'Etika Profesi dan Regulasi Teknologi Informasi',
            'Manajemen Proyek Teknologi Informasi Berstandar',
            'Cloud Computing dan Layanan Microservices',
            'Teori Bahasa dan Otomata dalam Komputasi',
            'Pengantar Sains Data Menggunakan Python',
            'Konsep Pemrograman Berorientasi Objek Java',
            'Desain Pola Arsitektur Perangkat Lunak',
            'Audit dan Tata Kelola Sistem Informasi',
            'Pengolahan Citra Digital dan Visi Komputer',
            'Sistem Informasi Geografis Berbasis Web',
            'Logika Komputasi dan Matematika Diskrit',
            'Pengujian dan Penjaminan Mutu Perangkat Lunak',
            'Analisis dan Desain Sistem Berorientasi Objek',
        ];

        return [
            'isbn' => fake()->unique()->numerify('978-602-##-####-#'),
            'judul' => fake()->unique()->randomElement($judulBuku),
            'penulis' => fake()->name(),
            'penerbit' => fake()->randomElement([
                'Informatika Bandung', 'Penerbit Andi Offset', 
                'Erlangga Digital', 'Gramedia Pustaka Utama', 'Salemba Infotek'
            ]),
            'tahun_terbit' => fake()->numberBetween(2018, 2025),
            'kategori_id' => Kategori::inRandomOrder()->first()?->id ?? Kategori::factory(),
            'stok' => fake()->numberBetween(1, 20),
            'sinopsis' => fake()->paragraph(3),
        ];
    }
}