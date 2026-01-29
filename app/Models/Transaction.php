<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    // Kolom yang diizinkan untuk diisi (Mass Assignment)
    protected $fillable = [
        'user_id',
        'code',
        'total_price',
        'shipping_price', // Kolom baru (Ongkir)
        'status',
        'payment_method', // Kolom baru (Metode Bayar)
        'address',        // Kolom baru (Alamat)
        'notes',          
        'phone'           
    ];

    // Relasi ke User (Yang membeli)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Detail Item (Barang apa saja yang dibeli)
    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }
}