<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __invoke(Category $category): View
    {
        $category->load('products.media');

        return view('category', compact('category'));
    }
}
