<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    // Kunci nama tabel ke 'peminjamans' agar Laravel tidak mencari 'peminjamen'
    protected $table = 'peminjamans';

    // Izinkan seluruh kolom disimpan tanpa terblokir mass-assignment
    protected $guarded = [];

    /**
     * Relasi ke model Buku
     */
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }
}