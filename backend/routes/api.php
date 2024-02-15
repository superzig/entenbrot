<?php

use App\Http\Controllers\ValidateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/validate')->group(function () {
    Route::get('/', [ValidateController::class, 'index'])->name('validate.index');
});
