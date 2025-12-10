<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

class AccessControllerWeb
{
    public function access_list()
    {
        return view('web.access.list');
    }
}
