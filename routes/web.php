<?php

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


//listing page view
Route::get('/', [\App\Http\Controllers\ParserController::class, 'index'])->name('view.index');

//view for folder or file
Route::get('/heritages/{path}', [\App\Http\Controllers\ParserController::class, 'show'])->name('view.show')->where('path', '.*');

//search method by keyword
Route::get('/search', [\App\Http\Controllers\ParserController::class, 'search'])->name('view.search');

//search by tags
Route::get('/tags/{tag}', [\App\Http\Controllers\ParserController::class, 'searchByTag'])->name('view.search.tag');

