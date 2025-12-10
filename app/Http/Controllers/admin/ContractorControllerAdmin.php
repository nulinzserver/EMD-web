<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

class ContractorControllerAdmin
{
    public function contractor_list()
    {
        return view('admin.contractor.list');
    }
}
