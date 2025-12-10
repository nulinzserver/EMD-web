<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientRegistrationController extends Controller
{
    // Step 1: Verify GST and Send OTP
    public function verifyGst(Request $request)
    {
        $request->validate([
            'gst_no' => 'required|string',
        ]);

        $gst_no = strtoupper(trim($request->gst_no));

        // Check if GST exists in master_clients_db
        $existing = DB::table('master_clients_db')->where('gst_no', $gst_no)->first();
        Log::info('above123 inserting into master_clients');
        if ($existing) {
            // GST found in local database
            // Generate OTP
            $otp = rand(1000, 9999);
            Log::info('Before inserting into master_clients');
            // Store/Update in master_clients table with OTP
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
                    'updated_at' => now(),
                ]
            );

            Log::info('Inserted into master_clients successfully');
            // Send OTP
            $phone = $existing->phone_number;
            if ($phone) {
                $this->sendOtp($phone, $otp);
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
                'source' => 'local',
                'message' => 'GST verified from local database',
                'otp' => $otp,
                'data' => [
                    'gst_number' => $existing->gst_no,
                    'business_legalname' => $existing->legal_name,
                    'promotors_name' => $existing->promoters,
                    'pan_number' => $existing->pan_number,
                    'phone_number' => $existing->phone_number,
                    'email' => $existing->email,
                    'address' => $existing->address,
                ],
            ]);
        }

        // GST not found locally - Fetch from IDFY API
        $group_id = 'ec33ab9a-6ebb-46a7-b87d-3966673f1214';
        $task_id = '8abc6431-fc08-4594-bc8d-090df206f15c';

        $payload = [
            'group_id' => $group_id,
            'task_id' => $task_id,
            'data' => [
                'gstnumber' => $gst_no,
                'isdetails' => true,
            ],
        ];

        $client = new Client;

        try {
            $response = $client->post('https://eve.idfy.com/v3/tasks/async/retrieve/gst_info', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'api-key' => 'ccb9fc21-aa17-424b-8b45-6627a571d679',
                    'account-id' => 'cad6ab7d7e57/871863ea-879e-4772-9879-b92b12500392',
                ],
                'json' => $payload,
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $requestId = $res['request_id'] ?? null;

            if (! $requestId) {
                return response()->json(['success' => false, 'message' => 'Request ID not found'], 400);
            }

            sleep(7); // Wait for async response

            $pollResponse = $client->get('https://eve.idfy.com/v3/tasks', [
                'headers' => [
                    'Accept' => 'application/json',
                    'api-key' => 'ccb9fc21-aa17-424b-8b45-6627a571d679',
                    'account-id' => 'cad6ab7d7e57/871863ea-879e-4772-9879-b92b12500392',
                ],
                'query' => [
                    'request_id' => $requestId,
                ],
            ]);

            $data = json_decode($pollResponse->getBody()->getContents(), true);
            $details = $data[0]['result']['details'] ?? null;

            if (! $details) {
                return response()->json(['success' => false, 'message' => 'No GST details found'], 404);
            }

            $promoters = isset($details['promoters']) ? implode(', ', $details['promoters']) : null;
            $phone = $details['contact_details']['principal']['mobile'] ?? null;
            $email = $details['contact_details']['principal']['email'] ?? null;
            $address = $details['contact_details']['principal']['address'] ?? null;
            $legal_name = $details['legal_name'] ?? null;
            $pan_number = $details['pan_number'] ?? null;

            // Insert into master_clients_db (complete details)
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
                'updated_at' => now(),
            ]);

            // Generate OTP
            $otp = rand(1000, 9999);

            // Store basic info in master_clients table with OTP
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
                'updated_at' => now(),
            ]);

            // Send OTP
            if ($phone) {
                $this->sendOtp($phone, $otp);
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
                'source' => 'api',
                'message' => 'GST verified from API',
                'otp' => $otp,
                'data' => [
                    'gst_number' => $gst_no,
                    'business_legalname' => $legal_name,
                    'promotors_name' => $promoters,
                    'pan_number' => $pan_number,
                    'phone_number' => $phone,
                    'email' => $email,
                    'address' => $address,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('GST verification failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'GST verification failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Step 2: Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'gst_number' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        $gst_no = strtoupper(trim($request->gst_number));
        $otp = $request->otp;

        $client = DB::table('master_clients')->where('gst_number', $gst_no)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'GST record not found. Please verify GST first.',
            ], 404);
        }

        if ($client->otp != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 400);
        }

        // Mark OTP as verified (optional: you can add a column otp_verified)
        DB::table('master_clients')
            ->where('gst_number', $gst_no)
            ->update([
                'otp_verified' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
        ]);
    }

    // Step 3: Set Password
    public function setPassword(Request $request)
    {
        $request->validate([
            'gst_number' => 'required|string',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
            'device_token' => 'nullable|string', // ✅ for FCM token
        ]);

        $gst_no = strtoupper(trim($request->gst_number));
        $fcmToken = $request->input('device_token'); // ✅ read from device_token key

        // 🔹 Find the client by GST number
        $client = DB::table('master_clients')->where('gst_number', $gst_no)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'GST record not found',
            ], 404);
        }

        // 🔹 Generate login token (same format as login API)
        $token = base64_encode($client->id.'|'.time().'|'.bin2hex(random_bytes(16)));

        // 🔹 Update password, token, and FCM token
        DB::table('master_clients')
            ->where('gst_number', $gst_no)
            ->update([
                'password' => Hash::make($request->password),
                'fcm_token' => $fcmToken,
                'login_token' => $token,
                'token_created_at' => now(),
                'updated_at' => now(),
            ]);

        // 🔹 Build response data
        $responseData = [
            'mc_id' => $client->id,
            'token' => $token,
            'image' => property_exists($client, 'image') ? $client->image : null,
            'emp_code' => property_exists($client, 'emp_code') ? $client->emp_code : null,
            'device_token' => $fcmToken,
        ];

        // 🔹 Send response
        return response()->json([
            'success' => true,
            'message' => 'Password set successfully',
            'data' => $responseData,
        ]);
    }

    // Step 4: Complete Registration (Additional Details)
    public function completeRegistration(Request $request)
    {

        log::info('complete registration called'.json_encode($request->all()));

        // dd(12345);
        $request->validate([
            'gst_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'projects' => 'required|string',
            'turn_over' => 'required|string',
            'contractor_type' => 'required|string',
            'challenge' => 'required|string',
        ]);

        $gst_no = strtoupper(trim($request->gst_number));
        $mobile = trim($request->mobile);

        // 1️⃣ Check using GST number if provided
        $client = null;

        if (! empty($gst_no)) {
            $client = DB::table('master_clients')
                ->where('gst_number', $gst_no)
                ->first();
        }

        // 2️⃣ If no GST match → check mobile number
        if (! $client && ! empty($mobile)) {
            $client = DB::table('master_clients')
                ->where('phone_number', $mobile)
                ->first();
        }

        // 3️⃣ If both GST and mobile fail
        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'No record found for GST or Mobile number',
            ], 404);
        }

        // 4️⃣ Update additional details
        DB::table('master_clients')
            ->where('id', $client->id)
            ->update([
                'projects' => $request->projects,
                'turn_over' => $request->turn_over,
                'contractor_type' => $request->contractor_type,
                'challenge' => $request->challenge,
                'registration_completed' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully',
        ]);
    }

    // Helper function to send OTP
    private function sendOtp($phone, $otp)
    {
        try {
            $authKey = '3636736465636b35323233';
            $senderId = 'DRDECK';
            $route = '2';
            $country = '91';
            $dltTeId = '1707175066512828187';
            $message = urlencode("Dear user, your EMD_Tenders registration OTP is $otp. Please do not share this with anyone. - EMD_Tenders");

            $url = "http://promo.smso2.com/api/sendhttp.php?authkey=$authKey&mobiles=$phone&message=$message&sender=$senderId&route=$route&country=$country&DLT_TE_ID=$dltTeId";

            $response = file_get_contents($url);

            Log::info("SMS sent to $phone. OTP: $otp. Response: $response");

            return true;
        } catch (\Exception $e) {
            Log::error("SMS sending failed for $phone: ".$e->getMessage());

            return false;
        }
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $phone = trim($request->phone_number);

        // 🔍 Check if client exists
        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found. Please register first.',
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
            'otp' => $newOtp,
        ]);
    }

    // Step 5: Edit Profile (only phone number editable)
    public function editProfile(Request $request)
    {
        $request->validate([
            'gst_number' => 'nullable|string',
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        $gst_no = strtoupper(trim($request->gst_number));

        // Fetch the client
        $client = DB::table('master_clients')->where('gst_number', $gst_no)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'GST record not found',
            ], 404);
        }

        // Update only the phone number
        DB::table('master_clients')
            ->where('gst_number', $gst_no)
            ->update([
                'phone_number' => $request->phone_number,
                'updated_at' => now(),
            ]);

        // Fetch updated data to return
        $updatedClient = DB::table('master_clients')
            ->select('business_legalname', 'promotors_name', 'pan_number', 'phone_number', 'address')
            ->where('gst_number', $gst_no)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'data' => $updatedClient,
        ]);
    }

    public function getProfile(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch client details based on mc_id
        $client = DB::table('master_clients')
            ->where('id', $mcId)
            ->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        // Fetch additional details from master_clients_db based on gst_number
        $clientDb = DB::table('master_clients_db')
            ->where('gst_no', $client->gst_number)
            ->first();

        // Extract city, state, and pincode from address
        $city = null;
        $state = null;
        $pincode = null;

        if ($clientDb && $clientDb->address) {
            // Extract pincode (6 digits at the end)
            if (preg_match('/(\d{6})/', $clientDb->address, $matches)) {
                $pincode = $matches[1];
            }

            // Split address by comma
            $addressParts = array_map('trim', explode(',', $clientDb->address));
            $totalParts = count($addressParts);

            // Typically format: ..., City, State, Pincode
            if ($totalParts >= 3) {
                $pincode = $pincode ?? end($addressParts); // Last part
                $state = $addressParts[$totalParts - 2]; // Second last
                $city = $addressParts[$totalParts - 3]; // Third last
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully',
            'data' => [
                'id' => $client->id,
                'business_legalname' => $client->business_legalname,
                'promotors_name' => $client->promotors_name,
                'gst_number' => $client->gst_number,
                'pan_number' => $client->pan_number,
                'phone_number' => $client->phone_number,
                'email' => $client->email ?? null,
                'address' => $client->address,
                'business_type' => $clientDb->nature_of_business ?? null,
                'register_date' => $clientDb->date_of_registration ?? null,
                'income' => $clientDb->annual_turnover ?? null,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at,
            ],
        ]);
    }

    // public function getProfile(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|integer'
    //     ]);

    //     $mcId = $this->validateTokenAndGetMcId($request);

    //     if (!$mcId) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid or expired token. Please login again.'
    //         ], 401);
    //     }

    //     // Fetch client details based on mc_id and user_id
    //     $client = DB::table('master_clients')
    //         ->where('id', $mcId)
    //         ->first();

    //     if (!$client) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Client not found'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Profile fetched successfully',
    //         'data' => [
    //             'id' => $client->id,
    //             'business_legalname' => $client->business_legalname,
    //             'promotors_name' => $client->promotors_name,
    //             'gst_number' => $client->gst_number,
    //             'pan_number' => $client->pan_number,
    //             'phone_number' => $client->phone_number,
    //             'email' => $client->email ?? null,
    //             'address' => $client->address,
    //             'created_at' => $client->created_at,
    //             'updated_at' => $client->updated_at
    //         ]
    //     ]);
    // }
    public function updateProfilePhone(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch the client for this logged-in master client
        $client = DB::table('master_clients')->where('id', $mcId)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        // Update only the phone number
        DB::table('master_clients')
            ->where('id', $mcId)
            ->update([
                'phone_number' => $request->phone_number,
                'updated_at' => now(),
            ]);

        // Fetch updated profile
        $updatedClient = DB::table('master_clients')
            ->select('business_legalname', 'promotors_name', 'gst_number', 'pan_number', 'phone_number', 'address')
            ->where('id', $mcId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'data' => $updatedClient,
        ]);
    }

    // Helper function: validate token and get mc_id
    private function validateTokenAndGetMcId(Request $request)
    {
        $token = $request->header('Authorization');

        Log::info('token', ['token' => $token]);

        if (! $token) {
            return null;
        }

        // Remove 'Bearer ' prefix if present
        $token = str_replace('Bearer ', '', $token);

        try {
            // Decode token to get mc_id
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);

            if (count($parts) < 2) {
                return null;
            }

            $mcId = $parts[0];

            // Verify token exists in database
            $client = DB::table('master_clients')
                ->where('id', $mcId)
                ->where('login_token', $token)
                ->first();

            if (! $client) {
                return null;
            }

            return $mcId;
        } catch (\Exception $e) {
            Log::error('Token validation failed: '.$e->getMessage());

            return null;
        }
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|min:10|max:15',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:50',        // optional, default 'user'
            'permission' => 'nullable|string',          // optional, JSON or CSV
        ]);

        // Get mc_id from token
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        try {
            $userId = DB::table('add_user')->insertGetId([
                'mc_id' => $mcId,
                'name' => $request->name,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($request->password),
                'role' => $request->input('role', 'user'),
                'permission' => $request->input('permission', null),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User added successfully',
                'data' => [
                    'id' => $userId,
                    'mc_id' => $mcId,
                    'name' => $request->name,
                    'mobile_number' => $request->mobile_number,
                    'role' => $request->input('role', 'user'),
                    'permission' => $request->input('permission', null),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add user: '.$e->getMessage(),
            ], 500);
        }
    }

    // Step 7: List all users for logged-in master client
    public function listUsers(Request $request)
    {
        // Get mc_id from token
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch users created by this master client
        $users = DB::table('add_user')
            ->select('id', 'name', 'mobile_number', 'role', 'permission', 'status', 'created_at', 'updated_at')
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User list fetched successfully',
            'data' => $users,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        // Get mc_id from token
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch current user
        $client = DB::table('master_clients')->where('id', $mcId)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check if old password is correct
        if (! Hash::check($request->old_password, $client->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password is incorrect',
            ], 400);
        }

        // Update password
        DB::table('master_clients')
            ->where('id', $mcId)
            ->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    public function forgotPasswordSendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        $phone = $request->phone_number;

        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found',
            ], 404);
        }

        // Generate OTP
        $otp = rand(1000, 9999);

        // Store OTP in master_clients table
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'otp' => $otp,
                'otp_verified' => false,
                'updated_at' => now(),
            ]);

        // Send OTP dynamically using your existing helper
        $sent = $this->sendOtp($phone, $otp);

        if (! $sent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }

        // ✅ Return OTP in response
        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your mobile number',
            'data' => [
                'otp' => $otp,
            ],
        ]);
    }

    // Step 2: Verify OTP
    public function forgotPasswordVerifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
            'otp' => 'required|digits:4',
        ]);

        $phone = $request->phone_number;
        $otp = $request->otp;

        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found',
            ], 404);
        }

        if ($client->otp != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
                'data' => [
                    'entered_otp' => $otp,
                    'expected_otp' => $client->otp,
                ],
            ], 400);
        }

        // Mark OTP as verified
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'otp_verified' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'otp' => $otp,
            ],
        ]);
    }

    // Step 3: Reset Password
    public function forgotPasswordReset(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        $phone = $request->phone_number;

        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found',
            ], 404);
        }

        if (! $client->otp_verified) {
            return response()->json([
                'success' => false,
                'message' => 'OTP not verified. Please verify OTP first.',
            ], 400);
        }

        // Reset password
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'password' => Hash::make($request->new_password),
                'otp_verified' => false, // reset OTP verification
                'otp' => null,           // clear OTP
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    public function dashboardStats(Request $request)
    {

        // 1️⃣ Get mc_id from login token
        $mcId = $this->validateTokenAndGetMcId($request);

        log::info('dashboard', ['mc_id' => $mcId]);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        try {
            // 2️⃣ Get turn_over
            $turnValue = DB::table('tender_bill')
                ->where('mc_id', $mcId)
                ->sum('total_amount');

            // 3️⃣ Count clients
            $clientsCount = DB::table('master_clients_sync')
                ->where('mc_id', $mcId)
                ->count();

            // 4️⃣ Count tenders
            $tendersCount = DB::table('master_tender')
                ->where('mc_id', $mcId)
                ->count();

            $totalAmount = DB::table('tender_bill')
                ->where('mc_id', $mcId)
                ->sum('total_amount');

            $withHeld = DB::table('tender_bill')
                ->where('mc_id', $mcId)
                ->sum('withheld_amount');

            $otherAmount = DB::table('tender_bill')
                ->where('mc_id', $mcId)
                ->sum('others_amount');

            // 5️⃣ Get upcoming collections
            $today = date('Y-m-d');
            $baseUrl = url('/'); // Base URL without 'public'

            $upcomingCollections = DB::table('tender_collection as tc')
                ->leftJoin('master_tender as mt', 'tc.t_id', '=', 'mt.id')
                ->where('tc.mc_id', $mcId)
                ->where('tc.collection_date', '>', $today)
                ->orderBy('tc.collection_date', 'asc')
                ->select(
                    'tc.*',
                    'mt.project_name',
                    'mt.contractor'
                )
                ->get();

            // Convert attachment to full URL
            foreach ($upcomingCollections as $c) {
                if (! empty($c->attachment)) {
                    $c->attachment = $baseUrl.'/'.ltrim($c->attachment, '/');
                }
            }

            // 6️⃣ Return final response
            return response()->json([
                'success' => true,
                'message' => 'Dashboard stats fetched successfully',
                'data' => [
                    'turn_over' => $turnValue,
                    'clients_count' => $clientsCount,
                    'tenders_count' => $tendersCount,
                    'upcoming_collections' => [
                        'count' => count($upcomingCollections),
                        'records' => $upcomingCollections,
                    ],
                    'emd' => $totalAmount,
                    'withheld' => $withHeld,
                    'other receivable' => $otherAmount,
                    'auditor_update' => [
                        'project_name' => 'null',
                        'amount' => 'null',
                    ],

                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats: '.$e->getMessage(),
            ], 500);
        }
    }

    public function addSubscription(Request $request)
    {
        // 1️⃣ Get logged-in user's mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // 2️⃣ Validate request input
        $request->validate([
            'application_pay' => 'nullable|numeric|min:0',
            'whatsapp' => 'nullable|numeric|min:0',
            'users' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
        ]);

        // 3️⃣ Prepare data to insert
        $subscriptionData = [
            'mc_id' => $mcId,
            'application_pay' => $request->input('application_pay', 0),
            'whatsapp' => $request->input('whatsapp', 0),
            'users' => $request->input('users', 0),
            'total' => $request->input('total', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // 4️⃣ Insert into subscription table
        $subscriptionId = DB::table('subscription')->insertGetId($subscriptionData);

        // 5️⃣ Return success response
        return response()->json([
            'success' => true,
            'message' => 'Subscription added successfully',
            'data' => [
                'subscription_id' => $subscriptionId,
                'mc_id' => $mcId,
                'application_pay' => $subscriptionData['application_pay'],
                'whatsapp' => $subscriptionData['whatsapp'],
                'users' => $subscriptionData['users'],
                'total' => $subscriptionData['total'],
                'created_at' => $subscriptionData['created_at'],
                'updated_at' => $subscriptionData['updated_at'],
            ],
        ]);
    } // add this at top with other imports

    public function getSubscriptionStatus(Request $request)
    {
        // 1️⃣ Validate input
        $request->validate([
            'mc_id' => 'required|integer',
        ]);

        $mcId = $request->mc_id;

        // 2️⃣ Check if subscription exists
        $subscription = DB::table('subscription')
            ->where('mc_id', $mcId)
            ->latest('created_at')
            ->first();

        // 3️⃣ Determine status
        $isSubscribed = $subscription ? true : false;
        $statusText = $isSubscribed ? 'subscribed' : 'not subscribed';

        // 4️⃣ Return response
        return response()->json([
            'success' => true,
            'mc_id' => $mcId,
            'subscription_status' => $statusText,
            'status' => $isSubscribed,
        ]);
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png',
            'logo' => 'required|image|mimes:jpg,jpeg,png',
        ]);

        $mcId = $this->validateTokenAndGetMcId($request);
        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        try {
            $folderPath = public_path('signature');
            if (! File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0777, true);
            }

            // Signature upload
            $file = $request->file('signature');
            $signatureFilename = 'signature_'.$mcId.'_'.time().'.'.$file->getClientOriginalExtension();
            $signaturePath = 'signature/'.$signatureFilename;
            $file->move($folderPath, $signatureFilename);

            // Logo upload
            $logoFile = $request->file('logo');
            $logoFilename = 'logo_'.$mcId.'_'.time().'.'.$logoFile->getClientOriginalExtension();
            $logoPath = 'signature/'.$logoFilename;
            $logoFile->move($folderPath, $logoFilename);

            // Delete old record
            DB::table('signature_uploads')->where('mc_id', $mcId)->delete();

            // Insert record
            DB::table('signature_uploads')->insert([
                'mc_id' => $mcId,
                'signature' => $signaturePath,
                'logo' => $logoPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signature uploaded successfully',
                'data' => [
                    'signature_url' => asset('public/'.$signaturePath),
                    'logo_url' => asset('public/'.$logoPath),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload signature: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getSignature(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);
        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $record = DB::table('signature_uploads')->where('mc_id', $mcId)->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'No signature found for this account.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Signature fetched successfully',
            'data' => [
                'signature_url' => asset('public/'.$record->signature),
                'logo_url' => $record->logo ? asset('public/'.$record->logo) : null,
            ],
        ]);
    }

    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'logo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $mcId = $this->validateTokenAndGetMcId($request);
        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        try {
            $existing = DB::table('signature_uploads')->where('mc_id', $mcId)->first();

            $folderPath = public_path('signature');
            if (! File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0777, true);
            }

            // Delete old files
            if ($existing) {
                if (File::exists(public_path($existing->signature))) {
                    File::delete(public_path($existing->signature));
                }
                if (File::exists(public_path($existing->logo))) {
                    File::delete(public_path($existing->logo));
                }
            }

            // New signature
            $file = $request->file('signature');
            $signatureFilename = 'signature_'.$mcId.'_'.time().'.'.$file->getClientOriginalExtension();
            $signaturePath = 'signature/'.$signatureFilename;
            $file->move($folderPath, $signatureFilename);

            // New logo
            $logoFile = $request->file('logo');
            $logoFilename = 'logo_'.$mcId.'_'.time().'.'.$logoFile->getClientOriginalExtension();
            $logoPath = 'signature/'.$logoFilename;
            $logoFile->move($folderPath, $logoFilename);

            // Update DB record
            DB::table('signature_uploads')->updateOrInsert(
                ['mc_id' => $mcId],
                [
                    'signature' => $signaturePath,
                    'logo' => $logoPath,
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Signature and logo updated successfully',
                'data' => [
                    'signature_url' => asset('public/'.$signaturePath),
                    'logo_url' => asset('public/'.$logoPath),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update signature: '.$e->getMessage(),
            ], 500);
        }
    }

    public function editUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $user = DB::table('add_user')
            ->select('id', 'name', 'mobile_number', 'role', 'permission')
            ->where('mc_id', $mcId)
            ->where('id', $request->user_id)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User fetched successfully',
            'data' => $user,
        ]);
    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|min:10|max:15',
            'role' => 'required|string|max:50',
            'permission' => 'nullable|string',
        ]);

        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $user = DB::table('add_user')
            ->where('mc_id', $mcId)
            ->where('id', $request->user_id)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $updateData = [
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'role' => $request->input('role', $user->role),
            'permission' => $request->input('permission', $user->permission),
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('add_user')
            ->where('id', $request->user_id)
            ->where('mc_id', $mcId)
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => array_merge(['id' => $request->user_id, 'mc_id' => $mcId], $updateData),
        ]);
    }

    public function updateUserStatus(Request $request)
    {
        // Validate request
        $request->validate([
            'user_id' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Check if user exists
        $user = DB::table('add_user')
            ->where('mc_id', $mcId)
            ->where('id', $request->user_id)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        try {
            // Update user status
            DB::table('add_user')
                ->where('id', $request->user_id)
                ->where('mc_id', $mcId)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'data' => [
                    'user_id' => $request->user_id,
                    'mc_id' => $mcId,
                    'status' => $request->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status: '.$e->getMessage(),
            ], 500);
        }
    }

    public function uploadProfileImage(Request $request)
    {
        // Validate file input
        $request->validate([
            'profile_image' => 'required|image|mimes:jpg,jpeg,png', // max 2MB
        ]);

        // Validate token and get mc_id (login user)
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        try {
            // Get file
            $file = $request->file('profile_image');

            // Create folder path (inside public/uploads/profile_images)
            $folderPath = public_path('uploads/profile_images');

            // Ensure folder exists
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            // Generate filename
            $filename = 'profile_'.$mcId.'_'.time().'.'.$file->getClientOriginalExtension();

            // Move file to folder
            $file->move($folderPath, $filename);

            // Store path (relative to public)
            $filePath = '/uploads/profile_images/'.$filename;

            // Update master_clients table
            DB::table('master_clients')
                ->where('id', $mcId)
                ->update([
                    'profile_image' => $filePath,
                    'updated_at' => now(),
                ]);

            // Fetch updated record
            $client = DB::table('master_clients')
                ->select('id', 'business_legalname', 'promotors_name', 'phone_number', 'profile_image')
                ->where('id', $mcId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'data' => [
                    'profile_image_url' => asset($filePath),
                    // 'client' => $client,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile image: '.$e->getMessage(),
            ], 500);
        }
    }

    public function schemeList()
    {
        // 🔹 Fetch unique scheme names from master_tender table
        $schemes = DB::table('master_tender')
            ->whereNotNull('scheme')
            ->where('scheme', '!=', '')
            ->distinct()
            ->pluck('scheme');

        if ($schemes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No schemes found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Scheme list fetched successfully.',
            'data' => $schemes,
        ]);
    }

    public function locationList()
    {
        // 🔹 Fetch distinct location values
        $locations = DB::table('master_tender')
            ->whereNotNull('location')       // exclude NULLs
            ->where('location', '!=', '')    // exclude empty strings
            ->distinct()
            ->pluck('location');             // get only the 'location' column

        if ($locations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No locations found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Location list fetched successfully.',
            'data' => $locations,
        ]);
    }

    // Without GST Clients

    public function storeClientDetails(Request $request)
    {
        $otp = rand(1000, 9999);
        $request->validate([
            'phone_number' => 'required|string',
            'business_legalname' => 'required|string',
            'promotors_name' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        try {

            $exists = DB::table('master_clients')
                ->where('phone_number', $request->phone_number)
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile number already registered',
                ], 409); // 409 = conflict
            }

            $data = [
                'gst_number' => $request->gst,
                'phone_number' => $request->phone_number,
                'business_legalname' => $request->business_legalname,
                'promotors_name' => $request->promotors_name ?? '',
                'pan_number' => $request->pan_number ?? '',
                'email' => $request->email ?? '',
                'address' => $request->address ?? '',
                'otp' => $otp,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('master_clients')->insert($data);

            DB::table('master_clients_db')->insert([
                'gst_no' => null,
                'business_name' => $request->business_legalname,
                'legal_name' => null,
                'pan_number' => $request->pan_number ?? '',

                'promoters' => $request->promotors_name ?? '',
                'address' => $request->address ?? '',
                'email' => $request->email ?? '',
                'phone_number' => $request->phone_number,

            ]);

            return response()->json([
                'success' => true,
                'message' => 'User details saved successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save details: '.$e->getMessage(),
            ], 500);
        }
    }

    public function verifyMobileOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        try {
            // $phone_number = strtoupper(trim($request->phone_number));
            $phone_number = $request->phone_number;
            $otp = $request->otp;

            $client = DB::table('master_clients')->where('phone_number', $phone_number)->first();

            if (! $client) {
                return response()->json([
                    'success' => false,
                    'message' => 'PhoneNumber record not found. Please verify PhoneNumber first.',
                ], 404);
            }

            if ($client->otp != $otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP. Please try again.',
                ], 400);
            }

            // Mark OTP as verified (optional: you can add a column otp_verified)
            DB::table('master_clients')
                ->where('phone_number', $phone_number)
                ->update([
                    'otp_verified' => true,
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            Log::info('OTP error', ['otp_error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
        ]);
    }

    public function setPasswordMobile(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
            'device_token' => 'nullable|string',
        ]);

        $phone = trim($request->phone_number);
        $fcmToken = $request->input('device_token');

        // 🔹 Find client by phone number
        $client = DB::table('master_clients')->where('phone_number', $phone)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number record not found',
            ], 404);
        }

        // 🔹 Generate login token
        $token = base64_encode($client->id.'|'.time().'|'.bin2hex(random_bytes(16)));

        // 🔹 Update password, token, and FCM token
        DB::table('master_clients')
            ->where('phone_number', $phone)
            ->update([
                'password' => Hash::make($request->password),
                'fcm_token' => $fcmToken,
                'login_token' => $token,
                'token_created_at' => now(),
                'updated_at' => now(),
            ]);

        // 🔹 Build response
        $responseData = [
            'mc_id' => $client->id,
            'token' => $token,
            'image' => property_exists($client, 'image') ? $client->image : null,
            'emp_code' => property_exists($client, 'emp_code') ? $client->emp_code : null,
            'device_token' => $fcmToken,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Password set successfully',
            'data' => $responseData,
        ]);
    }
}
