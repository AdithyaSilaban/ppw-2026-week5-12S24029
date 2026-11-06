<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus (Publik)
     * Mengambil koleksi katalog buku terpaginasi lengkap dengan Search, Filter, & Sorting (Bebas N+1)
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['judul', 'penulis', 'isbn', 'stok', 'tahun_terbit', 'created_at'];
        $sortBy       = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'judul';
        $sortDir      = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage      = min(max((int) $request->query('per_page', 5), 1), 50);

        // Kueri efisien bebas N+1 problem dengan with() dan withCount()
        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), fn ($q, $catId) => $q->where('kategori_id', $catId))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total_items'  => $paginator->total(),
                'total_pages'  => $paginator->lastPage(),
                'links'        => [
                    'first' => $paginator->url(1),
                    'prev'  => $paginator->previousPageUrl(),
                    'next'  => $paginator->nextPageUrl(),
                    'last'  => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/bukus/{id} (Publik)
     * Mengambil detail satu buku beserta objek kategori dan relasi riwayat peminjaman
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])->withCount('peminjamans')->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di katalog perpustakaan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus (Wajib Bearer Token)
     * Menambah buku baru (Mereturn 201 Created + Header Location)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_id'  => 'required|exists:kategoris,id',
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'judul'        => 'required|string|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'nullable|string|max:150',
            'tahun_terbit' => 'nullable|integer|min:1900|max:2099',
            'stok'         => 'required|integer|min:0|max:1000',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil ditambahkan ke katalog.'
        );
    }

    /**
     * PUT/PATCH /api/v1/bukus/{id} (Wajib Bearer Token)
     * Mendemonstrasikan perbedaan semantik PUT (Full Replacement) vs PATCH (Partial Update)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // Semantik PUT: Seluruh atribut representasi wajib dikirimkan secara utuh
            $validated = $request->validate([
                'kategori_id'  => 'required|exists:kategoris,id',
                'isbn'         => "required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul'        => 'required|string|max:255',
                'penulis'      => 'required|string|max:150',
                'penerbit'     => 'nullable|string|max:150',
                'tahun_terbit' => 'nullable|integer|min:1900|max:2099',
                'stok'         => 'required|integer|min:0|max:1000',
            ]);

            // Field opsional direset ke null jika tidak ada pada payload PUT
            $validated['penerbit']     = $request->input('penerbit', null);
            $validated['tahun_terbit'] = $request->input('tahun_terbit', null);
        } else {
            // Semantik PATCH: Hanya memvalidasi field yang disertakan pada body
            $validated = $request->validate([
                'kategori_id'  => 'sometimes|required|exists:kategoris,id',
                'isbn'         => "sometimes|required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul'        => 'sometimes|required|string|max:255',
                'penulis'      => 'sometimes|required|string|max:150',
                'penerbit'     => 'sometimes|nullable|string|max:150',
                'tahun_terbit' => 'sometimes|nullable|integer|min:1900|max:2099',
                'stok'         => 'sometimes|required|integer|min:0|max:1000',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT Full Replacement).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH Partial Update).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id} (Wajib Bearer Token)
     * Menghapus buku: Ditolak 409 Conflict jika masih dipinjam aktif, 204 No Content jika sukses
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::withCount(['peminjamans' => function ($q) {
            $q->where('status', 'dipinjam');
        }])->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        // Aturan Bisnis: Tolak 409 Conflict jika buku sedang memiliki peminjaman aktif
        if ($buku->peminjamans_count > 0) {
            return $this->problemResponse(
                title: 'Conflict Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena sedang dipinjam oleh {$buku->peminjamans_count} peminjaman aktif.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }
}