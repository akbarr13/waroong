<?php

use App\Http\Controllers\Auth\GoogleController;
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

Route::get("/", function () {
    return redirect("/admin");
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google')->middleware('throttle:5,1');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->middleware('throttle:10,1');

Route::get('/struk/{transaction}', function (\App\Models\Transaction $transaction) {
    if ($transaction->user_id !== auth()->id()) {
        abort(403);
    }
    $transaction->load('items.product', 'customer', 'user');
    return view('struk', compact('transaction'));
})->middleware(['auth'])->name('struk');
