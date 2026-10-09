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
        return [
            'isbn'         => fake()->unique()->numerify('978-602-####-##-#'),
            'judul'        => rtrim(fake()->sentence(mt_rand(3, 6)), '.'),
            'penulis'      => fake()->name(),
            'penerbit'     => fake()->company(),
            'tahun_terbit' => fake()->numberBetween(2015, 2025),
            // Mengambil ID kategori secara acak dari kategori yang sudah ada
            'kategori_id'  => Kategori::inRandomOrder()->value('id') ?? 1,
            'stok'         => fake()->numberBetween(1, 25),
            'sinopsis'     => fake()->paragraph(3),
        ];
    }
}