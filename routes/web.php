<?php

use App\Http\Controllers\BillAiController;
use App\Http\Controllers\GoogleBillController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemporaryFileController;

/* Route::get('/', function () {
    return view('welcome');
});
 */
Route::get('/temporary-file/{filename}', [TemporaryFileController::class, 'serve'])
       ->name('temporary-file.serve');

Route::get('/bill-ai', [BillAiController::class, 'index'])
       ->name('bill-ai.index');

Route::get('/google-bill', [GoogleBillController::class, 'listBillAccount'])
       ->name('google-bill.list');


