<?php

namespace App\Http\Controllers\landing;

use Illuminate\Http\Request;

class LandingPageController
{
    public function home()
    {
        return view('landing.index');
    }
}
