<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/users', [UserController::class, 'indexPage'])->name('users.index');
Route::get('/users/create', [UserController::class, 'createPage'])->name('users.create');
Route::get('/users/{id}/edit', [UserController::class, 'editPage'])->name('users.edit');
