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

    /**
     * Formulir tambah buku baru (Hanya Pustakawan).
     */
    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        return view('buku.create', compact('kategoris'));
    }

    /**
     * Simpan buku baru ke basis data.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'judul'        => 'required|string|min:5|max:200',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku baru berhasil ditambahkan ke katalog.');
    }

    /**
     * Tampilkan detail buku beserta riwayat sirkulasinya.
     */
    public function show(Buku $buku)
    {
        $buku->load(['kategori', 'peminjamans' => function ($query) {
            $query->latest();
        }]);

        return view('buku.show', compact('buku'));
    }

    /**
     * Formulir edit data buku.
     */
    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Perbarui data buku di basis data.
     */
    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn,' . $buku->id,
            'judul'        => 'required|string|min:5|max:200',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Informasi buku berhasil diperbarui.');
    }

    /**
     * Hapus data buku dari katalog.
     */
    public function destroy(Buku $buku)
    {
        // Cegah penghapusan jika buku sedang memiliki status dipinjam
        $adaPeminjamanAktif = $buku->peminjamans()->where('status', 'dipinjam')->exists();

        if ($adaPeminjamanAktif) {
            return back()->with('error', 'Buku tidak dapat dihapus karena masih ada eksemplar yang sedang dipinjam.');
        }

        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku telah berhasil dihapus dari katalog.');
    }
}