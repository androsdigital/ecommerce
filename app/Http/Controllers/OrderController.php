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
        return redirect()->route('order.show');
    }

    public function show(Order $order): View
    {
        return view('order', compact('order'));
    }
}
