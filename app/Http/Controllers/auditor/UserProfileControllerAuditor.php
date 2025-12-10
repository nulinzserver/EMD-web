<?php

namespace App\Http\Controllers\auditor;

use Illuminate\Http\Request;

class UserProfileControllerAuditor
{
    public function user_profile()
    {
        return view('auditor.user.user_profile');
    }
}
