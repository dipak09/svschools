<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\User;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fees = Fee::with('student')->latest()->get();
        $students = User::orderBy('name')->get(['id', 'name']);

        return view('fees', [
            'fees' => $fees,
            'students' => $students,
            'totalAmount' => $fees->sum('amount'),
            'collectedAmount' => $fees->sum('paid_amount'),
            'outstandingAmount' => $fees->sum(fn (Fee $fee) => $fee->balance),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'fee_type' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Fee::create($data);

        return redirect()->route('fees')->with('status', 'Fee record added successfully.');
    }
}