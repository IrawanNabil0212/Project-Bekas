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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('product_name'); // Nama barang
        $table->decimal('total_price', 12, 2); // Harga total
        $table->string('status')->default('Pesanan dibuat'); // Status awal
        $table->text('address'); // Alamat pengiriman saat dipesan
        $table->string('payment_method')->default('COD');
        $table->text('notes')->nullable(); // Catatan tambahan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
