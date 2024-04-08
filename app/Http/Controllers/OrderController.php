<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function store(Product $product): RedirectResponse
    {
        $order = $product->orders()->create([
            'price' => $product->price,
        ]);

        return redirect()->route('order.show', $order);
    }

    public function show(Order $order): View
    {
        return view('order', compact('order'));
    }
}
