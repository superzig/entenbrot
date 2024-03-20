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
    Route::get('/update1', [DataController::class, 'index'])->name('data.update.index');
    Route::get('/export', [ExportCsvController::class, 'index'])->name('export');
    Route::get('/export', [ExportCsvController::class, 'export'])->name('export.post');
    Route::get('/algo', [DataController::class, 'algoConfig'])->name('algoConfig');
    Route::post('/serializeCompanies', [DataController::class, 'serializeCompaniesAction'])->name('data.serializeCompanies');
    Route::post('/serializeStudents', [DataController::class, 'serializeStudentsAction'])->name('data.serializeStudents');
    Route::post('/serializeRooms', [DataController::class, 'serializeRoomsAction'])->name('data.serializeRooms');
    Route::post('/algorithmen', [DataController::class, 'algorithmAction'])->name('data.algorithmen');
    Route::get('/algorithmen', [DataController::class, 'listAction'])->name('data.algorithmen_list');
    Route::delete('/algorithmen/{cacheKey}', [DataController::class, 'deleteAction'])->name('data.algorithmen_delete');
    Route::get('/algorithmen/{cacheKey}', [DataController::class, 'viewAction'])->name('data.algorithmen_view');
});
