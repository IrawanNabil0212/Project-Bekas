<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // 1. Relasi ke User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // 2. Data Pengiriman (Wajib ada agar tidak error di Controller)
            $table->string('code')->unique(); // Kode Transaksi (TRX-...)
            $table->text('address');          // Alamat lengkap
            $table->string('phone');          // Nomor HP aktif
            
            // 3. Data Keuangan (BigInteger aman untuk Rupiah)
            $table->bigInteger('total_price');    // Total Harga Barang
            $table->bigInteger('shipping_price'); // Biaya Ongkir
            
            // 4. Data Status & Pembayaran
            $table->string('payment_method'); // Contoh: COD / Transfer
            $table->string('status')->default('PENDING'); // PENDING, SUCCESS, FAILED
            $table->text('notes')->nullable(); // Catatan (Boleh kosong)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};