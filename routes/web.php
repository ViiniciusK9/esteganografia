<?php

use App\Http\Controllers\EsteganografiaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EsteganografiaController::class, 'index'])->name('index');
Route::get('/decode', [EsteganografiaController::class, 'decodeForm'])->name('decode-form');
Route::post('/decode', [EsteganografiaController::class, 'decodeImage'])->name('decode-submit');
Route::get('/show', [EsteganografiaController::class, 'index'])->name('show');

Route::post('/encode', [EsteganografiaController::class, 'encode'])->name('encode');

