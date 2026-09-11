<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Techer;

class TecherController extends Controller
{
    public function AllTechers()
    {
        $techers = Techer::all();
        return view('all-teachers', compact('techers'));
    }

    public function StoreTecher(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:techers,email'],
            'std' => ['required', 'string', ],
        ]);

        Techer::create($data);

        return redirect()->route('techers')
            ->with('status', $data['name'] . ' has been added to the directory.');
    }
}
