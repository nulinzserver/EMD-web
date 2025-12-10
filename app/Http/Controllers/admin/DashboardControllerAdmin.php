<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

class DashboardControllerAdmin
{
    public function index()
    {
        return view('admin.dashboard.index');
    }
}
