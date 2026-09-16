<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Techer;
use Illuminate\Support\Facades\DB;

class TecherController extends Controller
{
    public function AllTechers()
    {
        $techers = Techer::get(); #Example of Eloquent ORM
        $techers2  = DB::table('techers')->where('id', 2)->get(); #example of Query Builder
        $techers3  = DB::select('SELECT * FROM techers WHERE id = 2'); #example of Raw SQL Query


        #dd($techers,$techers2,$techers3);



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
