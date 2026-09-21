<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ActivityLog;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalStudents' => User::where('role', User::ROLE_STUDENT)->count(),
            'totalTeachers' => User::where('role', User::ROLE_TEACHER)->count(),
            'totalStaff' => User::whereIn('role', [User::ROLE_STAFF, User::ROLE_PRINCIPAL])->count(),
            'totalParents' => User::where('role', User::ROLE_PARENT)->count(),
            'activeUsers' => User::where('status', 'active')->count(),
            'totalClasses' => SchoolClass::count(),
            'totalSubjects' => Subject::count(),
            'recentUsers' => User::latest()->limit(8)->get(),
            'recentActivities' => ActivityLog::with('user')->latest()->limit(6)->get(),
        ]);
    }
}