<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    /**
     * Nama model yang terikat dengan factory ini.
     */
    protected $model = Kategori::class;

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_kategori' => 'KAT-' . fake()->unique()->numerify('##'),
            'nama_kategori' => fake()->unique()->randomElement([
                'Teknologi & Komputer', 
                'Sains & Rekayasa', 
                'Sastra & Fiksi',
                'Bisnis & Manajemen', 
                'Sosial & Humaniora', 
                'Pendidikan & Pengajaran',
                'Agama & Filsafat',
                'Seni & Desain',
                'Kedokteran & Kesehatan',
                'Hukum & Tata Negara'
            ]),
        ];
    }
}