<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAvailable = (int) $this->stok > 0;

        $links = [
            'self' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'GET',
            ],
            'update_full' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PUT',
            ],
            'update_partial' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PATCH',
            ],
            'delete' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'DELETE',
            ],
        ];

        // HATEOAS Dinamis: Link pinjam_buku HANYA muncul di dalam _links jika stok > 0
        if ($isAvailable) {
            $links['pinjam_buku'] = [
                'href'   => url('/api/v1/peminjamans'),
                'method' => 'POST',
            ];
        }

        return [
            'id'                  => $this->id,
            'kategori_id'         => (int) $this->kategori_id,
            'kategori'            => $this->whenLoaded('kategori', fn () => [
                'id'   => $this->kategori->id,
                'nama' => $this->kategori->nama ?? $this->kategori->nama_kategori,
            ]),
            'isbn'                => $this->isbn,
            'judul'               => $this->judul,
            'penulis'             => $this->penulis,
            'penerbit'            => $this->penerbit ?? null,
            'tahun_terbit'        => isset($this->tahun_terbit) ? (int) $this->tahun_terbit : null,
            'stok'                => (int) $this->stok,
            'status_ketersediaan' => $isAvailable ? 'AVAILABLE' : 'OUT_OF_STOCK',
            'total_peminjaman'    => $this->whenCounted('peminjamans', fn () => $this->peminjamans_count, 0),
            'riwayat_peminjaman'  => PeminjamanResource::collection($this->whenLoaded('peminjamans')),
            'updated_at'          => $this->updated_at?->toIso8601String(),
            '_links'              => $links,
        ];
    }
}