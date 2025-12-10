<?php


namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\MasterClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthControllerWeb
{
    public function signin()
    {
        return view('web.auth.login');
    }

    public function signin_check(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'phone_number' => $request->mobile_number,
            'password' => $request->password,
        ];

        if (Auth::guard('client')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // 4. If the attempt fails, redirect back with an error
        return back()
            ->with('login_error', 'Invalid mobile number or password')
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgot_pass()
    {
        return view('web.auth.forgot_pass');
    }

    public function forgot_pass_submit(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|max:15'
        ]);

        $phone = $request->phone_number;

        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        // Generate OTP
        $otp = rand(1000, 9999);

        // Store OTP in master_clients table
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'otp' => $otp,
                'otp_verified' => false,
                'updated_at' => now()
            ]);

        session([
            'forgot_otp'   => $otp,
            'forgot_phone' => $phone
        ]);

        return redirect()->route('forgot_otp');
    }

    public function forgot_otp()
    {
        $otp = session('forgot_otp');
        $phone = session('forgot_phone');

        return view('web.auth.forgot_pass_otp', compact('otp', 'phone'));
    }

    public function forgot_password_verifyotp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
            'otp' => 'required|digits:4'
        ]);

        $phone = $request->phone_number;
        $otp = $request->otp;

        $client = DB::table('master_clients')->where('phone_number', $phone)->first();


        if ($client->otp != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 400);
        }

        // Mark OTP as verified
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'otp_verified' => true,
                'updated_at' => now()
            ]);

        session([
            'forgot_phone' => $phone,
            'forgot_user' => $client->id
        ]);

        return redirect()->route('change_pass');
    }


    public function change_pass()
    {
        $user = session('forgot_user');
        $phone = session('forgot_phone');

        return view('web.auth.change_pass', compact('user', 'phone'));
    }

    public function forgot_password_reset(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'password' => 'required'
        ]);

        $phone = $request->phone_number;

        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found'
            ], 404);
        }

        if (!$client->otp_verified) {
            return response()->json([
                'success' => false,
                'message' => 'OTP not verified. Please verify OTP first.'
            ], 400);
        }

        // Reset password
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'password' => Hash::make($request->password),
                'otp_verified' => false, // reset OTP verification
                'otp' => null,           // clear OTP
                'updated_at' => now()
            ]);

        return redirect()->route('login')->with([
            'status' => 'Success',
            'message' => 'Password reset successfully'
        ]);
    }

    public function signup_gst()
    {
        return view('web.auth.signup-gst');
    }

    public function gst_submit(Request $request)
    {
        $request->validate([
            'gst_no' => 'required|string'
        ]);

        $gst_no = strtoupper(trim($request->gst_no));

        // Check if GST exists in master_clients_db
        $existing = DB::table('master_clients_db')->where('gst_no', $gst_no)->first();

        if ($existing) {

            // Generate OTP
            $otp = rand(1000, 9999);

            DB::table('master_clients')->updateOrInsert(
                ['gst_number' => $gst_no],
                [
                    'business_legalname' => $existing->legal_name,
                    'promotors_name' => $existing->promoters,
                    'pan_number' => $existing->pan_number,
                    'phone_number' => $existing->phone_number,
                    'email' => $existing->email,
                    'address' => $existing->address,
                    'otp' => $otp,
                    'updated_at' => now()
                ]
            );

            // Send OTP
            if ($existing->phone_number) {
                $this->sendOtp($existing->phone_number, $otp);
            }

            // Redirect to GST details page
            return redirect()->route('signup_details')->with([
                'source' => 'local',
                'otp' => $otp,
                'gst_details' => [
                    'gst_number' => $existing->gst_no,
                    'business_legalname' => $existing->legal_name,
                    'promotors_name' => $existing->promoters,
                    'pan_number' => $existing->pan_number,
                    'phone_number' => $existing->phone_number,
                    'email' => $existing->email,
                    'address' => $existing->address
                ]
            ]);
        }

        // =============== API CALL BELOW (UNCHANGED) ===============

        $group_id = 'ec33ab9a-6ebb-46a7-b87d-3966673f1214';
        $task_id = '8abc6431-fc08-4594-bc8d-090df206f15c';

        $payload = [
            'group_id' => $group_id,
            'task_id' => $task_id,
            'data' => [
                'gstnumber' => $gst_no,
                'isdetails' => true
            ]
        ];

        $client = new Client();

        try {
            $response = $client->post('https://eve.idfy.com/v3/tasks/async/retrieve/gst_info', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'api-key' => 'ccb9fc21-aa17-424b-8b45-6627a571d679',
                    'account-id' => 'cad6ab7d7e57/871863ea-879e-4772-9879-b92b12500392'
                ],
                'json' => $payload
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $requestId = $res['request_id'] ?? null;

            if (!$requestId) {
                return redirect()->back()->with('error', 'Request ID not found');
            }

            sleep(7);

            $pollResponse = $client->get('https://eve.idfy.com/v3/tasks', [
                'headers' => [
                    'Accept' => 'application/json',
                    'api-key' => 'ccb9fc21-aa17-424b-8b45-6627a571d679',
                    'account-id' => 'cad6ab7d7e57/871863ea-879e-4772-9879-b92b12500392'
                ],
                'query' => ['request_id' => $requestId],
            ]);

            $data = json_decode($pollResponse->getBody()->getContents(), true);
            $details = $data[0]['result']['details'] ?? null;

            if (!$details) {
                return redirect()->back()->with('error', 'No GST details found');
            }

            $promoters = isset($details['promoters']) ? implode(', ', $details['promoters']) : null;
            $phone = $details['contact_details']['principal']['mobile'] ?? null;
            $email = $details['contact_details']['principal']['email'] ?? null;
            $address = $details['contact_details']['principal']['address'] ?? null;
            $legal_name = $details['legal_name'] ?? null;
            $pan_number = $details['pan_number'] ?? null;

            // Insert into master_clients_db
            DB::table('master_clients_db')->insert([
                'gst_no' => $details['gstin'] ?? $gst_no,
                'business_name' => $details['business_name'] ?? null,
                'legal_name' => $legal_name,
                'pan_number' => $pan_number,
                'promoters' => $promoters,
                'address' => $address,
                'email' => $email,
                'phone_number' => $phone,
                'constitution_of_business' => $details['constitution_of_business'] ?? null,
                'taxpayer_type' => $details['taxpayer_type'] ?? null,
                'nature_of_business' => $details['contact_details']['principal']['nature_of_business'] ?? null,
                'nature_of_core_business_activity_code' => $details['nature_of_core_business_activity_code'] ?? null,
                'nature_of_core_business_activity_description' => $details['nature_of_core_business_activity_description'] ?? null,
                'annual_turnover_fy' => $details['annual_turnover_fy'] ?? null,
                'annual_turnover' => $details['annual_turnover'] ?? null,
                'date_of_registration' => $details['date_of_registration'] ?? null,
                'date_of_cancellation' => $details['date_of_cancellation'] ?? null,
                'state_jurisdiction' => $details['state_jurisdiction'] ?? null,
                'center_jurisdiction' => $details['center_jurisdiction'] ?? null,
                'aadhaar_validation' => $details['aadhaar_validation'] ?? null,
                'aadhaar_validation_date' => $details['aadhaar_validation_date'] ?? null,
                'einvoice_status' => $details['einvoice_status'] ?? null,
                'field_visit_conducted' => $details['field_visit_conducted'] ?? null,
                'gstin_status' => $details['gstin_status'] ?? null,
                'percentage_in_cash' => $details['percentage_in_cash'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Generate OTP
            $otp = rand(1000, 9999);

            DB::table('master_clients')->insert([
                'gst_number' => $gst_no,
                'business_legalname' => $legal_name,
                'promotors_name' => $promoters,
                'pan_number' => $pan_number,
                'phone_number' => $phone,
                'email' => $email,
                'address' => $address,
                'otp' => $otp,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($phone) {
                $this->sendOtp($phone, $otp);
            }

            return redirect()->route('signup_details')->with([
                'source' => 'api',
                'otp' => $otp,
                'gst_details' => [
                    'gst_number' => $gst_no,
                    'business_legalname' => $legal_name,
                    'promotors_name' => $promoters,
                    'pan_number' => $pan_number,
                    'phone_number' => $phone,
                    'email' => $email,
                    'address' => $address
                ]
            ]);
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'GST verification failed: ' . $e->getMessage());
        }
    }

    public function signup_details()
    {
        return view('web.auth.signup-gst-details');
    }

    // Helper function to send OTP
    private function sendOtp($phone, $otp)
    {
        try {
            $authKey = "3636736465636b35323233";
            $senderId = "DRDECK";
            $route = "2";
            $country = "91";
            $dltTeId = "1707175066512828187";
            $message = urlencode("Dear user, your EMD_Tenders registration OTP is $otp. Please do not share this with anyone. - EMD_Tenders");

            $url = "http://promo.smso2.com/api/sendhttp.php?authkey=$authKey&mobiles=$phone&message=$message&sender=$senderId&route=$route&country=$country&DLT_TE_ID=$dltTeId";

            $response = file_get_contents($url);

            Log::info("SMS sent to $phone. OTP: $otp. Response: $response");

            return true;
        } catch (\Exception $e) {
            Log::error("SMS sending failed for $phone: " . $e->getMessage());
            return false;
        }
    }

    public function signup_otp(Request $request)
    {
        $otp = $request->otp;
        $phone_number = $request->phone_number;
        $gst = $request->gst;
        return view('web.auth.signup-otp', compact('otp', 'phone_number', 'gst'));
    }

    // Step 2: Verify OTP
    // public function verify_otp(Request $request)
    // {
    //     $request->validate([
    //         'gst' => 'required|string',
    //         'otp' => 'required|digits:4'
    //     ]);

    //     $gst_no = strtoupper(trim($request->gst));
    //     $otp = $request->otp;

    //     $client = DB::table('master_clients')->where('gst_number', $gst_no)->first();

    //     if (!$client) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'GST record not found. Please verify GST first.'
    //         ], 404);
    //     }

    //     if ($client->otp != $otp) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid OTP. Please try again.'
    //         ], 400);
    //     }

    //     // Mark OTP as verified (optional: you can add a column otp_verified)
    //     DB::table('master_clients')
    //         ->where('gst_number', $gst_no)
    //         ->update([
    //             'otp_verified' => true,
    //             'updated_at' => now()
    //         ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'OTP verified successfully'
    //     ]);
    // }

    public function verify_otp(Request $request)
    {
        $request->validate([
            'gst' => 'required|',
            'otp' => 'required|digits:4',
            'phone_number' => 'required'
        ]);

        $gst_no = strtoupper(trim($request->gst));
        $phone_number = $request->phone_number;
        $otp = $request->otp;

        $client = DB::table('master_clients')->where('gst_number', $gst_no)->first();

        if (!$client) {
            return redirect()->back()->with('error', 'GST record not found. Please verify GST first.');
        }

        if ($client->otp != $otp) {
            return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
        }

        // Mark OTP as verified
        DB::table('master_clients')
            ->where('gst_number', $gst_no)
            ->update([
                'otp_verified' => true,
                'updated_at' => now()
            ]);

        // Redirect to signup-pass route with gst_no and phone_number

        session(['verified_gst' => $gst_no, 'phone_number' => $phone_number]);
        return redirect()->route('signup_pass');
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $phone = trim($request->phone_number);

        // 🔍 Check if client exists
        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found. Please register first.'
            ], 404);
        }

        $newOtp = rand(1000, 9999);


        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'otp' => $newOtp,
                'otp_verified' => false,
                'updated_at' => now(),
            ]);


        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully.',
            'otp' => $newOtp
        ]);
    }

    public function signup_pass()
    {
        $gst = session('verified_gst');
        $phone = session('phone_number');
        return view('web.auth.signup-pass', compact('gst', 'phone'));
    }

    public function set_password(Request $request)
    {
        $request->validate([
            'gst' => 'required',
            'password' => 'required',
            'phone' => 'required'
        ]);

        $gst_no = strtoupper(trim($request->gst));
        $phone = $request->phone;

        // Find client by GST
        $client = DB::table('master_clients')->where('gst_number', $gst_no)->first();

        if (!$client) {
            return redirect()->back()->with('error', 'GST record not found.');
        }

        // Update password only (Web doesn't need tokens)
        DB::table('master_clients')
            ->where('gst_number', $gst_no)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now()
            ]);

        // Redirect to login or next step
        return redirect()->route('signup_insights');
    }

    public function signup_insights()
    {
        $gst = session('verified_gst');
        $phone = session('phone_number');
        return view('web.auth.signup-insight', compact('gst', 'phone'));
    }

    // public function update_insights(Request $request)
    // {
    //     // Validation for required fields
    //     $request->validate([
    //         'projects' => 'required|string',
    //         'turn_over' => 'required|string',
    //         'contractor_type' => 'required|string',
    //         'challenge' => 'required|string',
    //         'gst' => 'nullable|string',
    //         'phone' => 'nullable|string',
    //     ]);

    //     $gst_no = strtoupper(trim($request->gst));
    //     $mobile = $request->phone;

    //     $client = null;

    //     if (!empty($gst_no)) {
    //         $client = DB::table('master_clients')
    //             ->where('gst_number', $gst_no)
    //             ->first();
    //     }

    //     if (!$client && !empty($mobile)) {
    //         $client = DB::table('master_clients')
    //             ->where('phone_number', $mobile)
    //             ->first();
    //     }

    //     // If no client found, redirect back
    //     if (!$client) {
    //         return redirect()->back()->with('error', 'Client not found.');
    //     }

    //     DB::table('master_clients')
    //         ->where('id', $client->id)
    //         ->update([
    //             'projects' => $request->projects,
    //             'turn_over' => $request->turn_over,
    //             'contractor_type' => $request->contractor_type,
    //             'challenge' => $request->challenge,
    //             'registration_completed' => true,
    //             'updated_at' => now(),
    //         ]);

    //     Auth::guard('client')->login($client);
    //     return redirect()->route('dashboard');
    // }

    public function update_insights(Request $request)
    {
        $request->validate([
            'projects' => 'required|string',
            'turn_over' => 'required|string',
            'contractor_type' => 'required|string',
            'challenge' => 'required|string',
            'gst' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $gst_no = strtoupper(trim($request->gst));
        $mobile = $request->phone;

        $client = null;

        if (!empty($gst_no)) {
            $client = MasterClient::where('gst_number', $gst_no)->first();
        }

        if (!$client && !empty($mobile)) {
            $client = MasterClient::where('phone_number', $mobile)->first();
        }

        // if (!$client) {
        //     return redirect()->back()->with('error', 'Client not found.');
        // }

        // $client->update([
        //     'projects' => $request->projects,
        //     'turn_over' => $request->turn_over,
        //     'contractor_type' => $request->contractor_type,
        //     'challenge' => $request->challenge,
        //     'registration_completed' => true,
        // ]);

        // $client = DB::table('master_clients')
        //     ->where('phone_number', $mobile)
        //     ->update([
        //         'projects' => $request->projects,
        //         'turn_over' => $request->turn_over,
        //         'contractor_type' => $request->contractor_type,
        //         'challenge' => $request->challenge,
        //         'registration_completed' => true,
        //     ]);

        // $mobile = $request->phone;

        // // Fetch client for login later
        // $client = MasterClient::where('phone_number', $mobile)->first();

        // if (!$client) {
        //     return back()->with('error', 'Client not found.');
        // }

        // Update using query builder (no fillable needed)
        DB::table('master_clients')
            ->where('phone_number', $mobile)
            ->update([
                'projects' => $request->projects,
                'turn_over' => $request->turn_over,
                'contractor_type' => $request->contractor_type,
                'challenge' => $request->challenge,
                'registration_completed' => true,
            ]);

        // Login must use model instance
        Auth::guard('client')->login($client);


        return redirect()->route('dashboard');
    }

    // without gst
    public  function signup_no_gst()
    {
        return view('web.auth.signup-no-gst');
    }

    public function signup_no_gst_detials(Request $request)
    {
        $phone = $request->phone_number;

        return view('web.auth.signup-no-gst-details', compact('phone'));
    }

    public function non_gst_submit(Request $request)
    {
        $otp = rand(1000, 9999);

        // Validate form inputs
        $request->validate([
            'phone_number' => 'required|string',
            'business_legalname' => 'required|string',
            'promotors_name' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        // Check if phone is already registered
        $exists = DB::table('master_clients')
            ->where('phone_number', $request->phone_number)
            ->first();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Mobile number already registered');
        }

        // Insert into master_clients
        DB::table('master_clients')->insert([
            'gst_number' => $request->gst_number,
            'phone_number' => $request->phone_number,
            'business_legalname' => $request->business_legalname,
            'promotors_name' => $request->promotors_name ?? '',
            'pan_number' => $request->pan_number ?? '',
            'email' => $request->email ?? '',
            'address' => $request->address ?? '',
            'otp' => $otp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert into master_clients_db
        DB::table('master_clients_db')->insert([
            'gst_no' => $request->gst_number,
            'business_name' => $request->business_legalname,
            'legal_name' => null,
            'pan_number' => $request->pan_number ?? '',
            'promoters' => $request->promotors_name ?? '',
            'address' => $request->address ?? '',
            'email' => $request->email ?? '',
            'phone_number' => $request->phone_number,
        ]);

        // Redirect to OTP page with data using session
        return redirect()
            ->route('signup_no_gst_otp')
            ->with('otp', $otp)
            ->with('phone_number', $request->phone_number);
    }

    public function signup_no_gst_otp()
    {
        $otp = session('otp');
        $phone = session('phone_number');
        return view('web.auth.signup-no-otp', compact('otp', 'phone'));
    }


    public function verify_mobile_otp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp' => 'required|digits:4'
        ]);

        $phone_number = trim($request->phone_number);
        $otp = $request->otp;

        // Find client
        $client = DB::table('master_clients')
            ->where('phone_number', $phone_number)
            ->first();

        if (!$client) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Phone number not found.');
        }

        // OTP mismatch
        if ($client->otp != $otp) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid OTP. Please try again.');
        }

        // Mark OTP as verified
        DB::table('master_clients')
            ->where('phone_number', $phone_number)
            ->update([
                'otp_verified' => true,
                'updated_at' => now()
            ]);

        // Redirect to next page (set password)
        return redirect()
            ->route('signup_no_set_pass')
            ->with('phone_number', $phone_number);
    }

    public function signup_no_set_pass()
    {
        $phone = session('phone_number');

        return view('web.auth.signup-no-pass', compact('phone'));
    }

    public function set_password_nogst(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        $phone = trim($request->phone_number);

        // Find client
        $client = DB::table('master_clients')
            ->where('phone_number', $phone)
            ->first();

        if (!$client) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Phone number not found.');
        }

        // Update password
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
                'otp_verified' => false, // reset OTP verification
                'otp' => null,           // clear OTP
            ]);

        return redirect()
            ->route('signup_no_insights')
            ->with('phone_number', $phone);

        // // Login user using Auth:client guard
        // Auth::guard('client')->loginUsingId($client->id);

        // // regenerate session to avoid fixation
        // $request->session()->regenerate();

        // // Redirect to dashboard
        // return redirect()->route('dashboard');
    }

    public function signup_no_insights()
    {
        $phone = session('phone_number');

        return view('web.auth.signup-no-insight', compact('phone'));
    }

    // public function update_no_insights(Request $request)
    // {
    //     $request->validate([
    //         'projects' => 'required|string',
    //         'turn_over' => 'required|string',
    //         'contractor_type' => 'required|string',
    //         'challenge' => 'required|string',
    //         'phone' => 'nullable|string',
    //     ]);

    //     $mobile = $request->phone;

    //     $client = MasterClient::where('phone_number', $mobile)->first();

    //     $client->update([
    //         'projects' => $request->projects,
    //         'turn_over' => $request->turn_over,
    //         'contractor_type' => $request->contractor_type,
    //         'challenge' => $request->challenge,
    //         'registration_completed' => true,
    //     ]);

    //     Auth::guard('client')->login($client);

    //     return redirect()->route('dashboard');
    // }

    public function update_no_insights(Request $request)
    {
        $mobile = $request->phone;

        // Fetch client for login later
        $client = MasterClient::where('phone_number', $mobile)->first();

        if (!$client) {
            return back()->with('error', 'Client not found.');
        }

        // Update using query builder (no fillable needed)
        DB::table('master_clients')
            ->where('phone_number', $mobile)
            ->update([
                'projects' => $request->projects,
                'turn_over' => $request->turn_over,
                'contractor_type' => $request->contractor_type,
                'challenge' => $request->challenge,
                'registration_completed' => true,
            ]);

        // Login must use model instance
        Auth::guard('client')->login($client);

        return redirect()->route('dashboard');
    }

    public function skip($id)
    {
        $client = MasterClient::where('phone_number', $id)->first();

        Auth::guard('client')->login($client);

        return redirect()->route('dashboard');
    }
}
