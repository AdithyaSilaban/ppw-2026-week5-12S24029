<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Wajib Bearer Token)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', $nim))
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar riwayat transaksi peminjaman berhasil diambil.',
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
     * POST /api/v1/peminjamans (Wajib Bearer Token)
     * Mencatat transaksi peminjaman baru dengan jaminan ACID + lockForUpdate()
     */
   /**
     * POST /api/v1/peminjamans (Wajib Bearer Token)
     * Mencatat transaksi peminjaman baru dengan jaminan ACID + lockForUpdate()
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id'       => 'required|integer|exists:bukus,id',
            'nim_peminjam'  => 'required|string|max:20',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari'   => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Kunci baris buku secara eksklusif (Pessimistic Locking)
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: 'Buku yang akan dipinjam tidak ditemukan.',
                    status: 404,
                    errorSlug: 'buku-not-found'
                );
            }

            // 2. Tolak 409 Conflict jika mahasiswa masih meminjam buku yang sama dan belum dikembalikan
            $hasActiveLoan = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $validated['nim_peminjam'])
                ->where('status', 'dipinjam')
                ->exists();

            if ($hasActiveLoan) {
                return $this->problemResponse(
                    title: 'Duplicate Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$validated['nim_peminjam']}' masih meminjam buku '{$buku->judul}' dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-loan-conflict'
                );
            }

            // 3. Tolak 422 Unprocessable Content jika stok habis
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Book Stock Exhausted',
                    detail: "Maaf, stok untuk buku '{$buku->judul}' sedang habis (0 tersisa).",
                    status: 422,
                    errorSlug: 'stock-exhausted'
                );
            }

            // 4. Kurangi stok (-1)
            $buku->decrement('stok');

            // 5. Hitung tanggal pinjam & tanggal jatuh tempo (default: 7 hari)
            $durasiHari         = (int) ($validated['durasi_hari'] ?? 7);
            $tanggalPinjam      = now();
            $tanggalJatuhTempo  = now()->addDays($durasiHari);

            // 6. Simpan via Direct Assignment
            $peminjaman = new Peminjaman();
            $peminjaman->kode_pinjam          = 'PJ' . date('ymd') . rand(100, 999);
            $peminjaman->buku_id              = $buku->id;
            $peminjaman->nim_peminjam         = strtoupper(trim($validated['nim_peminjam']));
            $peminjaman->nama_peminjam        = $validated['nama_peminjam'];
            $peminjaman->tanggal_pinjam       = $tanggalPinjam->toDateString();
            $peminjaman->tanggal_jatuh_tempo  = $tanggalJatuhTempo->toDateString();
            $peminjaman->tanggal_kembali      = null;
            $peminjaman->status               = 'dipinjam';
            $peminjaman->save();

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok buku saat ini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Wajib Bearer Token)
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'peminjaman-not-found'
                );
            }

            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict Book Already Returned',
                    detail: 'Buku pada transaksi ini sudah pernah dikembalikan sebelumnya.',
                    status: 409,
                    errorSlug: 'already-returned-conflict'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();
            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->update([
                'status'          => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: "Buku '{$buku?->judul}' berhasil dikembalikan. Stok buku bertambah menjadi: {$buku?->fresh()->stok}."
            );
        });
    }
}