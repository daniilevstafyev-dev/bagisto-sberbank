<?php
use Illuminate\Support\Facades\Route;
use Goldmangroup\Sberbank\Http\Controllers\SberbankController;


Route::group(['middleware' => ['web']], function () {
    Route::prefix('sberbank')->group(function () {
        Route::get('/success/{id}', [SberbankController::class, 'success'])->name('sberbank.success');
        Route::get('/fail/{id}', [SberbankController::class, 'fail'])->name('sberbank.fail');
    });
});