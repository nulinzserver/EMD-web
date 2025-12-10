<?php

namespace App\Http\Controllers\admin;

use App\Models\master_client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthControllerAdmin
{
    public function signin()
    {
        return view('admin.auth.login');
    }

    public function forgot_pass()
    {
        return view('admin.auth.forgot_pass');
    }
    public function forgot_otp()
    {
        return view('admin.auth.otp');
    }
    public function signup_pass()
    {
        return view('admin.auth.change_pass');
    }
}
