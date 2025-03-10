<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class StockItemController extends Controller
{
    public function __invoke(Product $product): View
    {
        return view('product', compact('product'));
    }
}
