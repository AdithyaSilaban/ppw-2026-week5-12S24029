<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan riwayat dan daftar transaksi peminjaman.
     */
    public function index()
    {
        $peminjamans = Peminjaman::with('buku')
            ->latest()
            ->paginate(10);

        return view('peminjaman.index', compact('peminjamans'));
    }

    /**
     * Formulir pencatatan peminjaman baru.
     */
    public function create()
    {
        $bukus = Buku::orderBy('judul', 'asc')->get();
        return view('peminjaman.create', compact('bukus'));
    }

    /**
     * Simpan transaksi peminjaman dengan transaksi ACID dan lockForUpdate().
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id'             => 'required|exists:bukus,id',
            'nim_peminjam'        => 'required|string|max:15',
            'nama_peminjam'       => 'required|string|max:150',
            'tanggal_pinjam'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_pinjam',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Kunci baris data buku untuk mencegah race condition (over-borrowing)
                $buku = Buku::where('id', $validated['buku_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Validasi ketersediaan stok buku
                if ($buku->stok <= 0) {
                    throw new \Exception("Stok buku '{$buku->judul}' habis. Peminjaman tidak dapat diproses.");
                }

                // Generate kode unik peminjaman (contoh: PJ-20261017-A1B2C)
                $kodePinjam = 'PJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 1. Catat entri transaksi sirkulasi
                Peminjaman::create([
                    'kode_pinjam'         => $kodePinjam,
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => $validated['nim_peminjam'],
                    'nama_peminjam'       => $validated['nama_peminjam'],
                    'tanggal_pinjam'      => $validated['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status'              => 'dipinjam',
                ]);

                // 2. Kurangi stok buku secara atomik
                $buku->decrement('stok');
            });

            return redirect()->route('peminjaman.index')
                ->with('success', 'Transaksi peminjaman buku berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Proses pengembalian buku dengan transaksi ACID dan increment stok.
     */
    public function kembalikan($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // Kunci baris peminjaman
                $peminjaman = Peminjaman::where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new \Exception('Buku ini telah berstatus dikembalikan sebelumnya.');
                }

                // Kunci baris buku terkait
                $buku = Buku::where('id', $peminjaman->buku_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 1. Perbarui status dan catat tanggal pengembalian
                $peminjaman->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 2. Pulihkan stok buku
                $buku->increment('stok');
            });

            return redirect()->route('peminjaman.index')
                ->with('success', 'Buku telah berhasil dikembalikan dan stok diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}