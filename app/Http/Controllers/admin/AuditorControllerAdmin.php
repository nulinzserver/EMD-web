<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

class AuditorControllerAdmin
{
    public function auditor_list()
    {
        return view('admin.auditor.list');
    }
}
