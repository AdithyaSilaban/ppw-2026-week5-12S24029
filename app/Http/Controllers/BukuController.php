<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    /**
     * Tampilkan katalog buku beserta 4 kartu analitik agregasi statistik.
     */
    public function index(Request $request)
    {
        // 1. Eksekusi Query Agregasi Statistik (Spesifikasi 3)
        $totalJudul = Buku::count();
        $totalStok = (int) Buku::sum('stok');
        $rataStok = round((float) Buku::avg('stok'), 1);
        $stokTertinggi = (int) (Buku::max('stok') ?? 0);

        // 2. Query data buku: Eager loading kategori & withCount riwayat peminjaman
        $query = Buku::with('kategori')->withCount('peminjamans');

        // Fitur filter kategori opsional
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Fitur pencarian judul/penulis/ISBN opsional
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('penulis', 'like', "%{$keyword}%")
                  ->orWhere('isbn', 'like', "%{$keyword}%");
            });
        }

        $bukus = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('buku.index', compact(
            'bukus',
            'kategoris',
            'totalJudul',
            'totalStok',
            'rataStok',
            'stokTertinggi'
        ));
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
            'judul'        => 'required|string|max:255',
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
     * Perbarui data buku.
     */
    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn,' . $buku->id,
            'judul'        => 'required|string|max:255',
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
        // Cegah penghapusan jika buku sedang dalam status dipinjam
        $adaPeminjamanAktif = $buku->peminjamans()->where('status', 'dipinjam')->exists();

        if ($adaPeminjamanAktif) {
            return back()->with('error', 'Buku tidak dapat dihapus karena masih ada eksemplar yang sedang dipinjam.');
        }

        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku telah berhasil dihapus dari katalog.');
    }
}