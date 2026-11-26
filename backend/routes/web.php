<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SessionController;
use App\Http\Middleware\ActiveAccount;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/equipment');
Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:10,1');
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth');
Route::middleware(['auth', ActiveAccount::class])->group(function () {
    Route::get('/equipment', [EquipmentController::class, 'index']);
    Route::get('/equipment/create', [EquipmentController::class, 'create']);
    Route::post('/equipment', [EquipmentController::class, 'store']);
    Route::get('/equipment/{id}', [EquipmentController::class, 'show'])->whereNumber('id');
    Route::get('/equipment/{id}/edit', [EquipmentController::class, 'edit'])->whereNumber('id');
    Route::patch('/equipment/{id}', [EquipmentController::class, 'update'])->whereNumber('id');

    Route::get('/qr', [InventoryController::class, 'lookup']);
    Route::redirect('/inventory', '/inventory-sessions');
    Route::get('/inventory-sessions', [InventoryController::class, 'index']);
    Route::post('/inventory-sessions', [InventoryController::class, 'store']);
    Route::get('/inventory-sessions/{id}', [InventoryController::class, 'show'])->whereNumber('id');
    Route::patch('/inventory-sessions/{id}/items/{itemId}', [InventoryController::class, 'updateItem'])->whereNumber(['id', 'itemId']);
    Route::post('/inventory-sessions/{id}/close', [InventoryController::class, 'close'])->whereNumber('id');
});
