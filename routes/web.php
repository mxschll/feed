<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StreamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['throttle:global'])->get('/', [StreamController::class, 'index'])->name('stream.index');
Route::middleware(['throttle:global'])->get('/{stream_uuid}', [StreamController::class, 'show'])->name('stream.show');
Route::middleware(['throttle:store-stream'])->post('/stream', [StreamController::class, 'store']);