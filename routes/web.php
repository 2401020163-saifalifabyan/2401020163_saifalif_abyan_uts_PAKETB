
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanAlatController;

// Menampilkan daftar peminjaman
Route::get('/peminjaman', [
    PeminjamanAlatController::class,
    'index'
])->name('peminjaman.index');

// Menambahkan peminjaman
Route::post('/peminjaman', [
    PeminjamanAlatController::class,
    'store'
])->name('peminjaman.store');

// Membuka form edit
Route::get('/peminjaman/{id}/edit', [
    PeminjamanAlatController::class,
    'edit'
])->whereNumber('id')->name('peminjaman.edit');

// Memperbarui data
Route::put('/peminjaman/{id}', [
    PeminjamanAlatController::class,
    'update'
])->whereNumber('id')->name('peminjaman.update');

// Menghapus data
Route::delete('/peminjaman/{id}', [
    PeminjamanAlatController::class,
    'destroy'
])->whereNumber('id')->name('peminjaman.destroy');
