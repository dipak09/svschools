<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\UserController;
use App\Models\GymBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/hello', function (Request $request) {
    $gym_data = GymBanner::with('gym')->get();

    return view('hello', [
        'name' => $request->name ?? 'Akshaya',
        'gym_data' => $gym_data,
    ]);
})->name('hello');

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

    Route::get('/student', [UserController::class, 'student'])
        ->middleware('role:student,staff,admin');

    Route::middleware('role:staff,admin')->group(function () {
        Route::get('/add-student', [UserController::class, 'AddStudent'])->name('add.student');
        Route::get('/all-students', [UserController::class, 'AllStudent'])->name('students');
        Route::post('/all-students', [UserController::class, 'StoreStudent'])->name('students.store');

        Route::get('/fees', [FeeController::class, 'index'])->name('fees');
        Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
        Route::get('/fees/{fee}', [FeeController::class, 'bill'])->name('fees.bill');
    });
});
