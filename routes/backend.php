<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\Settings\DynamicPageController;
use App\Http\Controllers\Backend\Settings\FacebookSettingController;
use App\Http\Controllers\Backend\Settings\GoogleSettingController;
use App\Http\Controllers\Backend\Settings\MailSettingController;
use App\Http\Controllers\Backend\Settings\ProfileController;
use App\Http\Controllers\Backend\Settings\SocialMediaController;
use App\Http\Controllers\Backend\Settings\StripeSettingController;
use App\Http\Controllers\Backend\Settings\SystemSettingController;
use App\Http\Controllers\Backend\StyleController;
use App\Http\Controllers\Backend\ThemesController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

//! Route for ProfileController
Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.setting');
Route::post('/update-profile', [ProfileController::class, 'UpdateProfile'])->name('update.profile');

//! This route is for updating the user's profile
Route::post('/update-profile-picture', [ProfileController::class, 'UpdateProfilePicture'])->name('update.profile.picture');
Route::post('/update-profile-password', [ProfileController::class, 'UpdatePassword'])->name('update.Password');


//! Route for SystemSettingController
Route::get('/system-setting', [SystemSettingController::class, 'index'])->name('system.index');
Route::post('/system-setting', [SystemSettingController::class, 'update'])->name('system.update');

//! Route for MailSettingController
Route::get('/mail-setting', [MailSettingController::class, 'index'])->name('mail.setting');
Route::post('/mail-setting', [MailSettingController::class, 'update'])->name('mail.update');

//! Route for SocialMediaController
Route::get('/social-media', [SocialMediaController::class, 'index'])->name('social.index');
Route::post('/social-media', [SocialMediaController::class, 'update'])->name('social.update');
Route::delete('/social-media/{id}', [SocialMediaController::class, 'destroy'])->name('social.delete');

//! Route for DynamicpageController
Route::controller(DynamicPageController::class)->group(function () {
    Route::get('/dynamic-page', 'index')->name('dynamic_page.index');
    Route::get('/dynamic-page/create', 'create')->name('dynamic_page.create');
    Route::post('/dynamic-page/store', 'store')->name('dynamic_page.store');
    Route::get('/dynamic-page/edit/{id}', 'edit')->name('dynamic_page.edit');
    Route::post('/dynamic-page/update/{id}', 'update')->name('dynamic_page.update');
    Route::get('/dynamic-page/status/{id}', 'status')->name('dynamic_page.status');
    Route::delete('/dynamic-page/destroy/{id}', 'destroy')->name('dynamic_page.destroy');
});

//! Route for StripeSettingController
Route::get('/stripe-setting', [StripeSettingController::class, 'index'])->name('stripe.index');
Route::post('/stripe-setting', [StripeSettingController::class, 'update'])->name('stripe.update');

//! Route for Google Login
Route::get('/google-setting', [GoogleSettingController::class, 'index'])->name('google.index');
Route::post('/google-setting', [GoogleSettingController::class, 'update'])->name('google.update');

//! Route for Facebook Login
Route::get('/facebook-setting', [FacebookSettingController::class, 'index'])->name('facebook.index');
Route::post('/facebook-setting', [FacebookSettingController::class, 'update'])->name('facebook.update');

Route::controller(ThemesController::class)->group(function () {
    Route::get('/themes', 'index')->name('admin.themes.index');
    Route::get('/themes/create', 'create')->name('admin.themes.create');
    Route::post('/themes/store', 'store')->name('admin.themes.store');
    Route::get('/themes/edit/{id}', 'edit')->name('admin.themes.edit');
    Route::post('/themes/update/{id}', 'update')->name('admin.themes.update');
    Route::post('/themes/status/{id}', 'status')->name('admin.themes.status');
    Route::post('/themes/destroy/{id}', 'destroy')->name('admin.themes.destroy');
});

Route::controller(StyleController::class)->group(function () {
    Route::get('/styles', 'index')->name('admin.styles.index');
    Route::get('/styles/create', 'create')->name('admin.styles.create');
    Route::post('/styles/store', 'store')->name('admin.styles.store');
    Route::get('/styles/edit/{id}', 'edit')->name('admin.styles.edit');
    Route::post('/styles/update/{id}', 'update')->name('admin.styles.update');
    Route::post('/styles/status/{id}', 'status')->name('admin.styles.status');
    Route::post('/styles/destroy/{id}', 'destroy')->name('admin.styles.destroy');
});

Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'index')->name('admin.products.index');
    Route::get('/products/create', 'create')->name('admin.products.create');
    Route::post('/products/store', 'store')->name('admin.products.store');
    Route::get('/products/edit/{id}', 'edit')->name('admin.products.edit');
    Route::post('/products/update/{id}', 'update')->name('admin.products.update');
    Route::post('/products/status/{id}', 'status')->name('admin.products.status');
    Route::post('/products/popular/{id}', 'popular')->name('admin.products.popular');
    Route::post('/products/destroy/{id}', 'destroy')->name('admin.products.destroy');
});
