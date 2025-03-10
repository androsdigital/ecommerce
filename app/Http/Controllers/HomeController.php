<?php

namespace App\Http\Controllers;

use App\Models\StockItem;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $stockItems = StockItem::with('media')->get();

        return view('home', compact('stockItems'));
    }
}
