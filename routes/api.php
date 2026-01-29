<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==============================
// 1. ROUTE PUBLIC (Bisa diakses siapa saja)
// ==============================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Produk
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ==============================
// 2. ROUTE PRIVATE (Harus Login / Punya Token)
// ==============================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- USER & AUTH ---
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // [PERBAIKAN] Menggunakan PUT karena frontend mengirim request PUT
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // --- PRODUK (Admin/Seller) ---
    Route::post('/products', [ProductController::class, 'store']);

    // --- KERANJANG (CART) ---
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'showCart']);

    // --- TRANSAKSI ---
    // 1. Buat Pesanan Baru (Checkout)
    Route::post('/transactions', [TransactionController::class, 'createTransaction']);
    
    // 2. Lihat Riwayat Pesanan
    Route::get('/transactions', [TransactionController::class, 'index']);
});