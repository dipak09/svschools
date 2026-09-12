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

    /**
     * Store a student submitted from the "Add Student" modal on the directory page.
     */
    public function StoreStudent(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create($data);

        return redirect()->route('students')
            ->with('status', $data['name'] . ' has been added to the directory.');
    }
}
