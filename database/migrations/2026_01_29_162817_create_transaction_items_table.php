<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();

            // 1. Hubungkan ke tabel Transaksi Utama (Induknya)
            // Kalau transaksi dihapus, item-nya juga ikut terhapus (cascade)
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');

            // 2. Hubungkan ke tabel Produk (Barang apa yang dibeli)
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // 3. Detail Pembelian
            $table->integer('quantity'); // Jumlah beli (misal: 2 pcs)
            $table->bigInteger('price'); // Harga per item saat dibeli

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};