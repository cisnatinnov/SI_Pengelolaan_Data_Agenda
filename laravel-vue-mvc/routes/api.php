<?php

use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\SuratKegiatanController;
use App\Http\Controllers\SuratUndanganController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::apiResource('surat-kegiatan', SuratKegiatanController::class)->except(['store']);
    Route::apiResource('surat-undangan', SuratUndanganController::class);

    // All roles can read kegiatan; only staff can create/update/delete.
    Route::apiResource('kegiatan', KegiatanController::class)->only(['index', 'show']);
    Route::apiResource('kegiatan', KegiatanController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:staff');

    // Only admin can manage users.
    Route::apiResource('users', UserController::class)->middleware('role:admin');
});
