<?php

namespace App\Http\Controllers;

use App\Models\StockItem;
use Illuminate\Contracts\View\View;

class StockItemController extends Controller
{
    public function __invoke(StockItem $stockItem): View
    {
        return view('stockItem', compact('stockItem'));
    }
}
