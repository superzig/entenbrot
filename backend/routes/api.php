<?php

use App\Http\Controllers\ExportCsvController;
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
    Route::get('/update1', [ValidateController::class, 'index'])->name('validate.update.index');
    Route::get('/export', [ExportCsvController::class, 'index'])->name('export');
    Route::get('/export', [ExportCsvController::class, 'export'])->name('export.post');
    Route::get('/algo', [ValidateController::class, 'algoConfig'])->name('algoConfig');
    Route::get('/returnPresenceList', [ExportCsvController::class, 'generatePresenceList'])->name('export.generatePresenceList');
    Route::post('/returnCompanies', [ValidateController::class, 'returnCompanies'])->name('validate.returnCompanies');
    Route::post('/returnStudents', [ValidateController::class, 'returnStudents'])->name('validate.returnStudents');
    Route::post('/returnRooms', [ValidateController::class, 'returnRooms'])->name('validate.returnRooms');
});
