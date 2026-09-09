<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;




class UserController extends Controller
{
    public function student(Request $request)
    {
        return view('student', ['name' => $request->name ?? 'Akshaya']);
    }

    public function AddStudent(Request $request)
    {
        $student = new User();
        $student->name = $request->name;
        $student->email = $request->email;
        $student->password = $request->password;
        $student->save();

        return redirect('/all-students');
    }

    public function AllStudent(Request $request)
    {
        $students = User::all();
        return view('all-students', ['students' => $students]);
    }
}
