<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;

Route::get('/', fn() => redirect()->route('buku.index'));
Route::resource('buku', BukuController::class);
Route::resource('kategori', KategoriController::class);