<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // =================================================
    // 1. FUNGSI MELIHAT RIWAYAT TRANSAKSI
    // =================================================
    public function index()
    {
        $user = Auth::user();

        $transactions = Transaction::with(['items.product'])
                                   ->where('user_id', $user->id)
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Transaksi',
            'data'    => $transactions
        ], 200);
    }

    // =================================================
    // 2. FUNGSI MEMBUAT TRANSAKSI BARU (CHECKOUT)
    // =================================================
    public function createTransaction(Request $request)
    {
        // 1. Cek User Login
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // 2. Validasi Input
        $validator = Validator::make($request->all(), [
            'total_price'    => 'required|numeric',
            'shipping_price' => 'required|numeric',
            'address'        => 'required|string',
            'phone'          => 'required|string',
            
            // Validasi Array Item
            'items'              => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Validasi Gagal', 
                'errors'  => $validator->errors()
            ], 400);
        }

        // 3. Mulai Transaksi Database
        DB::beginTransaction();

        try {
            // A. Simpan Data Header Transaksi
            $trx = new Transaction();
            $trx->user_id        = $user->id;
            $trx->code           = 'TRX-' . time() . rand(100, 999);
            $trx->total_price    = $request->total_price;
            $trx->shipping_price = $request->shipping_price;
            $trx->address        = $request->address;
            $trx->phone          = $request->phone;
            $trx->notes          = $request->notes ?? '-';
            
            $trx->status         = $request->status ?? 'PENDING'; 
            $trx->payment_method = $request->payment_method ?? 'COD'; 

            $trx->save();
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price'          => $product->harga 
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil Dibuat',
                'data'    => $trx
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan transaksi', 
                'error'   => $e->getMessage() 
            ], 500);
        }
    }
}