<?php

use App\Http\Controllers\ExportCsvController;
use App\Http\Controllers\DataController;
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

Route::prefix('/data')->group(function () {
    Route::post('/algorithmen', [DataController::class, 'algorithmAction'])->name('data.algorithmen');
    Route::get('/algorithmen', [DataController::class, 'listAction'])->name('data.algorithmen_list');
    Route::delete('/algorithmen/{cacheKey}', [DataController::class, 'deleteAction'])->name('data.algorithmen_delete');
    Route::get('/algorithmen/{cacheKey}', [DataController::class, 'viewAction'])->name('data.algorithmen_view');
});

Route::prefix('/download')->group(function () {
    Route::get("/documents/{cacheKey}", [ExportCsvController::class, 'downloadDocuments'])->name('export.documents');
});
