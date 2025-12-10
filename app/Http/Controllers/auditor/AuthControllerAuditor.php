<?php

namespace App\Http\Controllers\auditor;

use Illuminate\Http\Request;

class AuthControllerAuditor
{
    public function signin()
    {
        return view('auditor.auth.login');
    }
    public function forgot_pass()
    {
        return view('auditor.auth.forgot_pass');
    }
    public function forgot_otp()
    {
        return view('auditor.auth.otp');
    }
    public function change_pass()
    {
        return view('auditor.auth.change_pass');
    }
    public function signup()
    {
        return view('auditor.auth.signup');
    }
    public function signup_otp()
    {
        return view('auditor.auth.signup-otp');
    }
    public function signup_pass()
    {
        return view('auditor.auth.set_pass');
    }
}
