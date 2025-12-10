<?php

namespace App\Http\Controllers;

use App\Models\MasterClient;
use Illuminate\Http\Request;

class NavControllerWeb extends Controller
{
    public function change_password(Request $request)
    {
        $password_change = MasterClient::where('id', auth()->user()->id)->select('password')->first();

        // dd($password_change);

        
    }
}
