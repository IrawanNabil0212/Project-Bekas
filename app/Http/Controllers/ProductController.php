<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // MENAMPILKAN SEMUA PRODUK (HOME)
    public function index()
    {
        $products = Product::latest()->get();
        
        $formatted = $products->map(function($item) {
            return [
                'id'          => $item->id,
                'name'        => $item->nama_barang,
                'category'    => $item->kategori,
                'price'       => $item->harga,
                'description' => $item->deskripsi,
                'location'    => $item->lokasi,
                'image'       => $item->gambar ? asset('storage/products/' . $item->gambar) : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    // --- TAMBAHAN BARU: MENAMPILKAN DETAIL 1 PRODUK ---
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        // Format data agar sama dengan frontend (Vue)
        $formatted = [
            'id'          => $product->id,
            'name'        => $product->nama_barang,
            'category'    => $product->kategori,
            'price'       => $product->harga,
            'description' => $product->deskripsi,
            'location'    => $product->lokasi,
            'image'       => $product->gambar ? asset('storage/products/' . $product->gambar) : null,
            'seller_id'   => $product->user_id,
        ];

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    // PROSES UPLOAD (JUAL)
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'price'       => 'required|numeric',
            'description' => 'required|string',
            'location'    => 'required|string',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Validasi Gagal', 
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imageName = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                if (!$file->isValid()) {
                    return response()->json(['success'=>false, 'message'=>'File gambar corrupt/rusak'], 422);
                }

                $imageName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('storage/products');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $imageName);
            }

            // 2. Simpan ke Database
            $product = Product::create([
                'user_id'     => Auth::id(),
                'nama_barang' => $request->name,
                'kategori'    => $request->category,
                'harga'       => $request->price,
                'deskripsi'   => $request->description,
                'lokasi'      => $request->location,
                'gambar'      => $imageName,
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Iklan berhasil ditayangkan!', 
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}