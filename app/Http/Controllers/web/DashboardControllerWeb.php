<?php

namespace App\Http\Controllers\web;

use App\Models\MasterClient;
use App\Models\MasterTender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DashboardControllerWeb
{

    public function index()
    {
        $userId = Auth::guard('client')->id();
        // or full user object
        $user = Auth::guard('client')->user();
        // tender
        $tender = $user->tenders;
        // expernse
        $expense = $user->expense;

        // overall expense
        $grouped = $expense->groupBy('expense_category')->map(function ($row) {
            return $row->sum('amount');
        });

        $chartLabels = $grouped->keys();
        $chartAmounts = $grouped->values();

        $turnValue = DB::table('master_clients')
            ->where('id', $userId)
            ->value('turn_over');

        $clientsCount = DB::table('master_clients_sync')
            ->where('mc_id', $userId)
            ->count();

        $tendersCount = DB::table('master_tender')
            ->where('mc_id', $userId)
            ->count();

        $totalAmount = DB::table('tender_bill')
            ->where('mc_id', $userId)
            ->sum('total_amount');

        $withHeld = DB::table('tender_bill')
            ->where('mc_id', $userId)
            ->sum('withheld_amount');

        $otherAmount = DB::table('tender_bill')
            ->where('mc_id', $userId)
            ->sum('others_amount');

        return view('web.dashboard.index', compact('userId', 'user', 'tender', 'expense', 'chartLabels', 'chartAmounts', 'grouped', 'turnValue', 'clientsCount', 'tendersCount', 'totalAmount', 'withHeld', 'otherAmount'));
    }

    public function add_attach(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'logo' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $mcId = auth()->user()->id;;

        $folderPath = public_path('signature');
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0777, true);
        }

        // Signature upload
        $file = $request->file('signature');
        $signatureFilename = 'signature_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $signaturePath = 'signature/' . $signatureFilename;
        $file->move($folderPath, $signatureFilename);

        // Logo upload
        $logoFile = $request->file('logo');
        $logoFilename = 'logo_' . $mcId . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
        $logoPath = 'signature/' . $logoFilename;
        $logoFile->move($folderPath, $logoFilename);

        // Delete old record
        DB::table('signature_uploads')->where('mc_id', $mcId)->delete();

        // Insert record
        DB::table('signature_uploads')->insert([
            'mc_id' => $mcId,
            'signature' => $signaturePath,
            'logo' => $logoPath,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        return back()->with([
            'status' => 'Success',
            'message' => 'Logo and Signature added succesfully'
        ]);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        // Update new password
        DB::table('master_clients')
            ->where('id', Auth::id())
            ->update([
                'password'   => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);

        return back()->with([
            'status' => 'Success',
            'message' => 'Password updated successfully'
        ]);
    }

    // public function update_profile(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'nullable|string',
    //         'legal_name' => 'nullable|string',
    //         'buisness_name' => 'nullable|string',
    //         'promoter_name' => 'nullable|string',
    //         'annual_income' => 'nullable|string',
    //         'pan_number' => 'nullable|string',
    //         'phone_number' => 'required|string|min:10|max:15',
    //         'email' => 'nullable|string',
    //         'address' => 'nullable|string'
    //     ]);

    //     $user = $request->user_id;

    //     // Fetch the client
    //     $client = DB::table('master_clients')->where('id', $user)->first();

    //     // Update only the phone number
    //     DB::table('master_clients')
    //         ->where('id', $user)
    //         ->update([
    //             'phone_number' => $request->phone_number,
    //             'business_legalname' => $request->buisness_name,
    //             'promotors_name' => $request->promoter_name,
    //             'pan_number' => $request->pan_number,
    //             'phone_number' => $request->phone_number,
    //             'email' => $request->email,
    //             'address' => $request->address,
    //             'updated_at' => now()
    //         ]);

    //     return back()->with([
    //         'status' => 'Success',
    //         'message' => 'Phone number updated successfully'
    //     ]);
    // }
    public function update_profile(Request $request)
    {

        $user = auth()->id();

        $request->validate([
            'user_id' => 'required|integer',
            'legal_name' => 'nullable|string',
            'business_type' => 'nullable|string',
            'promoter_name' => 'nullable|string',
            'register_date' => 'nullable|date',
            'turn_over' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'phone_number' => 'required|string|min:10|max:15',
            'email' => 'nullable|email',
            // 'address' => 'nullable|string'
        ]);

        // 📌 UPDATE master_clients TABLE
        DB::table('master_clients')
            ->where('id', $user)
            ->update([
                'business_legalname' => $request->legal_name,
                'promotors_name' => $request->promoter_name,
                'pan_number' => $request->pan_number,
                'phone_number' => $request->phone_number,
                'turn_over' => $request->turn_over,
                'email' => $request->email,
                // 'address' => $request->address,
                'updated_at' => now(),
            ]);

        // 📌 UPDATE master_clients_db TABLE
        DB::table('master_clients_db')
            ->where('gst_no', Auth::user()->gst_number)
            ->update([
                'nature_of_business' => $request->business_type,
                'date_of_registration' => $request->register_date,
                'annual_turnover' => $request->annual_income,
                'promoters' => $request->promoter_name,
                // 'address' => $request->address,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'updated_at' => now(),
            ]);

        return back()->with([
            'status' => 'Success',
            'message' => 'Profile updated successfully'
        ]);
    }
}
