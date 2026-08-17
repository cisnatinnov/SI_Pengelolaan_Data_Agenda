<?php

use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PengingatController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Staff can only view disposisi; only Asisten Daerah updates it (serahkan/tolak). No role deletes it.
    Route::apiResource('disposisi', DisposisiController::class)->except(['store', 'destroy']);
    Route::apiResource('surat', SuratController::class);

    // All roles can read kegiatan; only staff can create/update/delete.
    Route::apiResource('kegiatan', KegiatanController::class)->only(['index', 'show']);
    Route::apiResource('kegiatan', KegiatanController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:staff');

    // Only OPD can confirm kegiatan attendance.
    Route::post('kegiatan/{kegiatan}/kehadiran', [KegiatanController::class, 'konfirmasiKehadiran'])
        ->middleware('role:opd');

    // Only admin can manage users.
    Route::apiResource('users', UserController::class)->middleware('role:admin');
    Route::get('roles', [RoleController::class, 'index'])->middleware('role:admin');

    // All roles except admin can manage their own pengingat.
    Route::middleware('not-role:admin')->group(function () {
        Route::get('pengingat/notifications', [PengingatController::class, 'notifications']);
        Route::post('pengingat/{pengingat}/read', [PengingatController::class, 'markAsRead']);
        Route::post('pengingat/read-all', [PengingatController::class, 'markAllAsRead']);
        Route::apiResource('pengingat', PengingatController::class);
    });
});
