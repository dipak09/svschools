<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\SchoolManagementController;
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

    Route::middleware('role:admin,super_admin,principal,staff')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.status');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/students', [SchoolManagementController::class, 'students'])->name('students');
        Route::post('/students', [SchoolManagementController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/{student}', [SchoolManagementController::class, 'showStudent'])->name('students.show');
        Route::get('/students/{student}/edit', [SchoolManagementController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{student}', [SchoolManagementController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{student}', [SchoolManagementController::class, 'deleteStudent'])->name('students.delete');

        Route::get('/teachers', [SchoolManagementController::class, 'teachers'])->name('teachers');
        Route::post('/teachers', [SchoolManagementController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/{teacher}', [SchoolManagementController::class, 'showTeacher'])->name('teachers.show');
        Route::get('/teachers/{teacher}/edit', [SchoolManagementController::class, 'editTeacher'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [SchoolManagementController::class, 'updateTeacher'])->name('teachers.update');

        Route::get('/parents', [SchoolManagementController::class, 'parents'])->name('parents');
        Route::post('/parents', [SchoolManagementController::class, 'storeParent'])->name('parents.store');
        Route::get('/parents/{parent}', [SchoolManagementController::class, 'showParent'])->name('parents.show');
        Route::get('/parents/{parent}/edit', [SchoolManagementController::class, 'editParent'])->name('parents.edit');
        Route::put('/parents/{parent}', [SchoolManagementController::class, 'updateParent'])->name('parents.update');

        Route::get('/classes', [SchoolManagementController::class, 'classes'])->name('classes');
        Route::post('/classes', [SchoolManagementController::class, 'storeClass'])->name('classes.store');
        Route::get('/classes/{class}/edit', [SchoolManagementController::class, 'editClass'])->name('classes.edit');
        Route::put('/classes/{class}', [SchoolManagementController::class, 'updateClass'])->name('classes.update');
        Route::delete('/classes/{class}', [SchoolManagementController::class, 'deleteClass'])->name('classes.delete');

        Route::post('/sections', [SchoolManagementController::class, 'storeSection'])->name('sections.store');
        Route::get('/sections/{section}/edit', [SchoolManagementController::class, 'editSection'])->name('sections.edit');
        Route::put('/sections/{section}', [SchoolManagementController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{section}', [SchoolManagementController::class, 'deleteSection'])->name('sections.delete');

        Route::get('/subjects', [SchoolManagementController::class, 'subjects'])->name('subjects');
        Route::post('/subjects', [SchoolManagementController::class, 'storeSubject'])->name('subjects.store');
        Route::get('/subjects/{subject}/edit', [SchoolManagementController::class, 'editSubject'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [SchoolManagementController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SchoolManagementController::class, 'deleteSubject'])->name('subjects.delete');
        Route::post('/subjects/{subject}/assign-teacher', [SchoolManagementController::class, 'assignTeacher'])->name('subjects.assign-teacher');

        Route::get('/attendance', [SchoolManagementController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [SchoolManagementController::class, 'storeAttendance'])->name('attendance.store');
        Route::get('/attendance/history', [SchoolManagementController::class, 'attendanceHistory'])->name('attendance.history');
        Route::get('/attendance/export', [SchoolManagementController::class, 'attendanceExport'])->name('attendance.export');

        Route::get('/academics', [SchoolManagementController::class, 'academics'])->name('academics');
        Route::post('/academic-years', [SchoolManagementController::class, 'storeAcademicYear'])->name('academic-years.store');
        Route::put('/academic-years/{academicYear}', [SchoolManagementController::class, 'updateAcademicYear'])->name('academic-years.update');
        Route::delete('/academic-years/{academicYear}', [SchoolManagementController::class, 'deleteAcademicYear'])->name('academic-years.delete');
        Route::post('/exams', [SchoolManagementController::class, 'storeExam'])->name('exams.store');
        Route::put('/exams/{exam}', [SchoolManagementController::class, 'updateExam'])->name('exams.update');
        Route::delete('/exams/{exam}', [SchoolManagementController::class, 'deleteExam'])->name('exams.delete');
        Route::post('/exam-schedules', [SchoolManagementController::class, 'storeExamSchedule'])->name('exam-schedules.store');
        Route::post('/exam-results', [SchoolManagementController::class, 'storeResult'])->name('exam-results.store');
        Route::delete('/exam-results/{result}', [SchoolManagementController::class, 'deleteResult'])->name('exam-results.delete');

        Route::get('/announcements', [SchoolManagementController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [SchoolManagementController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [SchoolManagementController::class, 'editAnnouncement'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [SchoolManagementController::class, 'updateAnnouncement'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [SchoolManagementController::class, 'deleteAnnouncement'])->name('announcements.delete');
        Route::post('/announcements/{announcement}/toggle', [SchoolManagementController::class, 'toggleAnnouncement'])->name('announcements.toggle');

        Route::get('/reports', [SchoolManagementController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [SchoolManagementController::class, 'reportExport'])->name('reports.export');

        Route::get('/activities', [SchoolManagementController::class, 'activities'])->name('activities');

        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
        Route::post('/roles/assign-user/{user}', [AdminRoleController::class, 'assignUser'])->name('roles.assign-user');
    });
});
