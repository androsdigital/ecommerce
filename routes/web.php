<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'about')->name('about');
Route::get('category/{category:slug}', CategoryController::class)->name('category');
Route::get('product/{product:slug}', ProductController::class)->name('product');
Route::post('order/{product}', [OrderController::class, 'store'])->name('order.store');
Route::get('order/{order}', [OrderController::class, 'show'])->name('order.show');
