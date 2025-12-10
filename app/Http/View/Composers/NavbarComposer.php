<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterClient;
use Illuminate\Support\Facades\DB;

class NavbarComposer
{
    public function compose(View $view)
    {
        $password_change = MasterClient::where('id', Auth::id())
            ->select('password')
            ->first();

        $update_profile = DB::table('master_clients as mc')
            ->leftjoin('master_clients_db as mcd', 'mc.gst_number', '=', 'mcd.gst_no')
            ->select(
                'mc.business_legalname',
                'mc.promotors_name',
                'mc.pan_number',
                'mc.phone_number',
                'mc.email',
                'mc.address',
                'mcd.nature_of_business',
                'mcd.date_of_registration',
                'mc.turn_over'
            )
            ->where('mc.id', Auth::id())
            ->first();

        $attachemnt = DB::table('signature_uploads')->where('mc_id', Auth::id())->select('signature', 'logo')->first();

        $view->with([
            'authUser' => Auth::user(),
            'password_change' => $password_change,
            'attachemnt' => $attachemnt,
            'update_profile' => $update_profile
        ]);
    }
}
