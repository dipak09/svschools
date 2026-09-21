<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TecherController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\SchoolManagementController;
use App\Http\Controllers\AdminRoleController;
use App\Models\GymBanner;


Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/hello', function (Request $request) {

    // Same join as before, through the GymBanner -> Gym relationship.
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


    Route::get('/student', UserController::class . '@student')->middleware('role:student,staff,admin');

    Route::middleware('role:staff,admin')->group(function () {
        Route::get('/add-student', UserController::class . '@AddStudent');
        Route::get('/all-students', UserController::class . '@AllStudent')->name('students');
        Route::post('/all-students', UserController::class . '@StoreStudent')->name('students.store');

        Route::get('/all-techers', TecherController::class . '@AllTechers')->name('techers');
        Route::post('/all-techers', TecherController::class . '@StoreTecher')->name('techers.store');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin,admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', AdminUserController::class)->except(['create', 'show'])->middleware('permission:users.manage');
        Route::get('/users/create', [AdminUserController::class, 'create'])->middleware('permission:users.manage')->name('users.create');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->middleware('permission:users.manage')->name('users.show');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->middleware('permission:users.manage')->name('users.status');
        Route::get('/roles', [AdminRoleController::class, 'index'])->middleware('permission:roles.manage')->name('roles');
        Route::post('/roles', [AdminRoleController::class, 'store'])->middleware('permission:roles.manage')->name('roles.store');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:roles.manage')->name('roles.update');
        Route::post('/roles/users/{user}', [AdminRoleController::class, 'assignUser'])->middleware('permission:roles.manage')->name('roles.assign-user');

        Route::get('/students', [SchoolManagementController::class, 'students'])->middleware('permission:students.view')->name('students');
        Route::post('/students', [SchoolManagementController::class, 'storeStudent'])->middleware('permission:students.create')->name('students.store');
        Route::get('/students/{student}', [SchoolManagementController::class, 'showStudent'])->middleware('permission:students.view')->name('students.show');
        Route::get('/students/{student}/edit', [SchoolManagementController::class, 'editStudent'])->middleware('permission:students.edit')->name('students.edit');
        Route::put('/students/{student}', [SchoolManagementController::class, 'updateStudent'])->middleware('permission:students.edit')->name('students.update');
        Route::delete('/students/{student}', [SchoolManagementController::class, 'deleteStudent'])->middleware('permission:students.delete')->name('students.destroy');
        Route::get('/teachers', [SchoolManagementController::class, 'teachers'])->middleware('permission:teachers.view')->name('teachers');
        Route::post('/teachers', [SchoolManagementController::class, 'storeTeacher'])->middleware('permission:teachers.create')->name('teachers.store');
        Route::get('/teachers/{teacher}', [SchoolManagementController::class, 'showTeacher'])->middleware('permission:teachers.view')->name('teachers.show');
        Route::get('/teachers/{teacher}/edit', [SchoolManagementController::class, 'editTeacher'])->middleware('permission:teachers.edit')->name('teachers.edit');
        Route::put('/teachers/{teacher}', [SchoolManagementController::class, 'updateTeacher'])->middleware('permission:teachers.edit')->name('teachers.update');
        Route::get('/parents', [SchoolManagementController::class, 'parents'])->middleware('permission:users.manage')->name('parents');
        Route::post('/parents', [SchoolManagementController::class, 'storeParent'])->middleware('permission:users.manage')->name('parents.store');
        Route::get('/parents/{parent}', [SchoolManagementController::class, 'showParent'])->middleware('permission:users.manage')->name('parents.show');
        Route::get('/parents/{parent}/edit', [SchoolManagementController::class, 'editParent'])->middleware('permission:users.manage')->name('parents.edit');
        Route::put('/parents/{parent}', [SchoolManagementController::class, 'updateParent'])->middleware('permission:users.manage')->name('parents.update');
        Route::get('/classes', [SchoolManagementController::class, 'classes'])->middleware('permission:classes.manage')->name('classes');
        Route::post('/classes', [SchoolManagementController::class, 'storeClass'])->middleware('permission:classes.manage')->name('classes.store');
        Route::put('/classes/{class}', [SchoolManagementController::class, 'updateClass'])->middleware('permission:classes.manage')->name('classes.update');
        Route::get('/classes/{class}/edit', [SchoolManagementController::class, 'editClass'])->middleware('permission:classes.manage')->name('classes.edit');
        Route::delete('/classes/{class}', [SchoolManagementController::class, 'deleteClass'])->middleware('permission:classes.manage')->name('classes.destroy');
        Route::post('/sections', [SchoolManagementController::class, 'storeSection'])->middleware('permission:classes.manage')->name('sections.store');
        Route::put('/sections/{section}', [SchoolManagementController::class, 'updateSection'])->middleware('permission:classes.manage')->name('sections.update');
        Route::get('/sections/{section}/edit', [SchoolManagementController::class, 'editSection'])->middleware('permission:classes.manage')->name('sections.edit');
        Route::delete('/sections/{section}', [SchoolManagementController::class, 'deleteSection'])->middleware('permission:classes.manage')->name('sections.destroy');
        Route::get('/subjects', [SchoolManagementController::class, 'subjects'])->middleware('permission:subjects.manage')->name('subjects');
        Route::post('/subjects', [SchoolManagementController::class, 'storeSubject'])->middleware('permission:subjects.manage')->name('subjects.store');
        Route::delete('/subjects/{subject}', [SchoolManagementController::class, 'deleteSubject'])->middleware('permission:subjects.manage')->name('subjects.destroy');
        Route::post('/subjects/{subject}/teacher', [SchoolManagementController::class, 'assignTeacher'])->middleware('permission:subjects.manage')->name('subjects.teacher');
        Route::get('/subjects/{subject}/edit', [SchoolManagementController::class, 'editSubject'])->middleware('permission:subjects.manage')->name('subjects.edit');
        Route::put('/subjects/{subject}', [SchoolManagementController::class, 'updateSubject'])->middleware('permission:subjects.manage')->name('subjects.update');
        Route::get('/attendance', [SchoolManagementController::class, 'attendance'])->middleware('permission:attendance.view')->name('attendance');
        Route::post('/attendance', [SchoolManagementController::class, 'storeAttendance'])->middleware('permission:attendance.mark')->name('attendance.store');
        Route::get('/attendance/history', [SchoolManagementController::class, 'attendanceHistory'])->middleware('permission:attendance.view')->name('attendance.history');
        Route::get('/attendance/export', [SchoolManagementController::class, 'attendanceExport'])->middleware('permission:attendance.view')->name('attendance.export');
        Route::get('/academics', [SchoolManagementController::class, 'academics'])->middleware('permission:academics.view')->name('academics');
        Route::post('/academic-years', [SchoolManagementController::class, 'storeAcademicYear'])->middleware('permission:academics.edit')->name('academic-years.store');
        Route::put('/academic-years/{academicYear}', [SchoolManagementController::class, 'updateAcademicYear'])->middleware('permission:academics.edit')->name('academic-years.update');
        Route::delete('/academic-years/{academicYear}', [SchoolManagementController::class, 'deleteAcademicYear'])->middleware('permission:academics.edit')->name('academic-years.destroy');
        Route::post('/exams', [SchoolManagementController::class, 'storeExam'])->middleware('permission:academics.edit')->name('exams.store');
        Route::put('/exams/{exam}', [SchoolManagementController::class, 'updateExam'])->middleware('permission:academics.edit')->name('exams.update');
        Route::delete('/exams/{exam}', [SchoolManagementController::class, 'deleteExam'])->middleware('permission:academics.edit')->name('exams.destroy');
        Route::post('/exam-schedules', [SchoolManagementController::class, 'storeExamSchedule'])->middleware('permission:academics.edit')->name('exam-schedules.store');
        Route::post('/results', [SchoolManagementController::class, 'storeResult'])->middleware('permission:academics.edit')->name('results.store');
        Route::delete('/results/{result}', [SchoolManagementController::class, 'deleteResult'])->middleware('permission:academics.edit')->name('results.destroy');
        Route::get('/announcements', [SchoolManagementController::class, 'announcements'])->middleware('permission:announcements.view')->name('announcements');
        Route::post('/announcements', [SchoolManagementController::class, 'storeAnnouncement'])->middleware('permission:announcements.create')->name('announcements.store');
        Route::put('/announcements/{announcement}', [SchoolManagementController::class, 'updateAnnouncement'])->middleware('permission:announcements.edit')->name('announcements.update');
        Route::get('/announcements/{announcement}/edit', [SchoolManagementController::class, 'editAnnouncement'])->middleware('permission:announcements.edit')->name('announcements.edit');
        Route::patch('/announcements/{announcement}/publish', [SchoolManagementController::class, 'toggleAnnouncement'])->middleware('permission:announcements.edit')->name('announcements.publish');
        Route::delete('/announcements/{announcement}', [SchoolManagementController::class, 'deleteAnnouncement'])->middleware('permission:announcements.edit')->name('announcements.destroy');
        Route::get('/reports', [SchoolManagementController::class, 'reports'])->middleware('permission:reports.view')->name('reports');
        Route::get('/reports/export', [SchoolManagementController::class, 'reportExport'])->middleware('permission:reports.view')->name('reports.export');
        Route::get('/activities', [SchoolManagementController::class, 'activities'])->name('activities');
    });
});
