<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['cors'])->group(['prefix' => 'account'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

Route::middleware(['cors'])->group(['middleware' => ['auth:sanctum']], function () {
    Route::get('products', [ProductController::class, 'allProducts']);
    Route::get('product/{slug}', [ProductController::class, 'getProduct']);
    Route::middleware(['ability:admin'])->group(function () {
        Route::post('products', [ProductController::class, 'createProduct']);
        Route::put('products', [ProductController::class, 'updateProduct']);
        Route::delete('products', [ProductController::class, 'deleteProduct']);
    });
});
