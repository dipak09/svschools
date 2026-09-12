<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TecherController;


Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/hello', function (Request $request) {
    return view('hello', ['name' => $request->name ?? 'Akshaya']);
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    Route::get('/add-student', UserController::class . '@AddStudent');
    Route::get('/student', UserController::class . '@student');
    Route::get('/all-students', UserController::class . '@AllStudent')->name('students');
    Route::post('/all-students', UserController::class . '@StoreStudent')->name('students.store');


    Route::get('/all-techers', TecherController::class . '@AllTechers')->name('techers');
    Route::post('/all-techers', TecherController::class . '@StoreTecher')->name('techers.store');
});
