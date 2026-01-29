<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index() {
        // Ambil pesanan milik user yang login
        return response()->json(Order::where('user_id', Auth::id())->latest()->get());
    }

    public function store(Request $request) {
        $request->validate([
            'product_name' => 'required',
            'total_price' => 'required',
            'address' => 'required',
        ]);

        $order = Order::create([
            'user_id' => Auth::id(),
            'product_name' => $request->product_name,
            'total_price' => $request->total_price,
            'address' => $request->address,
            'notes' => $request->notes,
            'status' => 'Pesanan dibuat'
        ]);

        return response()->json(['message' => 'Pesanan berhasil dibuat', 'data' => $order]);
    }
}