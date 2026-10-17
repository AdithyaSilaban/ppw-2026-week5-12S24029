<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    /**
     * Tampilkan katalog buku beserta 4 kartu agregasi statistik sirkulasi.
     */
    public function index(Request $request)
    {
        // 1. Eksekusi 4 Query Agregasi Statistik menggunakan DB::table()
        $stats = [
            'total_judul'     => DB::table('bukus')->count(),
            'total_stok'      => (int) DB::table('bukus')->sum('stok'),
            'sedang_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'dikembalikan'    => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        // 2. Query data buku dengan Eager Loading, withCount, dan klausa when()
        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->filled('kategori_id'), fn($q) => $q->where('kategori_id', $request->kategori_id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = $request->q;
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('judul', 'like', "%{$keyword}%")
                        ->orWhere('penulis', 'like', "%{$keyword}%")
                        ->orWhere('isbn', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('buku.index', compact('bukus', 'kategoris', 'stats'));
    }

    // Method lainnya (create, store, show, edit, update, destroy) tetap sama
}