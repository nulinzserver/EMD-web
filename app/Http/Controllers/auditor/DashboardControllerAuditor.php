<?php

namespace App\Http\Controllers\auditor;

use Illuminate\Http\Request;

class DashboardControllerAuditor
{
    public function dashboard()
    {
        return view('auditor.dashboard.index');
    }
}
