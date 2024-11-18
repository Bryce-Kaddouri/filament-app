<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemporaryFileController;

/* Route::get('/', function () {
    return view('welcome');
});
 */
Route::get('/temporary-file/{filename}', [TemporaryFileController::class, 'serve'])
       ->name('temporary-file.serve');
