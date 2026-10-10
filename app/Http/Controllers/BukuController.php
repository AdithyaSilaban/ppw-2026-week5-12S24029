<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bukus = Buku::with('kategori')->latest()->paginate(10);
        return view('buku.index', compact('bukus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'judul'        => 'required|string|min:5|max:200',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku baru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Buku $buku)
    {
       $buku->load('kategori');
       return view('buku.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            // Pengecualian ID unik agar ISBN buku yang sedang diedit tidak dianggap duplikat
            'isbn'         => 'required|string|max:20|unique:bukus,isbn,' . $buku->id,
            'judul'        => 'required|string|min:5|max:200',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Data buku berhasil dihapus!');
    }
}
