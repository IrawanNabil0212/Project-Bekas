<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    // Nama tabel harus sama persis dengan di database
    protected $table = 'transaction_items';

    // Kolom ini wajib ada agar bisa diisi oleh Controller
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price'
    ];

    // Relasi balik ke Produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}