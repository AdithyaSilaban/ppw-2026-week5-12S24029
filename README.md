# SIPUS-Del — Sistem Informasi Perpustakaan Kampus Del
**Tugas Mandiri Minggu 5 — 12S3101 Pemrograman & Pengujian Web (PPW)**  
Institut Teknologi Del · Semester Ganjil 2026/2027

| Identitas | Keterangan |
| :--- | :--- |
| **Nama** | Adithya Philip Jona Putra Silaban |
| **NIM** | 12S24029 |
| **Program Studi** | Sarjana Sistem Informasi |
| **Dosen Pengampu** | Chandro Pardede, S.Kom., M.Sc. |
| **Repositori** | `ppw-2026-week5-12S24029` |

---

### 1. Deskripsi Singkat
SIPUS-Del adalah aplikasi web pengelolaan master data Kategori dan Katalog Buku perpustakaan kampus yang dibangun menggunakan Laravel 11.x dengan arsitektur Model-View-Controller (MVC) dan *server-side rendering* Blade. 

Aplikasi ini mendemonstrasikan:
* Arsitektur MVC dengan pemisahan tanggung jawab (*Separation of Concerns*).
* Skema basis data berversi (*Migrations*), *Factory*, dan *Seeder* dengan dataset realistis.
* Eloquent ORM: relasi One-to-Many, proteksi `$fillable`, dan pencegahan *N+1 Query Problem* via *Eager Loading*.
* Standar *Routing* RESTful (7 aksi *Resource Controller*) dengan *Implicit Route Model Binding*.
* Validasi formulir di sisi server (*server-side*), proteksi CSRF, sanitasi output (pencegahan XSS), serta integritas referensial.
* Antarmuka modular menggunakan Blade Component (`<x-layout>`), utilitas tema Bootstrap 5 *Del Purple* (`#4C1D95`), penanganan data kosong via `@forelse`, dan *alert dismissible*.

---

### 2. Spesifikasi Teknologi

| Komponen | Versi / Keterangan |
| :--- | :--- |
| **Bahasa Pemrograman** | PHP 8.2 / 8.3 |
| **Framework** | Laravel 11.x |
| **Basis Data** | SQLite (default), kompatibel dengan MySQL 8.0+ |
| **Dependency Manager** | Composer 2.6+ |
| **Frontend Styling** | Bootstrap 5.3.3 via CDN, Custom CSS Del Purple (`#4C1D95`) |
| **Tooling** | Artisan CLI, Git (Conventional Commits) |

---

### 3. Fitur Utama
* **Pengelolaan Kategori:**
  * Menampilkan daftar kategori lengkap dengan perhitungan agregat jumlah buku (*real-time* via `withCount`).
  * Tambah kategori baru dengan kode unik.
  * Halaman rincian koleksi per kategori (`show`).
  * Ubah nama dan kode kategori (dengan pengecualian validasi *unique* pada ID bersangkutan).
  * Hapus kategori dengan proteksi integritas: kategori yang masih memuat buku dilarang dihapus.
* **Pengelolaan Katalog Buku:**
  * Menampilkan katalog buku dengan relasi nama kategori dan status stok.
  * Tambah buku baru lengkap dengan nomor ISBN, penulis, penerbit, tahun, stok, dan sinopsis.
  * Halaman rincian spesifikasi buku (`show`).
  * Ubah data buku (dengan pengecualian validasi *unique* pada ISBN yang sama).
  * Hapus data buku dari katalog.
* **Keamanan & UX:**
  * Penanganan *flash message* terpusat pada layout master.
  * Paginasi 10 data per halaman dengan tampilan responsif (`table-responsive`).
  * Penanganan kondisi data kosong melalui direktif Blade `@forelse ... @empty`.

---

### 4. Skema Basis Data

#### Tabel `kategoris`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | Big Integer (PK) | Auto increment |
| `kode_kategori` | String(20) | Unique |
| `nama_kategori` | String(100) | Not null |
| `created_at`, `updated_at` | Timestamp | Pencatatan waktu |

#### Tabel `bukus`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | Big Integer (PK) | Auto increment |
| `isbn` | String(20) | Unique |
| `judul` | String(200) | Minimal 5 karakter |
| `penulis` | String(150) | Not null |
| `penerbit` | String(150) | Not null |
| `tahun_terbit` | Integer | Tahun valid (1900 s.d. tahun berjalan) |
| `kategori_id` | Big Integer (FK) | Relasi ke `kategoris.id` (`cascadeOnDelete`) |
| `stok` | Integer | Default 0, minimal 0 |
| `sinopsis` | Text | Nullable |
| `created_at`, `updated_at` | Timestamp | Pencatatan waktu |

**Relasi Eloquent:**
* `Kategori` $\rightarrow$ `hasMany(Buku::class)`
* `Buku` $\rightarrow$ `belongsTo(Kategori::class)`

**Mekanisme Integritas Hapus Kategori:**
1. **Lapisan Aplikasi:** `KategoriController@destroy` memeriksa keberadaan buku terkait (`$kategori->bukus()->exists()`). Jika masih ada, proses dibatalkan dan sistem mengembalikan pesan galat.
2. **Lapisan Basis Data:** *Foreign key* didefinisikan dengan aturan `cascadeOnDelete()` sebagai proteksi struktural cadangan.

---

### 5. Struktur MVC

| Lapisan | Lokasi Berkas | Tanggung Jawab |
| :--- | :--- | :--- |
| **Model** | `app/Models/Kategori.php`<br>`app/Models/Buku.php` | Definisi relasi Eloquent, mass-assignment (`$fillable`). |
| **View** | `resources/views/buku/*.blade.php`<br>`resources/views/kategori/*.blade.php` | Presentasi data, navigasi antarhalaman, dan formulir input. |
| **Controller** | `app/Http/Controllers/BukuController.php`<br>`app/Http/Controllers/KategoriController.php` | Menangani HTTP request, validasi input, koordinasi Model, dan *redirect* dengan pesan sesi. |
| **Layout** | `resources/views/components/layout.blade.php` | Kerangka dasar HTML, CDN Bootstrap, kelas utilitas Del, dan penanganan notifikasi global. |

---

### 6. Arsitektur RESTful Routing

Rute didaftarkan secara deklaratif di `routes/web.php`:
```php
Route::get('/', fn() => redirect()->route('buku.index'));
Route::resource('buku', BukuController::class);
Route::resource('kategori', KategoriController::class);
```

#### Pemetaan Rute Sumber Daya (*Resource Table*):

| Method HTTP | URI | Aksi Controller | Nama Rute | Deskripsi Fungsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/buku` | `index` | `buku.index` | Katalog koleksi buku (paginasi) |
| `GET` | `/buku/create` | `create` | `buku.create` | Formulir tambah buku |
| `POST` | `/buku` | `store` | `buku.store` | Menyimpan data buku baru |
| `GET` | `/buku/{buku}` | `show` | `buku.show` | Halaman rincian buku |
| `GET` | `/buku/{buku}/edit` | `edit` | `buku.edit` | Formulir ubah data buku |
| `PUT/PATCH` | `/buku/{buku}` | `update` | `buku.update` | Memperbarui data buku |
| `DELETE` | `/buku/{buku}` | `destroy` | `buku.destroy` | Menghapus buku dari katalog |

*(Pola metode dan URI yang sama berlaku penuh untuk modul `kategori`).* Seluruh parameter rute menggunakan *Implicit Route Model Binding*.

> **Catatan Struktur:** Modul `Mahasiswa` dari sesi praktikum terbimbing sebelumnya tetap dipertahankan berkas model dan migrasinya demi menjaga keutuhan riwayat lab, namun antarmuka navigasi utama telah dikhususkan sepenuhnya untuk alur SIPUS-Del.

---

### 7. Validasi & Keamanan

* **Validasi Buku:**
  * `isbn`: Wajib, unik pada tabel `bukus` (mengecualikan ID buku aktif saat update via `Rule::unique`).
  * `judul`: Wajib, minimal 5 karakter, maksimal 200 karakter.
  * `penulis`, `penerbit`: Wajib, maksimal 150 karakter.
  * `tahun_terbit`: Wajib, bilangan bulat, antara 1900 hingga tahun berjalan.
  * `kategori_id`: Wajib, harus valid terdaftar pada tabel `kategoris`.
  * `stok`: Wajib, bilangan bulat, nilai minimal 0.
  * `sinopsis`: Opsional (nullable), string.
* **Validasi Kategori:**
  * `kode_kategori`: Wajib, unik pada tabel `kategoris` (mengecualikan ID kategori aktif saat update).
  * `nama_kategori`: Wajib, string, maksimal 100 karakter.
* **Standar Keamanan:**
  * **Proteksi CSRF:** Seluruh formulir menyertakan token `@csrf` dan *method spoofing* (`@method('PUT')`, `@method('DELETE')`).
  * **Pencegahan Mass-Assignment:** Seluruh atribut model dipagari via properti `$fillable`.
  * **Pencegahan XSS:** Seluruh ekspresi dinamis di Blade di-escape menggunakan sintaks kurung kurawal ganda `{{ }}`.
  * **Pencegahan SQL Injection:** Seluruh manipulasi kueri mengandalkan *Parameter Binding* Eloquent ORM.

---

### 8. Instalasi & Pengujian Lokal

```bash
# 1. Klon repositori
git clone [https://github.com/AdithyaSilaban/ppw-2026-week5-12S24029.git](https://github.com/AdithyaSilaban/ppw-2026-week5-12S24029.git)
cd ppw-2026-week5-12S24029

# 2. Pasang dependensi PHP
composer install

# 3. Salin berkas lingkungan dan buat Application Key
cp .env.example .env
php artisan key:generate

# 4. Siapkan basis data SQLite
# Windows (PowerShell):
New-Item database/database.sqlite -ItemType File -Force
# Linux/macOS:
touch database/database.sqlite

# 5. Jalankan migrasi dan seeding data awal
php artisan migrate:fresh --seed

# 6. Jalankan server lokal
php artisan serve
```
Aplikasi dapat diakses langsung pada peramban melalui alamat `http://127.0.0.1:8000`.

---

### 9. Seeder & Data Uji (*Mock Data*)

Pengisian data awal dieksekusi secara otomatis melalui `DatabaseSeeder.php`:

| Kategori Data | Jumlah | Sumber & Karakteristik Data |
| :--- | :--- | :--- |
| **Kategori Master** | 5 Kategori | Dibuat via `firstOrCreate`: `KAT-01` s.d. `KAT-05` (Teknologi & Komputer, Sains & Rekayasa, Sastra & Fiksi, Bisnis & Manajemen, Sosial & Humaniora). |
| **Buku Dummy** | 20 Buku | Digenerate otomatis lewat `BukuFactory` menggunakan **25 judul buku literatur ilmiah berbahasa Indonesia**, nomor ISBN tervalidasi Faker, penerbit nasional, dan terdistribusi acak ke kategori master. |
| **Buku Referensi** | 1 Buku | *Clean Architecture: A Craftsman's Guide to Software Structure* karya Robert C. Martin (`KAT-01`). |

---

### 10. Audit Kueri SQL (Eager Loading vs N+1 Problem)

Untuk menjamin efisiensi performa basis data, pendengar kueri dipasang pada `app/Providers/AppServiceProvider.php` via `DB::listen` yang mencatat eksekusi SQL ke `storage/logs/laravel.log` dengan label `[SQL AUDIT]`.

#### Implementasi Kode:
```php
// BukuController@index (Mencegah N+1 saat memuat relasi kategori)
$bukus = Buku::with('kategori')->latest()->paginate(10);

// KategoriController@index (Menghitung agregat buku dalam 1 kueri)
$kategoris = Kategori::withCount('bukus')->latest()->paginate(10);
```

#### Cuplikan Log Eksekusi Halaman `/buku`:
```text
[SQL AUDIT] (0.42 ms) select count(*) as "aggregate" from "bukus"
[SQL AUDIT] (0.55 ms) select * from "bukus" order by "created_at" desc limit 10 offset 0
[SQL AUDIT] (0.39 ms) select * from "kategoris" where "kategoris"."id" in (1, 2, 4, 5)
```
**Analisis Hasil:**  
Halaman katalog hanya menjalankan **3 kueri SQL**: 1 kueri agregat untuk paginasi, 1 kueri untuk mengambil 10 buku, dan 1 kueri relasi kategori menggunakan klausul `WHERE IN (...)`. Tanpa *Eager Loading*, sistem akan mengeksekusi $1 + 1 + 10 = 12$ kueri terpisah (*N+1 Query Problem*).

---

### 11. Struktur Berkas Utama

```text
ppw-2026-week5-12S24029/
├── app/
│   ├── Http/Controllers/
│   │   ├── BukuController.php         # RESTful Controller Katalog Buku
│   │   └── KategoriController.php     # RESTful 
Controller Master Kategori
│   ├── Models/
│   │   ├── Buku.php                   # Model Buku, relasi belongsTo Kategori
│   │   └── Kategori.php               # Model Kategori, relasi hasMany Buku
│   └── Providers/
│       └── AppServiceProvider.php     # Registrasi DB::listen & Bootstrap Paginator
├── database/
│   ├── factories/
│   │   ├── BukuFactory.php            # Mock data 25 judul buku realistis
│   │   └── KategoriFactory.php        # Mock data kategori
│   ├── migrations/                    # Skema tabel berversi
│   └── seeders/
│       └── DatabaseSeeder.php         # Inisialisasi master KAT-01..05 & 20+ buku
├── resources/views/
│   ├── components/
│   │   └── layout.blade.php           # Master template Blade (<x-layout>)
│   ├── buku/
│   │   ├── index.blade.php            # Tabel katalog buku & tautan detail
│   │   ├── create.blade.php           # Form penambahan buku
│   │   ├── edit.blade.php             # Form pembaruan buku
│   │   └── show.blade.php             # Halaman rincian spesifikasi buku
│   └── kategori/
│       ├── index.blade.php            # Tabel master kategori & tautan detail
│       ├── create.blade.php           # Form penambahan kategori
│       ├── edit.blade.php             # Form pembaruan kategori
│       └── show.blade.php             # Halaman rincian koleksi per kategori
└── routes/
    └── web.php                        # Definisi rute RESTful Resource
```

---

### 12. Catatan Penutup & Integritas Repositori
* Berkas `.env` dan direktori dependensi `vendor/` diabaikan secara ketat dari pelacakan Git melalui konfigurasi `.gitignore`.
* Format pesan commit menerapkan standar *Conventional Commits* (`feat`, `fix`, `refactor`, `style`, `docs`) untuk menjaga keterbacaan riwayat repositori secara profesional.

---
© 2026 — **Adithya Philip Jona Putra Silaban** (12S24029) · Sarjana Sistem Informasi · Institut Teknologi Del