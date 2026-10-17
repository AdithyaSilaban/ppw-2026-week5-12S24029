<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjamans = Peminjaman::with('buku')
            ->latest()
            ->paginate(10);

        return view('peminjaman.index', compact('peminjamans'));
    }

    public function create()
    {
        $bukus = Buku::orderBy('judul', 'asc')->get();
        return view('peminjaman.create', compact('bukus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id'             => 'required|exists:bukus,id',
            'nim_peminjam'        => 'required|string|max:15',
            'nama_peminjam'       => 'required|string|max:150',
            'tanggal_pinjam'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_pinjam',
        ]);

        $nim = strtoupper(trim($validated['nim_peminjam']));

        try {
            // Jalankan transaksi dengan toleransi percobaan ulang hingga 3 kali saat deadlock
            DB::transaction(function () use ($validated, $nim) {
                // 1. Kunci baris buku untuk mencegah race condition stok
                $buku = Buku::where('id', $validated['buku_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Validasi ketersediaan fisik stok buku
                if ($buku->stok <= 0) {
                    throw new RuntimeException("Stok buku '{$buku->judul}' habis. Peminjaman tidak dapat diproses.");
                }

                // 3. Validasi pencegahan peminjaman ganda oleh NIM yang sama
                $sudahPinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->exists();

                if ($sudahPinjam) {
                    throw new RuntimeException("Mahasiswa dengan NIM {$nim} masih meminjam buku ini dan belum mengembalikannya.");
                }

                // 4. Generate kode unik transaksi peminjaman
                $kodePinjam = 'PJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 5. Simpan transaksi peminjaman
                Peminjaman::create([
                    'kode_pinjam'         => $kodePinjam,
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => $nim,
                    'nama_peminjam'       => trim($validated['nama_peminjam']),
                    'tanggal_pinjam'      => $validated['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status'              => 'dipinjam',
                ]);

                // 6. Kurangi stok secara atomik
                $buku->decrement('stok');
            }, 3);

            return redirect()->route('peminjaman.index')
                ->with('success', 'Transaksi peminjaman buku berhasil dicatat.');
        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Peminjaman gagal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan basis data. Transaksi dibatalkan.');
        }
    }

    public function kembalikan($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Kunci baris transaksi peminjaman
                $peminjaman = Peminjaman::where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new RuntimeException('Buku ini telah berstatus dikembalikan sebelumnya.');
                }

                // 2. Kunci baris buku terkait
                $buku = Buku::where('id', $peminjaman->buku_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 3. Perbarui status dan catat tanggal riil pengembalian
                $peminjaman->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Pulihkan stok buku
                $buku->increment('stok');
            }, 3);

            return redirect()->route('peminjaman.index')
                ->with('success', 'Buku telah berhasil dikembalikan dan stok diperbarui.');
        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Pengembalian gagal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan basis data saat memproses pengembalian.');
        }
    }
}