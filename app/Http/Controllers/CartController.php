<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class CartController extends Controller
{
    // ==========================================
    // 1. TAMBAH KE KERANJANG (POST)
    // ==========================================
    public function addToCart(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'quantity'   => 'integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Invalid data', 'errors' => $validator->errors()], 400);
            }

            // Cek barang lama
            $existingCart = Cart::where('user_id', $user->id)
                                ->where('product_id', $request->product_id)
                                ->first();

            if ($existingCart) {
                $existingCart->quantity += $request->input('quantity', 1);
                $existingCart->save();
            } else {
                Cart::create([
                    'user_id'    => $user->id,
                    'product_id' => $request->product_id,
                    'quantity'   => $request->input('quantity', 1)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil masuk keranjang',
            ], 200); 

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 2. LIHAT ISI KERANJANG (GET)
    // ==========================================
    public function showCart()
    {
        try {
            $user = Auth::user();
            
            // Ambil keranjang + data produknya
            $cartItems = Cart::where('user_id', $user->id)
                             ->with('product') 
                             ->get();

            // MAPPING DATA (PERBAIKAN NAMA KOLOM DISINI)
            $mappedData = $cartItems->map(function($item) {
                $product = $item->product; 
                
                // Pastikan ambil dari 'nama_barang' dan 'harga' sesuai database
                $nama = $product ? $product->nama_barang : 'Produk Tidak Ditemukan';
                $harga = $product ? $product->harga : 0;
                $gambar = $product ? $product->gambar : null;

                return [
                    // Data Asli Cart
                    'id'            => $item->id,
                    'user_id'       => $item->user_id,
                    'product_id'    => $item->product_id,
                    'quantity'      => $item->quantity,
                    
                    // Data Flatten (Agar mudah dibaca Android)
                    'product_name'  => $nama,
                    'product_price' => $harga,
                    'product_image' => $gambar, 
                    'subtotal'      => $harga * $item->quantity,
                    
                    // Kirim objek product lengkap (opsional, untuk jaga-jaga)
                    'product'       => $product 
                ];
            });

            // Hitung Total Bayar Semua
            $totalBayar = $mappedData->sum('subtotal');

            return response()->json([
                'success' => true,
                'message' => 'List keranjang berhasil diambil',
                'data'    => $mappedData, 
                'total_payment' => $totalBayar
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success'=>false, 'message'=>'Gagal ambil keranjang', 'error'=>$e->getMessage()], 500);
        }
    }
}