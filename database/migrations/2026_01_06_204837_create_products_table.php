<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            
            // --- KOLOM PRODUK ---
            $table->string('nama_barang');
            $table->string('kategori'); // <--- TAMBAHKAN INI (Wajib)
            $table->text('deskripsi');  // Ganti string jadi text biar muat banyak
            $table->bigInteger('harga'); // Ganti integer jadi bigInteger (antisipasi harga jutaan)
            $table->string('lokasi')->nullable(); 
            $table->string('gambar')->nullable(); 
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};