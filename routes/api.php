<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StyleController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\WallpaperController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SearchConroller;
use App\Http\Controllers\Api\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/social-login', [SocialAuthController::class, 'socialLogin']);

Route::resource('themes', ThemeController::class)->only(['index', 'store']);
Route::resource('styles', StyleController::class)->only(['index', 'store']);
Route::resource('products', ProductController::class)->except(['create', 'edit']);
Route::get('themes/{theme}/products', [ThemeController::class, 'getProductsByTheme']);
Route::get('themes/products/popular', [ThemeController::class, 'getPopularProducts']);
Route::get('themes/products/premium', [ThemeController::class, 'getPremiumProducts']);


//Search All Product
Route::post('/search/product', [SearchConroller::class, 'searchAllProduct']);

//Search Premium Product Only
Route::post('/search/product/premium', [SearchConroller::class, 'searchPremiumProduct']);


Route::group(['middleware' => ['jwt.verify']], function() {
    // Route to add/remove a product from favorites
    Route::post('favorites/toggle', [FavoriteController::class, 'toggleFavorite']);

    // Route to get all favorite products
    Route::get('favorites', [FavoriteController::class, 'getFavorites']);

    Route::post('/search/product/favorite', [SearchConroller::class, 'searchFavoriteProduct']);
    Route::post('/users/register/{id}', [UserController::class, 'userRegister']);
    Route::get('/users/data/{id}', [UserController::class, 'userData']);
    Route::post('/users/update/{id}', [UserController::class, 'userUpdate']);
    Route::delete('user/delete', [UserController::class, 'deleteUser']);
    Route::post('logout', [SocialAuthController::class, 'logout']);

});
