<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', function (Request $request) {
    return view('hello', ['name' => $request->name ?? 'Akshaya']);
});


Route::get('/add-student', UserController::class . '@AddStudent');
Route::get('/student', UserController::class . '@student');
Route::get('/all-students', UserController::class . '@AllStudent');
