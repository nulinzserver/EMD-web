<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Fcm;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientSyncController extends Controller
{
    // update popup
    public function update_popup()
    {

        return response()->json(['version' => '0.0.1']);
    }

    // Step 1: Verify GST and Store in Sync Table
    public function verifyGstSync(Request $request)
    {

        $request->validate([
            'gst_no' => 'required|string',
        ]);

        $gst_no = strtoupper(trim($request->gst_no));

        // Check if GST exists in master_clients_db
        $existing = DB::table('master_clients_db')->where('gst_no', $gst_no)->first();

        if ($existing) {
            // GST found in local database
            // Parse address to extract city and pincode
            $addressParts = $this->parseAddress($existing->address);

            // Use state from address parsing, fallback to state_jurisdiction if not found
            $state = $addressParts['state'] ?? ($existing->state_jurisdiction ?? null);

            // Insert into master_clients_sync
            $syncId = DB::table('master_clients_sync')->insertGetId([
                'mc_db_id' => $existing->id,
                'gst_no' => $existing->gst_no,
                'business_legalname' => $existing->legal_name,
                'business_type' => $existing->constitution_of_business,
                'register_date' => $existing->date_of_registration,
                'promotors_name' => $existing->promoters,
                'income_annual' => $existing->annual_turnover,
                'pan_no' => $existing->pan_number,
                'phone_number' => $existing->phone_number,
                'email' => $existing->email,
                'address' => $existing->address,
                'city' => $addressParts['city'],
                'state' => $state,
                'pincode' => $addressParts['pincode'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'source' => 'local',
                'message' => 'GST verified from local database',
                'sync_id' => $syncId,
                'data' => [
                    'gst_no' => $existing->gst_no,
                    'business_legalname' => $existing->legal_name,
                    'business_type' => $existing->constitution_of_business,
                    'register_date' => $existing->date_of_registration,
                    'promotors_name' => $existing->promoters,
                    'income_annual' => $existing->annual_turnover,
                    'pan_no' => $existing->pan_number,
                    'phone_number' => $existing->phone_number,
                    'email' => $existing->email,
                    'address' => $existing->address,
                    'city' => $addressParts['city'],
                    'state' => $addressParts['state'],
                    'pincode' => $addressParts['pincode'],
                ],
            ]);
        }

        // GST not found locally - Fetch from IDFY API
        return $this->fetchFromApi($gst_no);
    }

    // public function syncByPhone(Request $request)
    // {
    //     // Validate input
    //     $request->validate([
    //         'phone_number' => 'required|string',
    //     ]);

    //     $phone = trim($request->phone_number);

    //     $client = DB::table('master_clients_db')->where('phone_number', $phone)->first();

    //     if (!$client) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Phone number not found in master_clients_db'
    //         ], 404);
    //     }

    //     $addressParts = $this->parseAddress($client->address);

    //     $state = $addressParts['state'] ?? ($client->state_jurisdiction ?? null);

    //     $syncId = DB::table('master_clients_sync')->insertGetId([
    //         'mc_db_id' => $client->id,
    //         'gst_no' => $client->gst_no,
    //         'business_legalname' => $client->legal_name,
    //         'business_type' => $client->constitution_of_business,
    //         'register_date' => $client->date_of_registration,
    //         'promotors_name' => $client->promoters,
    //         'income_annual' => $client->annual_turnover,
    //         'pan_no' => $client->pan_number,
    //         'phone_number' => $client->phone_number,
    //         'email' => $client->email,
    //         'address' => $client->address,
    //         'city' => $addressParts['city'] ?? null,
    //         'state' => $state,
    //         'pincode' => $addressParts['pincode'] ?? null,
    //         'created_at' => now(),
    //         'updated_at' => now()
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Record synced successfully',
    //         'sync_id' => $syncId,
    //         'data' => [
    //             'gst_no' => null,
    //             'business_legalname' => $request->business_legalname,
    //             'business_type' => null,
    //             'register_date' => null,
    //             'promotors_name' => $request->promotors_name,
    //             'income_annual' => null,
    //             'pan_no' => $request->pan_number,
    //             'phone_number' => $request->phone_number,
    //             'email' => $request->email,
    //             'address' => $request->address,
    //             'city' => $request->city,
    //             'state' => $request->state,
    //             'pincode' => $request->pincode
    //         ]
    //     ]);
    // }

    public function saveClientByPhone(Request $request)
    {

        // log::info($request->all());

        try {
            // Validate input
            $request->validate([
                'user_phone' => 'required',
                'client_phone' => 'required',
                'business_legalname' => 'required|string',
                'promotors_name' => 'nullable|string',
                'pan_number' => 'nullable|string',
                'email' => 'nullable|string',
                'nick_name' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'pincode' => 'nullable|string',
            ]);

            $userid = DB::table('master_clients')
                ->where('phone_number', $request->user_phone)
                ->first();

            // // STEP 1: Check login user phone in master_clients
            $user = DB::table('master_clients_db')
                ->where('phone_number', $request->client_phone)
                ->first();

            // if ((! $user) && ($request->gst_no != null)) {
            // return response()->json([
            //     'success' => false,
            //     'message' => 'User phone number not found in master_clients',
            // ], 404);

            // if (isset($client->gst_no)) {
            //     $gstNo = $client->gst_no;
            // } else {
            //     $gstNo = null; // or default
            // }

            $master_client_db = DB::table('master_clients_db')->insertGetId([
                'gst_no' => $request->gst_no ?? 0,
                'legal_name' => $request->business_legalname,
                'promoters' => $request->promotors_name,
                'pan_number' => $request->pan_number,
                'phone_number' => $request->client_phone,        // SAVE CLIENT NUMBER
                'email' => $request->email,
                'address' => $request->address,
                'status' => 'without',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // }

            // STEP 2: Save CLIENT details (NOT login user number)
            $syncId = DB::table('master_clients_sync')->insertGetId([
                'mc_id' => $userid->id ?? null,
                'mc_db_id' => $master_client_db ?? null,                         // link to master_clients
                'business_legalname' => $request->business_legalname,
                'promotors_name' => $request->promotors_name,
                'pan_no' => $request->pan_number,
                'phone_number' => $request->client_phone,        // SAVE CLIENT NUMBER
                'email' => $request->email,
                'nick_name' => $request->nick_name,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'status' => 'without',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (\Exception $e) {
            log::info('error', ['error' => $e->getMessage(), 'Line' => $e->getLine()]);
        }

        // STEP 3: Response
        return response()->json([
            'success' => true,
            'message' => 'Client saved successfully',
            // 'sync_id' => $syncId,
            'data' => [
                'mc_db_id' => $user->id ?? null,
                'business_legalname' => $request->business_legalname,
                'promotors_name' => $request->promotors_name,
                'pan_no' => $request->pan_number,
                'client_phone' => $request->client_phone,
                'email' => $request->email,
                'address' => $request->address,
                'nick_name' => $request->nick_name,

                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,

            ],
        ]);
    }

    public function getByDbId($mc_db_id)
    {
        // Fetch data
        $data = DB::table('master_clients_sync')
            ->where('mc_db_id', $mc_db_id)
            ->get();

        // Count records
        $count = $data->count();

        return response()->json([
            'status' => true,
            'mc_db_id' => $mc_db_id,
            'count' => $count,
            'data' => $data,
        ]);
    }

    // Fetch from API and store in both master_clients_db and master_clients_sync
    private function fetchFromApi($gst_no)
    {
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
                    'api-key' => '6f0425e9-63af-4f1f-b96e-1f01bff888f2',
                    'account-id' => 'd46db310a92c/7e109c77-5501-4330-ad0a-2f1274c23374',
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
                    'api-key' => '6f0425e9-63af-4f1f-b96e-1f01bff888f2',
                    'account-id' => 'd46db310a92c/7e109c77-5501-4330-ad0a-2f1274c23374',
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
            $fullAddress = $details['contact_details']['principal']['address'] ?? null;
            $legal_name = $details['legal_name'] ?? null;
            $pan_number = $details['pan_number'] ?? null;
            $business_name = $details['business_name'] ?? null;
            $constitution = $details['constitution_of_business'] ?? null;
            $registration_date = $details['date_of_registration'] ?? null;
            $state = $details['state_jurisdiction'] ?? null;
            $annual_turnover = $details['annual_turnover'] ?? null;

            // Parse city and pincode from address
            $addressParts = $this->parseAddress($fullAddress);

            // Insert into master_clients_db
            $mcDbId = DB::table('master_clients_db')->insertGetId([
                'gst_no' => $details['gstin'] ?? $gst_no,
                'business_name' => $business_name,
                'legal_name' => $legal_name,
                'pan_number' => $pan_number,
                'promoters' => $promoters,
                'address' => $fullAddress,
                'email' => $email,
                'phone_number' => $phone,
                'constitution_of_business' => $constitution,
                'taxpayer_type' => $details['taxpayer_type'] ?? null,
                'nature_of_business' => $details['contact_details']['principal']['nature_of_business'] ?? null,
                'nature_of_core_business_activity_code' => $details['nature_of_core_business_activity_code'] ?? null,
                'nature_of_core_business_activity_description' => $details['nature_of_core_business_activity_description'] ?? null,
                'annual_turnover_fy' => $details['annual_turnover_fy'] ?? null,
                'annual_turnover' => $annual_turnover,
                'date_of_registration' => $registration_date,
                'date_of_cancellation' => $details['date_of_cancellation'] ?? null,
                'state_jurisdiction' => $state,
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

            // Insert into master_clients_sync
            $syncId = DB::table('master_clients_sync')->insertGetId([
                'mc_db_id' => $mcDbId,
                'gst_no' => $gst_no,
                'business_legalname' => $legal_name,
                'business_type' => $constitution,
                'register_date' => $registration_date,
                'promotors_name' => $promoters,
                'income_annual' => $annual_turnover,
                'pan_no' => $pan_number,
                'phone_number' => $phone,
                'email' => $email,
                'address' => $fullAddress,
                'city' => $addressParts['city'],
                'state' => $state,
                'pincode' => $addressParts['pincode'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'source' => 'api',
                'message' => 'GST verified from API',
                'sync_id' => $syncId,
                'data' => [
                    'gst_no' => $gst_no,
                    'business_legalname' => $legal_name,
                    'business_type' => $constitution,
                    'register_date' => $registration_date,
                    'promotors_name' => $promoters,
                    'income_annual' => $annual_turnover,
                    'pan_no' => $pan_number,
                    'phone_number' => $phone,
                    'email' => $email,
                    'address' => $fullAddress,
                    'city' => $addressParts['city'],
                    'state' => $state,
                    'pincode' => $addressParts['pincode'],
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

    // Step 2: Add Nick Name
    public function addNickName(Request $request)
    {
        $request->validate([
            'sync_id' => 'required|integer',
            'nick_name' => 'required|string|max:100',
        ]);

        // Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $syncId = $request->sync_id;
        $nickName = $request->nick_name;

        // Check if sync record exists
        $syncRecord = DB::table('master_clients_sync')->where('id', $syncId)->first();

        if (! $syncRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Sync record not found',
            ], 404);
        }

        // Update nick name
        DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->update([
                'nick_name' => $nickName,
                'updated_at' => now(),
            ]);

        // Update sync table with mc_id (obtained from token)
        DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->update(['mc_id' => $mcId]);

        return response()->json([
            'success' => true,
            'message' => 'Nick name added successfully',
            'data' => [
                'sync_id' => $syncId,
                'mc_id' => $mcId,
                'nick_name' => $nickName,
                'gst_no' => $syncRecord->gst_no,
                'business_legalname' => $syncRecord->business_legalname,
            ],
        ]);
    }

    // Helper function to parse city, state and pincode from address
    private function parseAddress($address)
    {
        $city = null;
        $state = null;
        $pincode = null;

        if ($address) {
            // Try to extract pincode (6 digits)
            if (preg_match('/\b(\d{6})\b/', $address, $matches)) {
                $pincode = $matches[1];
            }

            // Try to extract state name (common Indian states)
            $states = [
                'Kerala',
                'Tamil Nadu',
                'Karnataka',
                'Maharashtra',
                'Gujarat',
                'Rajasthan',
                'Uttar Pradesh',
                'Madhya Pradesh',
                'West Bengal',
                'Bihar',
                'Andhra Pradesh',
                'Telangana',
                'Punjab',
                'Haryana',
                'Jharkhand',
                'Assam',
                'Odisha',
                'Chhattisgarh',
                'Himachal Pradesh',
                'Uttarakhand',
                'Goa',
                'Delhi',
                'Jammu and Kashmir',
                'Puducherry',
                'Chandigarh',
                'Manipur',
                'Meghalaya',
                'Tripura',
                'Mizoram',
                'Arunachal Pradesh',
                'Nagaland',
                'Sikkim',
                'Andaman and Nicobar Islands',
                'Dadra and Nagar Haveli',
                'Daman and Diu',
                'Lakshadweep',
                'Ladakh',
            ];

            foreach ($states as $stateName) {
                if (stripos($address, $stateName) !== false) {
                    $state = $stateName;
                    break;
                }
            }

            // Try to extract city (word before state or pincode)
            $addressParts = explode(',', $address);
            if (count($addressParts) >= 2) {
                // Try to find city from address parts
                foreach ($addressParts as $part) {
                    $part = trim($part);
                    // Skip if it contains the state name or pincode
                    if ($state && stripos($part, $state) !== false) {
                        continue;
                    }
                    if ($pincode && strpos($part, $pincode) !== false) {
                        continue;
                    }
                    // Skip if it's too short or contains numbers at the start
                    if (strlen($part) < 3 || preg_match('/^\d/', $part)) {
                        continue;
                    }

                    // This is likely the city
                    if (! $city && strlen($part) > 2) {
                        $city = $part;
                    }
                }
            }
        }

        return [
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
        ];
    }

    // Get Client Sync Details
    public function getClientSync($syncId)
    {
        $sync = DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->first();

        if (! $sync) {
            return response()->json([
                'success' => false,
                'message' => 'Sync record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sync,
        ]);
    }

    // Client Login
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'mobile_number' => 'required|string:10',
    //         'password' => 'required|string'
    //     ]);

    //     $mobileNumber = $request->mobile_number;
    //     $password = $request->password;

    //     // Find client by phone number
    //     $client = DB::table('master_clients')
    //         ->where('phone_number', $mobileNumber)
    //         ->first();

    //     if (!$client) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Mobile number not found. Please register first.'
    //         ], 404);
    //     }

    //     // Check if password exists
    //     if (!$client->password) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Password not set. Please complete your registration.'
    //         ], 400);
    //     }

    //     // Verify password
    //     if (!Hash::check($password, $client->password)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid password. Please try again.'
    //         ], 401);
    //     }

    //     // Generate token with mc_id embedded
    //     $token = base64_encode($client->id . '|' . time() . '|' . bin2hex(random_bytes(16)));

    //     // Store token in database for validation (optional but recommended)
    //     DB::table('master_clients')
    //         ->where('id', $client->id)
    //         ->update([
    //             'login_token' => $token,
    //             'token_created_at' => now()
    //         ]);

    //     // Login successful - Return only mc_id and token
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Login successful',
    //         'data' => [
    //             'mc_id' => $client->id,
    //             'token' => $token,
    //             'image' => 'null',
    //             'emp_code' => 'null'
    //         ]
    //     ]);
    // }

    public function login(Request $request)
    {

        $request->validate([
            'mobile_number' => 'required|string|min:10|max:15',
            'password' => 'required|string',
            'device_token' => 'nullable|string',
        ]);

        $mobileNumber = $request->mobile_number;
        $password = $request->password;
        // $fcmToken = $request->input('device_token');
        $fcmToken = $request->device_token;

        // 🔹 Check master_clients first
        $user = DB::table('master_clients')
            ->where('phone_number', $mobileNumber)
            ->first();

        $table = 'master_clients';

        // 🔹 If not found, check add_user
        if (! $user) {
            $user = DB::table('add_user')
                ->where('mobile_number', $mobileNumber)
                ->first();
            $table = 'add_user';
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found. Please register first.',
            ], 404);
        }

        if (! $user->password) {
            return response()->json([
                'success' => false,
                'message' => 'Password not set. Please complete your registration.',
            ], 400);
        }

        if (! Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password. Please try again.',
            ], 401);
        }

        // 🔹 Generate token
        $token = base64_encode($user->id.'|'.time().'|'.bin2hex(random_bytes(16)));

        // 🔹 Update DB with login token + FCM token
        $updateData = [
            'login_token' => $token,
            'token_created_at' => now(),
            'fcm_token' => $fcmToken,
        ];

        if ($table === 'master_clients') {
            DB::table('master_clients')->where('id', $user->id)->update($updateData);
        } else {
            DB::table('add_user')->where('id', $user->id)->update($updateData);
        }

        // 🔹 Fetch sync_id (if available)
        $sync = DB::table('master_clients')
            ->where('id', $user->id)
            ->latest('id')
            ->first();

        $syncId = $sync->id ?? null;

        // 🔹 If user is from add_user, check if corresponding master_client exists
        $masterClient = null;
        if ($table === 'add_user') {
            $masterClient = DB::table('master_clients')
                ->where('id', $user->mc_id ?? 0)
                ->first();
        } else {
            $masterClient = DB::table('master_clients')
                ->where('id', $user->id)
                ->first();
        }

        $masterClientsDb = DB::table('master_clients_db')
            ->where('phone_number', $mobileNumber)
            ->first();

        $mcDbId = $masterClientsDb->id ?? null;

        // 🔹 Prepare response data
        $responseData = [
            'mc_id' => $masterClient->id ?? null,
            'name' => ($table === 'add_user') ? $user->name : $user->business_legalname,
            'mobile_number' => $masterClient->phone_number ?? null,
            'mc_db_id' => $mcDbId,
            'token' => $token,
            'image' => $user->profile_image ? asset($user->profile_image) : null,
            'emp_code' => property_exists($user, 'emp_code') ? $user->emp_code : null,
            'device_token' => $fcmToken,
            'sync_id' => $syncId,
            'table' => $table,
            // 'permission' => property_exists($user, 'permission') ? $user->permission : null,
            'master_client_exists' => $masterClient ? true : false,
        ];

        // 🔹 Add extra info if user is from add_user
        if ($table === 'add_user') {
            $responseData['role'] = $user->role ?? null;
            $responseData['permission'] = $user->permission ?? null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $responseData,
        ]);
    }

    // Helper function to validate token and get mc_id
    private function validateTokenAndGetMcId(Request $request)
    {
        $token = $request->header('Authorization');

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

    // Get logged-in user profile
    public function getProfile(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $client = DB::table('master_clients')
            ->where('id', $mcId)
            ->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mc_id' => $client->id,
                'gst_number' => $client->gst_number,
                'business_legalname' => $client->business_legalname,
                'promotors_name' => $client->promotors_name,
                'pan_number' => $client->pan_number,
                'phone_number' => $client->phone_number,
                'email' => $client->email,
                'address' => $client->address,
                'nick_name' => $client->nick_name,
                'projects' => $client->projects,
                'turn_over' => $client->turn_over,
                'contractor_type' => $client->contractor_type,
                'challenge' => $client->challenge,
            ],
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }

        // Clear token from database
        DB::table('master_clients')
            ->where('id', $mcId)
            ->update([
                'login_token' => null,
                'token_created_at' => null,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    //      public function createTender(Request $request)
    //     {
    //         // Validate token and get mc_id
    //         $mcId = $this->validateTokenAndGetMcId($request);

    //         if (!$mcId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid or expired token. Please login again.'
    //             ], 401);
    //         }

    //         // Validate request
    //         $request->validate([
    //             'tender_no' => 'required|string|max:100',
    //             'project_name' => 'nullable|string|max:255',
    //             'contractor' => 'nullable|string|max:255',
    //             'authority' => 'nullable|integer|exists:master_clients_sync,id',
    //             'scheme' => 'nullable|string|max:255',
    //             'location' => 'nullable|string|max:255',
    //             'status' => 'nullable|string|max:50',
    //             'remainder_date' => 'nullable|date',
    //             'as_no' => 'nullable|string|max:100',
    //             'as_date' => 'nullable|date',
    //             'ts_date' => 'nullable|date',
    //             'tender_value' => 'nullable|numeric',
    //             'bid_value' => 'nullable|numeric',
    //             'emd_value' => 'nullable|numeric',
    //             'gst_applicable' => 'nullable|string|max:10',
    //             'hsn_code' => 'nullable|string|max:50',
    //             'year_end_date' => 'nullable|string|max:50',
    //             'emd_type' => 'nullable|string|max:100',
    //             'emd_date' => 'required|date',
    //             'reference_id' => 'nullable|string|max:100',
    //             'bank_name' => 'nullable|string|max:255',
    //             'account_no' => 'nullable|string|max:50',
    //             'notes' => 'nullable|string',
    //             'tendors_notes' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
    //             'bg_emd_scans' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
    //             'contract_agreements' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
    //             'as_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
    //             'estimation_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
    //             'others' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'
    //         ]);
    //  if ($request->authority) {
    //         $authorityExists = DB::table('master_clients_sync')
    //             ->where('id', $request->authority)
    //             ->where('mc_id', $mcId)
    //             ->exists();

    //         if (!$authorityExists) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid authority. Please select from your synced clients.'
    //             ], 400);
    //         }
    //     }
    //         // Handle file uploads
    //         $tendorsNotesPath = null;
    //         $bgEmdScansPath = null;
    //         $contractAgreementsPath = null;
    //         $asCopyPath = null;
    //         $estimationCopyPath = null;
    //         $othersPath = null;

    //         // Upload Tendors Notes
    //         if ($request->hasFile('tendors_notes')) {
    //             $file = $request->file('tendors_notes');
    //             $filename = 'tendors_notes_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
    //             $file->move(public_path('tender_documents/tendors_notes'), $filename);
    //             $tendorsNotesPath = 'tender_documents/tendors_notes/' . $filename;
    //         }

    //         // Upload BG/EMD Scans
    //         if ($request->hasFile('bg_emd_scans')) {
    //             $file = $request->file('bg_emd_scans');
    //             $filename = 'bg_emd_scans_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
    //             $file->move(public_path('tender_documents/bg_emd_scans'), $filename);
    //             $bgEmdScansPath = 'tender_documents/bg_emd_scans/' . $filename;
    //         }

    //         // Upload Contract Agreements
    //         if ($request->hasFile('contract_agreements')) {
    //             $file = $request->file('contract_agreements');
    //             $filename = 'contract_agreements_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
    //             $file->move(public_path('tender_documents/contract_agreements'), $filename);
    //             $contractAgreementsPath = 'tender_documents/contract_agreements/' . $filename;
    //         }

    //         // Upload AS Copy
    //         if ($request->hasFile('as_copy')) {
    //             $file = $request->file('as_copy');
    //             $filename = 'as_copy_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
    //             $file->move(public_path('tender_documents/as_copy'), $filename);
    //             $asCopyPath = 'tender_documents/as_copy/' . $filename;
    //         }

    //         // Upload Estimation Copy
    //         if ($request->hasFile('estimation_copy')) {
    //             $file = $request->file('estimation_copy');
    //             $filename = 'estimation_copy_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
    //             $file->move(public_path('tender_documents/estimation_copy'), $filename);
    //             $estimationCopyPath = 'tender_documents/estimation_copy/' . $filename;
    //         }

    //         // Upload Others
    //         if ($request->hasFile('others')) {
    //             $file = $request->file('others');
    //             $filename = 'others_' . $mcId . '_' . time() . '.' . $file->getClientOriginalExtension();
    //             $file->move(public_path('tender_documents/others'), $filename);
    //             $othersPath = 'tender_documents/others/' . $filename;
    //         }

    //         try {
    //             // Insert tender data
    //             $tenderId = DB::table('master_tender')->insertGetId([
    //                 'mc_id' => $mcId,
    //                 'tender_no' => $request->tender_no,
    //                 'project_name' => $request->project_name,
    //                 'contractor' => $request->contractor,
    //                 'authority' => $request->authority,
    //                 'scheme' => $request->scheme,
    //                 'location' => $request->location,
    //                 'status' => $request->status,
    //                 'remainder_date' => $request->remainder_date,
    //                 'as_no' => $request->as_no,
    //                 'as_date' => $request->as_date,
    //                 'ts_date' => $request->ts_date,
    //                 'tender_value' => $request->tender_value,
    //                 'bid_value' => $request->bid_value,
    //                 'emd_value' => $request->emd_value,
    //                 'gst_applicable' => $request->gst_applicable,
    //                 'hsn_code' => $request->hsn_code,
    //                 'year_end_date' => $request->year_end_date,
    //                 'emd_type' => $request->emd_type,
    //                 'emd_date' => $request->emd_date,
    //                 'reference_id' => $request->reference_id,
    //                 'bank_name' => $request->bank_name,
    //                 'account_no' => $request->account_no,
    //                 'tendors_notes' => $tendorsNotesPath,
    //                 'bg_emd_scans' => $bgEmdScansPath,
    //                 'contract_agreements' => $contractAgreementsPath,
    //                 'as_copy' => $asCopyPath,
    //                 'estimation_copy' => $estimationCopyPath,
    //                 'others' => $othersPath,
    //                 'notes' => $request->notes,
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Tender created successfully',
    //                 'data' => [
    //                     'tender_id' => $tenderId,
    //                     'mc_id' => $mcId,
    //                     'tender_no' => $request->tender_no,
    //                     'project_name' => $request->project_name
    //                 ]
    //             ]);

    //         } catch (\Exception $e) {
    //             Log::error("Tender creation failed: " . $e->getMessage());
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Tender creation failed: ' . $e->getMessage()
    //             ], 500);
    //         }
    //     }
    public function createTender(Request $request)
    {
        // ✅ Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // ✅ Validate request (added new fields)
        $request->validate([
            'tender_no' => 'required|string|max:100',
            'project_name' => 'required|string|max:255',
            'contractor' => 'required|string|max:255',
            'authority' => 'required|integer|exists:master_clients_sync,id',
            'scheme' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'remainder_date' => 'required|date',
            'as_no' => 'nullable|string|max:100',
            'as_date' => 'nullable|date',
            'ts_no' => 'nullable|string|max:100',         // ✅ new field
            'ts_date' => 'nullable|date',                 // ✅ already existed
            'maturity_amount' => 'nullable|numeric',      // ✅ new field
            'issue_date' => 'nullable|date',              // ✅ new field
            'expired_date' => 'nullable|date',
            'challance_id' => 'nullable|string|max:100',           // ✅ new field
            'challance_date' => 'nullable|date',
            'date' => 'nullable|date',                    // ✅ new field
            'tender_value' => 'required|numeric',
            'bid_value' => 'nullable|numeric',
            'emd_value' => 'required|numeric',
            'gst_applicable' => 'required|string|max:10',
            'hsn_code' => 'required|string|max:50',
            'year_end_date' => 'nullable|string|max:50',
            'emd_type' => 'required|string|max:100',
            'emd_date' => 'required|date',
            'reference_id' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:50',
            'notes' => 'required|string',
            'tendors_notes' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'bg_emd_scans' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'contract_agreements' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'as_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'estimation_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'others' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // ✅ Authority validation
        if ($request->authority) {
            $authorityExists = DB::table('master_clients_sync')
                ->where('id', $request->authority)
                ->where('mc_id', $mcId)
                ->exists();

            if (! $authorityExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authority. Please select from your synced clients.',
                ], 400);
            }
        }

        // ✅ File uploads
        $paths = [
            'tendors_notes' => null,
            'bg_emd_scans' => null,
            'contract_agreements' => null,
            'as_copy' => null,
            'estimation_copy' => null,
            'others' => null,
        ];

        foreach ($paths as $key => &$path) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = "{$key}_{$mcId}_".time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path("tender_documents/{$key}"), $filename);
                $path = "tender_documents/{$key}/".$filename;
            }
        }

        try {
            // ✅ Insert into master_tender
            $tenderId = DB::table('master_tender')->insertGetId([
                'mc_id' => $mcId,
                'tender_no' => $request->tender_no,
                'project_name' => $request->project_name,
                'contractor' => $request->contractor,
                'authority' => $request->authority,
                'scheme' => $request->scheme,
                'location' => $request->location,
                'status' => $request->status,
                'remainder_date' => $request->remainder_date,
                'as_no' => $request->as_no,
                'as_date' => $request->as_date,
                'ts_no' => $request->ts_no,                         // ✅ new
                'ts_date' => $request->ts_date,                     // ✅ new
                // 'maturity_amount' => $request->maturity_amount,     // ✅ new
                'bg_issue_date' => $request->issue_date,               // ✅ new
                'bg_expire_date' => $request->expired_date,           // ✅ new
                'date' => $request->date,                           // ✅ new
                'tender_value' => $request->tender_value,
                'bid_value' => $request->bid_value,
                'emd_value' => $request->emd_value,
                'gst_applicable' => $request->gst_applicable,
                'hsn_code' => $request->hsn_code,
                'year_end_date' => $request->year_end_date,
                'emd_type' => $request->emd_type,
                'emd_date' => $request->emd_date,
                'reference_id' => $request->reference_id,
                'bank_name' => $request->bank_name,
                'account_no' => $request->account_no,
                'tendors_notes' => $paths['tendors_notes'],
                'bg_emd_scans' => $paths['bg_emd_scans'],
                'contract_agreements' => $paths['contract_agreements'],
                'as_copy' => $paths['as_copy'],
                'estimation_copy' => $paths['estimation_copy'],
                'others' => $paths['others'],
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tender created successfully',
                'data' => [
                    'tender_id' => $tenderId,
                    'mc_id' => $mcId,
                    'tender_no' => $request->tender_no,
                    'project_name' => $request->project_name,
                    'ts_no' => $request->ts_no,
                    'ts_date' => $request->ts_date,
                    'maturity_amount' => $request->maturity_amount,
                    'issue_date' => $request->issue_date,
                    'expired_date' => $request->expired_date,
                    'date' => $request->date,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Tender creation failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Tender creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Get All Tenders for Logged-in User
    public function getTenders(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get tenders with authority nickname
        $tenders = DB::table('master_tender as t')
            ->leftJoin('master_clients_sync as c', 't.authority', '=', 'c.id')
            ->where('t.mc_id', $mcId)
            ->orderBy('t.created_at', 'desc')
            ->select(
                't.*',
                DB::raw('c.nick_name as authority') // or 'c.client_name' if you store full name
            )
            ->get();

        // Convert file paths to full URLs
        $baseUrl = url('public/');
        $fileFields = [
            'tendors_notes',
            'bg_emd_scans',
            'contract_agreements',
            'as_copy',
            'estimation_copy',
            'others',
        ];

        foreach ($tenders as $tender) {
            foreach ($fileFields as $field) {
                if (! empty($tender->$field)) {
                    $tender->$field = $baseUrl.'/'.ltrim($tender->$field, '/');
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $tenders,
        ]);
    }

    public function userSchemeList(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch distinct schemes for this user
        $schemes = DB::table('master_tender')
            ->where('mc_id', $mcId)
            ->whereNotNull('scheme')
            ->where('scheme', '!=', '')
            ->distinct()
            ->pluck('scheme');

        return response()->json([
            'success' => true,
            'message' => 'User scheme list fetched successfully.',
            'data' => $schemes,
        ]);
    }

    public function userAuthorityList(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch distinct authority IDs used in user's tenders
        $authorities = DB::table('master_tender as t')
            ->leftJoin('master_clients_sync as c', 't.authority', '=', 'c.id')
            ->where('t.mc_id', $mcId)
            ->whereNotNull('t.authority')
            ->distinct()
            ->select('c.id', 'c.nick_name')  // return authority id + name
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User authority list fetched successfully.',
            'data' => $authorities,
        ]);
    }

    public function userStatusList(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch distinct status values for this user's tenders
        $statuses = DB::table('master_tender')
            ->where('mc_id', $mcId)
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->pluck('status');

        return response()->json([
            'success' => true,
            'message' => 'User status list fetched successfully.',
            'data' => $statuses,
        ]);
    }

    public function tenderReportDownload(Request $request)
    {
        log::info('tender status report', ['report' => $request->all()]);

        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ]);
        }

        // $query = DB::table('master_tender')->where('mc_id', $mcId);
        $query = DB::table('master_tender')
            ->leftJoin('master_clients_sync', 'master_tender.authority', '=', 'master_clients_sync.id')
            ->where('master_tender.mc_id', $mcId)
            ->select(
                'master_tender.*',
                'master_clients_sync.business_legalname as authority_name'
            );

        if (! empty($request->scheme)) {
            $query->where('master_tender.scheme', $request->scheme);
        }

        if (! empty($request->authority)) {
            $query->where('master_tender.authority', $request->authority);
        }

        if (! empty($request->status)) {
            $query->where('master_tender.status', $request->status);
        }

        $tenders = $query->get();

        if ($tenders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for selected filter(s)',
            ]);
        }

        $tenderIds = $tenders->pluck('id')->toArray();

        $bills = DB::table('tender_bill')
            ->where('mc_id', $mcId)
            ->whereIn('t_id', $tenderIds)
            ->get()
            ->groupBy('t_id');

        $tenders = $tenders->map(function ($tender) use ($bills) {
            $tender->bills = $bills->has($tender->id) ? $bills[$tender->id]->values()->toArray() : [];

            return $tender;
        });

        log::info('tender status report: '.json_encode($tenders, JSON_PRETTY_PRINT));

        $user = DB::table('master_clients_sync')->where('mc_id', $mcId)->first();

        return response()->json([
            'success' => true,
            'user' => $user,
            'tenders' => $tenders,
        ]);
    }

    public function tenderPaymentDownload(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        // log::info('tender payment report', ['report' => $request->all()]);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ]);
        }

        $query = DB::table('master_tender')->where('mc_id', $mcId);

        // if (! empty($request->scheme)) {
        //     $query->where('scheme', $request->scheme);
        // }

        // if (! empty($request->emd_type)) {
        //     $query->where('emd_type', $request->emd_type);
        // }

        // dd($request->all());

        if ($request->filled('scheme')) {
            $query->where('scheme', $request->scheme);
        }

        if ($request->filled('emd_type')) {
            $query->where('emd_type', $request->emd_type);
        }

        if ($request->filled(['from_date', 'to_date'])) {
            $query->whereDate('created_at', '>=', $request->from_date)
                ->whereDate('created_at', '<=', $request->to_date);
        }

        // if (! empty($request->from_date) && ! empty($request->to_date)) {
        //     $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        // }
        // elseif (! empty($request->from_date)) {
        //     $query->where('created_at', '>=', $request->from_date);
        // } elseif (! empty($request->to_date)) {
        //     $query->where('created_at', '<=', $request->to_date);
        // }

        $tenders = $query->get();

        // log::info('tender payment report: '.json_encode($tenders, JSON_PRETTY_PRINT));

        if ($tenders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for selected filter(s)',
            ]);
        }

        $tenderIds = $tenders->pluck('id')->toArray();

        $bills = DB::table('tender_bill')
            ->where('mc_id', $mcId)
            ->whereIn('t_id', $tenderIds)  // assuming 't_id' in bills refers to tender's 'id'
            ->get()
            ->groupBy('t_id');

        $tenders = $tenders->map(function ($tender) use ($bills) {
            $tender->bills = $bills->has($tender->id) ? $bills[$tender->id]->values()->toArray() : [];

            return $tender;
        });

        $user = DB::table('master_clients_sync')->where('mc_id', $mcId)->first();

        return response()->json([
            'success' => true,
            'user' => $user,
            'tenders' => $tenders,
        ]);
    }

    // Tender Refund Filter Details

    public function tenderRefundReportDownload(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ]);
        }

        // ------------ STEP 1: BASE QUERY --------------
        $query = DB::table('master_tender')->where('mc_id', $mcId)->where('tender_status', 'collected');

        $isFilterApplied = false;

        if ($request->filled('scheme')) {
            $query->where('scheme', trim($request->scheme));
            $isFilterApplied = true;
        }

        if ($request->filled('authority')) {
            $query->where('authority', trim($request->authority));
            $isFilterApplied = true;
        }

        $tenders = $query->get();

        return response()->json([
            'success' => true,
            'data' => $tenders,
        ]);

        // cleared the data logic as per new requirement on 06-12-2024

        // If filter applied, use filtered tenders
        // If no filter applied, use ALL tenders
        // $tenders = $isFilterApplied
        //     ? $query->get()
        //     : DB::table('master_tender')->where('mc_id', $mcId)->get();

        // if ($tenders->isEmpty()) {
        //     // If filtered tenders empty → return empty, NOT all tenders
        //     return response()->json([
        //         'success' => true,
        //         'tenders' => [],
        //     ]);
        // }

        // ------------ STEP 2: GET COLLECTED BILLS --------------

        $bills = DB::table('tender_bill')
            ->where('mc_id', $mcId)
            ->whereIn('t_id', $tenderIds)
            ->whereRaw("LOWER(TRIM(status)) = 'collected'")
            ->get()
            ->groupBy('t_id');

        // ------------ STEP 3: RETURN ONLY TENDERS HAVING COLLECTED BILLS --------------
        $filteredTenders = $tenders
            ->filter(fn ($t) => $bills->has($t->id))
            ->map(function ($t) use ($bills) {
                $t->bills = $bills[$t->id]->values()->toArray();

                return $t;
            })
            ->values();

        return response()->json([
            'success' => true,
            'tenders' => $filteredTenders,
        ]);
    }

    public function userPaymentList(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch distinct emd_type for this user
        $emdTypes = DB::table('master_tender')
            ->where('mc_id', $mcId)
            ->whereNotNull('emd_type')
            ->where('emd_type', '!=', '')
            ->distinct()
            ->pluck('emd_type');

        return response()->json([
            'success' => true,
            'message' => 'User EMD type list fetched successfully.',
            'data' => $emdTypes,
        ]);
    }

    // public function tenderReportDownload(Request $request)
    // {

    //     $mcId = $this->validateTokenAndGetMcId($request);

    //     if (!$mcId) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid or expired token'
    //         ]);
    //     }

    //     $filterType = $request->filter_type; // All | Scheme | Authority | Status

    //     $query = DB::table('master_tender')->where('mc_id', $mcId);

    //     // -------------------------
    //     // APPLY FILTER BASED ON TYPE
    //     // -------------------------
    //     if ($filterType == "Scheme") {
    //         $query->where('scheme', $request->scheme);
    //     } else if ($filterType == "Authority") {
    //         $query->where('authority', $request->authority);
    //     } else if ($filterType == "Status") {
    //         $query->where('status', $request->status);
    //     } else if ($filterType == "All" || empty($filterType)) {
    //         // No additional filters
    //     }

    //     $tenders = $query->get();

    //     if ($tenders->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No data found for selected filter'
    //         ]);
    //     }

    //     // Get user details
    //     $user = DB::table('master_tender')->where('mc_id', $mcId)->first();

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.tender_report', [
    //         'tenders' => $tenders,
    //         'filterType' => $filterType,
    //         'user' => $user
    //     ]);

    //     $fileName = 'tender_report_' . time() . '.pdf';
    //     Storage::disk('public')->put('reports/' . $fileName, $pdf->output());

    //     return response()->json([
    //         'success' => true,
    //         'file_url' => url('storage/reports/' . $fileName)
    //     ]);
    // }

    // public function tenderReportDownload(Request $request)
    // {
    //     $mcId = $this->validateTokenAndGetMcId($request);

    //     if (!$mcId) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid or expired token'
    //         ]);
    //     }

    //     $filterType = $request->filter_type;

    //     $query = DB::table('master_tender')->where('mc_id', $mcId);

    //     if ($filterType == "Scheme") {
    //         $query->where('scheme', $request->scheme);
    //     }

    //     if ($filterType == "Authority") {
    //         $query->where('authority', $request->authority);
    //     }

    //     if ($filterType == "Status") {
    //         $query->where('status', $request->status);
    //     }

    //     $tenders = $query->get();

    //     if ($tenders->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No data found for selected filter'
    //         ]);
    //     }
    //     $user = DB::table('master_tender')->where('id', $mcId)->first();

    //     $pdf = Pdf::loadView('pdf.tender_report', [
    //         'tenders' => $tenders,
    //         'filterType' => $filterType,
    //         'user' => $user
    //     ]);

    //     $fileName = 'tender_report_' . time() . '.pdf';
    //     Storage::disk('public')->put('reports/' . $fileName, $pdf->output());

    //     return response()->json([
    //         'success' => true,
    //         'file_url' => url('storage/reports/' . $fileName)
    //     ]);
    // }

    public function getTenderById(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get tenderId from request body (form data or JSON)
        $tenderId = $request->input('tender_id');
        if (! $tenderId) {
            return response()->json([
                'success' => false,
                'message' => 'Tender ID is required',
            ], 400);
        }

        // Get tender record
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        // Fetch authority details separately
        $authority = DB::table('master_clients_sync')
            ->where('id', $tender->authority)
            ->select('id', 'nick_name')
            ->first();

        // Convert file paths to full URLs
        $baseUrl = url('public/');
        $fileFields = [
            'tendors_notes',
            'bg_emd_scans',
            'contract_agreements',
            'as_copy',
            'estimation_copy',
            'others',
        ];

        foreach ($fileFields as $field) {
            if (! empty($tender->$field)) {
                $tender->$field = $baseUrl.'/'.ltrim($tender->$field, '/');
            }
        }

        // Convert tender to array to append authority
        $tenderData = (array) $tender;

        // Add authority details
        $tenderData['authority'] = $authority ? [
            'id' => $authority->id,
            'nick_name' => $authority->nick_name,
        ] : null;

        return response()->json([
            'success' => true,
            'data' => $tenderData,
        ]);
    }

    // Create Tender Collection
    public function createTenderCollection(Request $request)
    {
        // 1️⃣ Validate token
        $mcId = $this->validateTokenAndGetMcId($request);
        if (! $mcId) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token.'], 401);
        }

        // 2️⃣ Validate input
        $request->validate([
            't_id' => 'required|integer',
            'collection_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'required|string',
            'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // 3️⃣ Verify tender
        $tender = DB::table('master_tender')
            ->where('id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json(['success' => false, 'message' => 'Tender not found or unauthorized'], 404);
        }

        // 4️⃣ File upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'collection_'.$mcId.'_'.$request->t_id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('tender_collections'), $filename);
            $attachmentPath = 'tender_collections/'.$filename;
        }

        try {
            DB::beginTransaction();

            // 5️⃣ Insert collection record
            $collectionId = DB::table('tender_collection')->insertGetId([
                't_id' => $request->t_id,
                'mc_id' => $mcId,
                'collection_date' => $request->collection_date,
                'amount' => $request->amount,
                'attachment' => $attachmentPath,
                'remarks' => $request->remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // // 6️⃣ Get client info
            // $client = DB::table('master_clients')
            //     ->where('id', $mcId)
            //     ->select('phone_number', 'fcm_token', 'business_legalname')
            //     ->first();

            // // 7️⃣ Prepare notification data
            // $title = 'Tender Collection Added';
            // $body = "₹{$request->amount} collected for Tender #{$request->t_id} on {$request->collection_date}.";

            // // 8️⃣ Store notification in DB
            // DB::table('notifications')->insert([
            //     'mc_id' => $mcId,
            //     'title' => $title,
            //     'message' => $body,
            //     'type' => 'tender_collection',
            //     'related_id' => $collectionId,
            //     'status' => 'unread',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);

            // // 9️⃣ Send FCM if available
            // if ($client && ! empty($client->fcm_token)) {

            //     try {

            //         $fcm = new Fcm;
            //         $fcm->send_notify($client->fcm_token, [
            //             'title' => $title,
            //             'body' => $body,
            //         ]);

            //         // $fcm = new Fcm;
            //         // $fcm->send_notify($client->fcm_token, $title, $body);
            //         Log::info("✅ FCM Notification sent to mc_id {$mcId}");
            //     } catch (\Throwable $e) {
            //         Log::error('❌ Failed to send FCM: '.$e->getMessage());
            //     }
            // } else {
            //     Log::warning("⚠️ No FCM token found for mc_id {$mcId}");
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tender collection created and notification sent.',
                'data' => [
                    'collection_id' => $collectionId,
                    'mc_id' => $mcId,
                    't_id' => $request->t_id,
                    'amount' => $request->amount,
                    'collection_date' => $request->collection_date,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Tender collection failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    // Get All Tender Collections for Logged-in User
    public function getTenderCollections(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $collections = DB::table('tender_collection')
            ->join('master_tender', 'tender_collection.t_id', '=', 'master_tender.id')
            ->where('tender_collection.mc_id', $mcId)
            ->select(
                'tender_collection.*',
                'master_tender.tender_no',
                'master_tender.project_name'
            )
            ->orderBy('tender_collection.collection_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $collections,
        ]);
    }

    // Get Collections by Tender ID
    public function getCollectionsByTender(Request $request, $tenderId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Verify tender belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        $collections = DB::table('tender_collection')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->orderBy('collection_date', 'desc')
            ->get();

        // Calculate total collection amount
        $totalAmount = DB::table('tender_collection')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'tender_no' => $tender->tender_no,
                'project_name' => $tender->project_name,
                'total_collection' => $totalAmount,
                'collections' => $collections,
            ],
        ]);
    }

    // Get Single Collection Details
    public function getCollectionById(Request $request, $collectionId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $collection = DB::table('tender_collection')
            ->join('master_tender', 'tender_collection.t_id', '=', 'master_tender.id')
            ->where('tender_collection.id', $collectionId)
            ->where('tender_collection.mc_id', $mcId)
            ->select(
                'tender_collection.*',
                'master_tender.tender_no',
                'master_tender.project_name'
            )
            ->first();

        if (! $collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $collection,
        ]);
    }

    // Update Tender Collection
    public function updateTenderCollection(Request $request, $collectionId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Check if collection exists and belongs to user
        $collection = DB::table('tender_collection')
            ->where('id', $collectionId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection record not found',
            ], 404);
        }

        // Validate request
        $request->validate([
            'collection_date' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // Prepare update data
        $updateData = [];

        if ($request->has('collection_date')) {
            $updateData['collection_date'] = $request->collection_date;
        }
        if ($request->has('amount')) {
            $updateData['amount'] = $request->amount;
        }
        if ($request->has('remarks')) {
            $updateData['remarks'] = $request->remarks;
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file
            if ($collection->attachment && file_exists(public_path($collection->attachment))) {
                unlink(public_path($collection->attachment));
            }

            $file = $request->file('attachment');
            $filename = 'collection_'.$mcId.'_'.$collection->t_id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('tender_collections'), $filename);
            $updateData['attachment'] = 'tender_collections/'.$filename;
        }

        $updateData['updated_at'] = now();

        try {
            DB::table('tender_collection')
                ->where('id', $collectionId)
                ->where('mc_id', $mcId)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Collection updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Collection update failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Collection update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete Tender Collection
    public function deleteTenderCollection(Request $request, $collectionId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $collection = DB::table('tender_collection')
            ->where('id', $collectionId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection record not found',
            ], 404);
        }

        try {
            // Delete attachment file
            if ($collection->attachment && file_exists(public_path($collection->attachment))) {
                unlink(public_path($collection->attachment));
            }

            // Delete collection record
            DB::table('tender_collection')
                ->where('id', $collectionId)
                ->where('mc_id', $mcId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Collection deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Collection deletion failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Collection deletion failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Add these methods to your ClientSyncController class

    // Create Tender Expense
    public function createTenderExpense(Request $request)
    {
        // Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate request
        $request->validate([
            't_id' => 'required|integer',
            'expense_category' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0',
        ]);

        // Verify tender exists and belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found or does not belong to you',
            ], 404);
        }

        try {
            // Insert expense data
            $expenseId = DB::table('tender_expense')->insertGetId([
                't_id' => $request->t_id,
                'mc_id' => $mcId,
                'expense_category' => $request->expense_category,
                'amount' => $request->amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tender expense created successfully',
                'data' => [
                    'expense_id' => $expenseId,
                    'mc_id' => $mcId,
                    't_id' => $request->t_id,
                    'expense_category' => $request->expense_category,
                    'amount' => $request->amount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Tender expense creation failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Tender expense creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Get All Tender Expenses for Logged-in User
    public function getTenderExpenses(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $expenses = DB::table('tender_expense')
            ->join('master_tender', 'tender_expense.t_id', '=', 'master_tender.id')
            ->where('tender_expense.mc_id', $mcId)
            ->select(
                'tender_expense.*',
                'master_tender.tender_no',
                'master_tender.project_name'
            )
            ->orderBy('tender_expense.created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $expenses,
        ]);
    }

    // Get Expenses by Tender ID
    public function getExpensesByTender(Request $request, $tenderId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Verify tender belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        $expenses = DB::table('tender_expense')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total expense amount
        $totalAmount = DB::table('tender_expense')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('amount');

        // Group expenses by category
        $expensesByCategory = DB::table('tender_expense')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tender_no' => $tender->tender_no,
                'project_name' => $tender->project_name,
                'total_expense' => $totalAmount,
                'expenses_by_category' => $expensesByCategory,
                'expenses' => $expenses,
            ],
        ]);
    }

    // Get Single Expense Details
    public function getExpenseById(Request $request, $expenseId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $expense = DB::table('tender_expense')
            ->join('master_tender', 'tender_expense.t_id', '=', 'master_tender.id')
            ->where('tender_expense.id', $expenseId)
            ->where('tender_expense.mc_id', $mcId)
            ->select(
                'tender_expense.*',
                'master_tender.tender_no',
                'master_tender.project_name'
            )
            ->first();

        if (! $expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $expense,
        ]);
    }

    // Update Tender Expense
    public function updateTenderExpense(Request $request, $expenseId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Check if expense exists and belongs to user
        $expense = DB::table('tender_expense')
            ->where('id', $expenseId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense record not found',
            ], 404);
        }

        // Validate request
        $request->validate([
            'expense_category' => 'nullable|string|max:150',
            'amount' => 'nullable|numeric|min:0',
        ]);

        // Prepare update data
        $updateData = [];

        if ($request->has('expense_category')) {
            $updateData['expense_category'] = $request->expense_category;
        }
        if ($request->has('amount')) {
            $updateData['amount'] = $request->amount;
        }

        $updateData['updated_at'] = now();

        try {
            DB::table('tender_expense')
                ->where('id', $expenseId)
                ->where('mc_id', $mcId)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Expense update failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Expense update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function fetchBillAmount(Request $request)
    {
        // 1️⃣ Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // 2️⃣ Validate incoming parameters
        $request->validate([
            't_id' => 'required|integer|exists:master_tender,id',
            'bill_id' => 'required|integer|exists:tender_bill,id',
        ]);

        try {
            // 3️⃣ Fetch the bill record
            $bill = DB::table('tender_bill')
                ->where('mc_id', $mcId)
                ->where('t_id', $request->t_id)
                ->where('id', $request->bill_id)
                ->select('id as bill_id', 't_id', 'mc_id', 'total_amount', 'created_at')
                ->first();

            // 4️⃣ Check if found
            if (! $bill) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bill found for the given details.',
                ], 404);
            }

            // 5️⃣ Return response
            return response()->json([
                'success' => true,
                'message' => 'Bill details fetched successfully',
                'data' => [
                    'bill_id' => $bill->bill_id,
                    't_id' => $bill->t_id,
                    'mc_id' => $bill->mc_id,
                    'total_amount' => $bill->total_amount,
                    'bill_date' => \Carbon\Carbon::parse($bill->created_at)->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bill details: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete Tender Expense
    public function deleteTenderExpense(Request $request, $expenseId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $expense = DB::table('tender_expense')
            ->where('id', $expenseId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense record not found',
            ], 404);
        }

        try {
            // Delete expense record
            DB::table('tender_expense')
                ->where('id', $expenseId)
                ->where('mc_id', $mcId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Expense deletion failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Expense deletion failed: '.$e->getMessage(),
            ], 500);
        }
    }
    // Add these methods to your ClientSyncController class

    // Create Tender Bill
    public function createTenderBill(Request $request)
    {
        // dd($request->all());
        log::info('tendrr bill create-2416', ['error' => $request->all()]);
        // Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate request
        $request->validate(rules: [
            't_id' => 'required|integer',
            'payment_type' => 'required|string|max:50',
            'work_done_amount' => 'required|numeric|min:0',
            'taxable_amount' => 'required|numeric|min:0',
            'it_amount' => 'required|numeric|min:0',
            'cgst_amount' => 'required|numeric|min:0',
            'sgst_amount' => 'required|numeric|min:0',
            'lwf_amount' => 'required|numeric|min:0',
            'others_amount' => 'required|numeric|min:0',
            'withheld_amount' => 'required|numeric|min:0',
            'deduction' => 'required|numeric|min:0',
            'collection_proof' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'remarks' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        // Verify tender exists and belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found or does not belong to you',
            ], 404);
        }

        // Handle file upload for collection_proof
        $collectionProofPath = null;
        if ($request->hasFile('collection_proof')) {
            $file = $request->file('collection_proof');
            $filename = 'collection_proof_'.$mcId.'_'.$request->t_id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('tender_bills/collection_proofs'), $filename);
            $collectionProofPath = 'tender_bills/collection_proofs/'.$filename;
        }

        try {
            // Calculate total_amount
            $workDoneAmount = $request->work_done_amount ?? 0;
            $itAmount = $request->it_amount ?? 0;
            $cgstAmount = $request->cgst_amount ?? 0;
            $sgstAmount = $request->sgst_amount ?? 0;
            $lwfAmount = $request->lwf_amount ?? 0;
            $othersAmount = $request->others_amount ?? 0;
            $withheldAmount = $request->withheld_amount ?? 0;
            $deduction = $request->deduction ?? 0;
            $totalAmount = $request->total_amount ?? 0;

            // Insert bill data
            $billId = DB::table('tender_bill')->insertGetId([
                't_id' => $request->t_id,
                'mc_id' => $mcId,
                'payment_type' => $request->payment_type,
                'work_done_amount' => $workDoneAmount,
                'taxable_amount' => $request->taxable_amount,
                'it_amount' => $itAmount,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'lwf_amount' => $lwfAmount,
                'others_amount' => $othersAmount,
                'withheld_amount' => $withheldAmount,
                'deduction' => $deduction,
                'collection_proof' => $collectionProofPath,
                'remarks' => $request->remarks,
                'total_amount' => $totalAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tender bill created successfully',
                'data' => [
                    'bill_id' => $billId,
                    'mc_id' => $mcId,
                    't_id' => $request->t_id,
                    'payment_type' => $request->payment_type,
                    'work_done_amount' => $workDoneAmount,
                    'deduction' => $deduction,
                    'total_amount' => $totalAmount,
                    'collection_proof' => $collectionProofPath,

                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Tender bill creation failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Tender bill creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Get All Tender Bills for Logged-in User
    public function getTenderBills(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $bills = DB::table('tender_bill')
            ->join('master_tender', 'tender_bill.t_id', '=', 'master_tender.id')
            ->where('tender_bill.mc_id', $mcId)
            ->select(
                'tender_bill.*',
                'master_tender.tender_no',
                'master_tender.project_name'
            )
            ->orderBy('tender_bill.created_at', 'desc')
            ->get();

        // Convert file fields to full URLs
        $baseUrl = url('public/');
        $fileFields = ['collection_proof']; // Add more fields here if needed

        foreach ($bills as $bill) {
            foreach ($fileFields as $field) {
                if (! empty($bill->$field)) {
                    $bill->$field = $baseUrl.'/'.ltrim($bill->$field, '/');
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $bills,
        ]);
    }

    // Get Bills by Tender ID
    public function getBillsByTender(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get tenderId from request body
        $tenderId = $request->input('tender_id');
        if (! $tenderId) {
            return response()->json([
                'success' => false,
                'message' => 'Tender ID is required',
            ], 400);
        }

        // Verify tender belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        $bills = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Convert file paths to URLs
        $baseUrl = url('public/');
        $fileFields = ['collection_proof']; // Add more file fields if any
        foreach ($bills as $bill) {
            foreach ($fileFields as $field) {
                if (! empty($bill->$field)) {
                    $bill->$field = $baseUrl.'/'.ltrim($bill->$field, '/');
                }
            }
        }

        // Calculate totals
        $totalWorkDone = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('work_done_amount');

        $totalAmount = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('total_amount');

        $totalDeductions = $totalWorkDone - $totalAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'tender_no' => $tender->tender_no,
                'project_name' => $tender->project_name,
                'total_work_done' => $totalWorkDone,
                'total_deductions' => $totalDeductions,
                'total_amount' => $totalAmount,
                'bills' => $bills,
            ],
        ]);
    }

    // Get Single Bill Details
    public function getBillById(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get bill_id from request body
        $billId = $request->input('bill_id');
        if (! $billId) {
            return response()->json([
                'success' => false,
                'message' => 'Bill ID is required',
            ], 400);
        }

        $bill = DB::table('tender_bill')
            ->join('master_tender', 'tender_bill.t_id', '=', 'master_tender.id')
            ->where('tender_bill.id', $billId)
            ->where('tender_bill.mc_id', $mcId)
            ->select(
                'tender_bill.*',
                'master_tender.tender_no',
                'master_tender.project_name'
            )
            ->first();

        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill record not found',
            ], 404);
        }

        // Convert file paths to full URL (if there are attachments)
        $baseUrl = url('public/');
        $fileFields = ['collection_proof']; // Add other file fields if exist
        foreach ($fileFields as $field) {
            if (! empty($bill->$field)) {
                $bill->$field = $baseUrl.'/'.ltrim($bill->$field, '/');
            }
        }

        return response()->json([
            'success' => true,
            'data' => $bill,
        ]);
    }

    // Update Tender Bill
    public function updateTenderBill(Request $request, $billId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Check if bill exists and belongs to user
        $bill = DB::table('tender_bill')
            ->where('id', $billId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill record not found',
            ], 404);
        }

        // Validate request
        $request->validate([
            'payment_type' => 'nullable|string|max:50',
            'work_done_amount' => 'nullable|numeric|min:0',
            'taxable_amount' => 'nullable|numeric|min:0',
            'it_amount' => 'nullable|numeric|min:0',
            'cgst_amount' => 'nullable|numeric|min:0',
            'sgst_amount' => 'nullable|numeric|min:0',
            'lwf_amount' => 'nullable|numeric|min:0',
            'others_amount' => 'nullable|numeric|min:0',
            'withheld_amount' => 'nullable|numeric|min:0',
            'collection_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'remarks' => 'nullable|string',
        ]);

        // Prepare update data
        $updateData = [];

        if ($request->has('payment_type')) {
            $updateData['payment_type'] = $request->payment_type;
        }
        if ($request->has('work_done_amount')) {
            $updateData['work_done_amount'] = $request->work_done_amount;
        }
        if ($request->has('taxable_amount')) {
            $updateData['taxable_amount'] = $request->taxable_amount;
        }
        if ($request->has('it_amount')) {
            $updateData['it_amount'] = $request->it_amount;
        }
        if ($request->has('cgst_amount')) {
            $updateData['cgst_amount'] = $request->cgst_amount;
        }
        if ($request->has('sgst_amount')) {
            $updateData['sgst_amount'] = $request->sgst_amount;
        }
        if ($request->has('lwf_amount')) {
            $updateData['lwf_amount'] = $request->lwf_amount;
        }
        if ($request->has('others_amount')) {
            $updateData['others_amount'] = $request->others_amount;
        }
        if ($request->has('withheld_amount')) {
            $updateData['withheld_amount'] = $request->withheld_amount;
        }
        if ($request->has('remarks')) {
            $updateData['remarks'] = $request->remarks;
        }

        // Handle file upload
        if ($request->hasFile('collection_proof')) {
            // Delete old file
            if ($bill->collection_proof && file_exists(public_path($bill->collection_proof))) {
                unlink(public_path($bill->collection_proof));
            }

            $file = $request->file('collection_proof');
            $filename = 'collection_proof_'.$mcId.'_'.$bill->t_id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('tender_bills/collection_proofs'), $filename);
            $updateData['collection_proof'] = 'tender_bills/collection_proofs/'.$filename;
        }

        // Recalculate total_amount if any amount field is updated
        if (count(array_intersect(['work_done_amount', 'it_amount', 'cgst_amount', 'sgst_amount', 'lwf_amount', 'others_amount', 'withheld_amount'], array_keys($updateData))) > 0) {
            $workDoneAmount = $updateData['work_done_amount'] ?? $bill->work_done_amount ?? 0;
            $itAmount = $updateData['it_amount'] ?? $bill->it_amount ?? 0;
            $cgstAmount = $updateData['cgst_amount'] ?? $bill->cgst_amount ?? 0;
            $sgstAmount = $updateData['sgst_amount'] ?? $bill->sgst_amount ?? 0;
            $lwfAmount = $updateData['lwf_amount'] ?? $bill->lwf_amount ?? 0;
            $othersAmount = $updateData['others_amount'] ?? $bill->others_amount ?? 0;
            $withheldAmount = $updateData['withheld_amount'] ?? $bill->withheld_amount ?? 0;

            $updateData['total_amount'] = $workDoneAmount - ($itAmount + $cgstAmount + $sgstAmount + $lwfAmount + $othersAmount + $withheldAmount);
        }

        $updateData['updated_at'] = now();

        try {
            DB::table('tender_bill')
                ->where('id', $billId)
                ->where('mc_id', $mcId)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Bill updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Bill update failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bill update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete Tender Bill
    public function deleteTenderBill(Request $request, $billId)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $bill = DB::table('tender_bill')
            ->where('id', $billId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill record not found',
            ], 404);
        }

        try {
            // Delete collection_proof file
            if ($bill->collection_proof && file_exists(public_path($bill->collection_proof))) {
                unlink(public_path($bill->collection_proof));
            }

            // Delete bill record
            DB::table('tender_bill')
                ->where('id', $billId)
                ->where('mc_id', $mcId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Bill deletion failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bill deletion failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getTenderProfile(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get tenderId from request body
        $tenderId = $request->input('tender_id');
        if (! $tenderId) {
            return response()->json([
                'success' => false,
                'message' => 'Tender ID is required',
            ], 400);
        }

        // Get tender details
        $tender = DB::table('master_tender as t')
            ->leftJoin('master_clients_sync as c', 't.authority', '=', 'c.id')
            ->where('t.id', $tenderId)
            ->where('t.mc_id', $mcId)
            ->select(
                't.*',
                DB::raw('c.nick_name as authority')
            )
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        // 🧩 Convert document paths to full URLs
        $baseUrl = url('public/');

        $fileFields = [
            'tendors_notes',
            'bg_emd_scans',
            'contract_agreements',
            'as_copy',
            'estimation_copy',
            'others',
        ];

        foreach ($fileFields as $field) {
            // if (! empty($tender->$field)) {
            //     $tender->$field = $baseUrl.'/'.ltrim($tender->$field, '/');
            // }
            if (! empty($tender->$field)) {
                $tender->$field = asset('/'.ltrim($tender->$field, '/'));
            }
        }

        // Get all collections
        $collections = DB::table('tender_collection')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->orderBy('collection_date', 'desc')
            ->get();

        $totalCollections = DB::table('tender_collection')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('amount');

        // Update collection file paths
        foreach ($collections as $c) {
            if (! empty($c->attachment)) {
                $c->attachment = asset('/'.ltrim($c->attachment, '/'));
            }
        }

        // Get all expenses
        $expenses = DB::table('tender_expense')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpenses = DB::table('tender_expense')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('amount');

        $expensesByCategory = DB::table('tender_expense')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->get();

        // Get all bills
        $bills = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($bills as $b) {
            if (! empty($b->collection_proof)) {
                $b->collection_proof = asset('/'.ltrim($b->collection_proof, '/'));
            }
        }

        // Calculate bill totals
        $totalWorkDone = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('work_done_amount');

        $totalBillAmount = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('total_amount');

        $totalDeductions = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->sum('withheld_amount');

        // 🔹 Get detailed sums from tender_bill
        $billSummary = DB::table('tender_bill')
            ->where('t_id', $tenderId)
            ->where('mc_id', $mcId)
            ->select(
                DB::raw('SUM(work_done_amount) as total_work_done'),
                DB::raw('SUM(taxable_amount) as total_taxable'),
                DB::raw('SUM(withheld_amount+cgst_amount+sgst_amount+it_amount+lwf_amount+others_amount) as total_deduction'),
                DB::raw('(SUM(cgst_amount) + SUM(sgst_amount)) as total_gst'),
                DB::raw('SUM(lwf_amount) as total_lwf'),
                DB::raw('SUM(others_amount) as total_others')
            )
            ->first();

        // Get status timeline
        $statusTimeline = DB::table('tender_status')
            ->where('t_id', $tenderId)
            ->orderBy('status_date', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'tender_value' => $tender->tender_value ?? 0,
            'bid_value' => $tender->bid_value ?? 0,
            'emd_value' => $tender->emd_value ?? 0,
            'total_collections' => $totalCollections ?? 0,
            'total_expenses' => $totalExpenses ?? 0,
            'total_work_done' => $totalWorkDone ?? 0,
            'total_deductions' => $totalDeductions ?? 0,
            'total_bill_amount' => $totalBillAmount ?? 0,
            'net_profit' => ($totalCollections ?? 0) - ($totalExpenses ?? 0),
        ];

        // 🧾 Merge tender details with tender_bill totals
        $tenderDetails = $tender;
        $tenderDetails->total_work_done = $billSummary->total_work_done ?? 0;
        $tenderDetails->total_taxable = $billSummary->total_taxable ?? 0;
        $tenderDetails->total_deduction = $billSummary->total_deduction ?? 0;
        $tenderDetails->total_gst = $billSummary->total_gst ?? 0;
        $tenderDetails->total_lwf = $billSummary->total_lwf ?? 0;
        $tenderDetails->total_others = $billSummary->total_others ?? 0;

        // ✅ Final response
        return response()->json([
            'success' => true,
            'data' => [
                'tender_details' => $tenderDetails,
                'summary' => $summary,
                'collections' => [
                    'total' => $totalCollections ?? 0,
                    'count' => count($collections),
                    'records' => $collections,
                ],
                'expenses' => [
                    'total' => $totalExpenses ?? 0,
                    'count' => count($expenses),
                    'by_category' => $expensesByCategory,
                    'records' => $expenses,
                ],
                'bills' => [
                    'total_work_done' => $totalWorkDone ?? 0,
                    'total_deductions' => $totalDeductions ?? 0,
                    'total_amount' => $totalBillAmount ?? 0,
                    'count' => count($bills),
                    'records' => $bills,
                ],
                'status_timeline' => [
                    'count' => count($statusTimeline),
                    'records' => $statusTimeline,
                ],
            ],
        ]);
    }

    public function updateTenderStatus(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate inputs
        $validated = $request->validate([
            'tender_id' => 'required|integer',
            'status' => 'required|string|max:100',
            'date' => 'required|date',
        ]);

        $tenderId = $validated['tender_id'];
        $status = $validated['status'];
        $date = $validated['date'];

        // Check if tender exists for this user
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            // 1️⃣ Update status in master_tender
            DB::table('master_tender')
                ->where('id', $tenderId)
                ->update([
                    'status' => $status,
                    'updated_at' => now(),
                ]);

            // 2️⃣ Insert into tender_status log table
            DB::table('tender_status')->insert([
                't_id' => $tenderId,
                'status' => $status,
                'status_date' => $date,
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tender status updated successfully.',
                'data' => [
                    'tender_id' => $tenderId,
                    'status' => $status,
                    'date' => $date,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating tender status: '.$e->getMessage(),
            ], 500);
        }
    }

    // Get Tender Details for Editing
    // public function getTenderForEdit(Request $request)
    // {
    //     $mcId = $this->validateTokenAndGetMcId($request);

    //     if (!$mcId) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid or expired token. Please login again.'
    //         ], 401);
    //     }

    //     // Validate tender_id in request body
    //     $request->validate([
    //         'tender_id' => 'required|integer'
    //     ]);

    //     $tenderId = $request->tender_id;
    //     $tender = DB::table('master_tender as t')
    //         ->leftJoin('master_clients_sync as c', 't.authority', '=', 'c.id')
    //         ->where('t.id', $tenderId)
    //         ->where('t.mc_id', $mcId)
    //         ->select(
    //             't.*',
    //             DB::raw('c.nick_name as authority') // or 'c.client_name'
    //         )
    //         ->first();
    //     if (!$tender) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tender not found'
    //         ], 404);
    //     }

    //     // Convert file paths to full URLs
    //     $baseUrl = url('/');

    //     $fileFields = [
    //         'tendors_notes',
    //         'bg_emd_scans',
    //         'contract_agreements',
    //         'as_copy',
    //         'estimation_copy',
    //         'others'
    //     ];

    //     foreach ($fileFields as $field) {
    //         if (!empty($tender->$field)) {
    //             $tender->$field = $baseUrl . '/' . ltrim($tender->$field, '/');
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Tender details fetched successfully',
    //         'data' => $tender
    //     ]);
    // }
    public function getTenderForEdit(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate tender_id in request body
        $request->validate([
            'tender_id' => 'required|integer',
        ]);

        $tenderId = $request->tender_id;

        // Fetch tender record directly (no join)
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        // Convert file paths to full URLs
        $baseUrl = url('public/');

        $fileFields = [
            'tendors_notes',
            'bg_emd_scans',
            'contract_agreements',
            'as_copy',
            'estimation_copy',
            'others',
        ];

        foreach ($fileFields as $field) {
            if (! empty($tender->$field)) {
                $tender->$field = $baseUrl.'/'.ltrim($tender->$field, '/');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tender details fetched successfully',
            'data' => $tender,
        ]);
    }

    // Update Tender Details
    public function updateTender(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate tender_id in request body
        $request->validate([
            'tender_id' => 'required|integer',
        ]);

        $tenderId = $request->tender_id;

        // Check if tender exists and belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        // Validate request
        $request->validate([
            'tender_no' => 'required|string|max:100',
            'project_name' => 'required|string|max:255',
            'contractor' => 'required|string|max:255',
            'authority' => 'required|string|max:255',
            'scheme' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'remainder_date' => 'nullable|date',
            'as_no' => 'nullable|string|max:100',
            'as_date' => 'nullable|date',
            'ts_date' => 'nullable|date',
            'tender_value' => 'required|numeric',
            'bid_value' => 'nullable|numeric',
            'emd_value' => 'required|numeric',
            'gst_applicable' => 'required|string|max:10',
            'hsn_code' => 'required|string|max:50',
            'year_end_date' => 'nullable|string|max:50',
            'emd_type' => 'required|string|max:100',
            'emd_date' => 'required|date',
            'reference_id' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'tendors_notes' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'bg_emd_scans' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'contract_agreements' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'as_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'estimation_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'others' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // Prepare update data
        $updateData = [];

        // Update text fields
        if ($request->has('tender_no')) {
            $updateData['tender_no'] = $request->tender_no;
        }
        if ($request->has('project_name')) {
            $updateData['project_name'] = $request->project_name;
        }
        if ($request->has('contractor')) {
            $updateData['contractor'] = $request->contractor;
        }
        if ($request->has('authority')) {
            $updateData['authority'] = $request->authority;
        }
        if ($request->has('scheme')) {
            $updateData['scheme'] = $request->scheme;
        }
        if ($request->has('location')) {
            $updateData['location'] = $request->location;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->has('remainder_date')) {
            $updateData['remainder_date'] = $request->remainder_date;
        }
        if ($request->has('as_no')) {
            $updateData['as_no'] = $request->as_no;
        }
        if ($request->has('as_date')) {
            $updateData['as_date'] = $request->as_date;
        }
        if ($request->has('ts_date')) {
            $updateData['ts_date'] = $request->ts_date;
        }
        if ($request->has('tender_value')) {
            $updateData['tender_value'] = $request->tender_value;
        }
        if ($request->has('bid_value')) {
            $updateData['bid_value'] = $request->bid_value;
        }
        if ($request->has('emd_value')) {
            $updateData['emd_value'] = $request->emd_value;
        }
        if ($request->has('gst_applicable')) {
            $updateData['gst_applicable'] = $request->gst_applicable;
        }
        if ($request->has('hsn_code')) {
            $updateData['hsn_code'] = $request->hsn_code;
        }
        if ($request->has('year_end_date')) {
            $updateData['year_end_date'] = $request->year_end_date;
        }
        if ($request->has('emd_type')) {
            $updateData['emd_type'] = $request->emd_type;
        }
        if ($request->has('emd_date')) {
            $updateData['emd_date'] = $request->emd_date;
        }
        if ($request->has('reference_id')) {
            $updateData['reference_id'] = $request->reference_id;
        }
        if ($request->has('bank_name')) {
            $updateData['bank_name'] = $request->bank_name;
        }
        if ($request->has('account_no')) {
            $updateData['account_no'] = $request->account_no;
        }
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        // Handle file uploads
        $fileFields = [
            'tendors_notes' => 'tendors_notes',
            'bg_emd_scans' => 'bg_emd_scans',
            'contract_agreements' => 'contract_agreements',
            'as_copy' => 'as_copy',
            'estimation_copy' => 'estimation_copy',
            'others' => 'others',
        ];

        foreach ($fileFields as $fieldName => $folderName) {
            if ($request->hasFile($fieldName)) {
                // Delete old file if exists
                if ($tender->$fieldName) {
                    $oldFilePath = public_path($tender->$fieldName);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Upload new file
                $file = $request->file($fieldName);
                $filename = $fieldName.'_'.$mcId.'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('tender_documents/'.$folderName), $filename);
                $updateData[$fieldName] = 'tender_documents/'.$folderName.'/'.$filename;
            }
        }

        $updateData['updated_at'] = now();

        try {
            DB::table('master_tender')
                ->where('id', $tenderId)
                ->where('mc_id', $mcId)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Tender updated successfully',
                'data' => [
                    'tender_id' => $tenderId,
                    'updated_fields' => array_keys($updateData),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Tender update failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Tender update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Delete Tender (Optional)
    public function deleteTender(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate tender_id in request body
        $request->validate([
            'tender_id' => 'required|integer',
        ]);

        $tenderId = $request->tender_id;

        $tender = DB::table('master_tender')
            ->where('id', $tenderId)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found',
            ], 404);
        }

        try {
            // Delete all associated files
            $fileFields = [
                'tendors_notes',
                'bg_emd_scans',
                'contract_agreements',
                'as_copy',
                'estimation_copy',
                'others',
            ];

            foreach ($fileFields as $field) {
                if ($tender->$field && file_exists(public_path($tender->$field))) {
                    unlink(public_path($tender->$field));
                }
            }

            // Delete tender record
            DB::table('master_tender')
                ->where('id', $tenderId)
                ->where('mc_id', $mcId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tender deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Tender deletion failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Tender deletion failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getClientNickNames(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get all synced clients with nick names for this user
        $clients = DB::table('master_clients_sync')
            ->where('mc_id', $mcId)
            ->whereNotNull('nick_name')  // Only get records with nick names
            ->select('id', 'nick_name', 'gst_no', 'business_legalname', 'business_type', 'city', 'state')
            ->orderBy('nick_name', 'asc')
            ->get();

        if ($clients->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No clients found',
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Client nick names fetched successfully',
            'count' => $clients->count(),
            'data' => $clients,
        ]);
    }

    public function getSyncedClients(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $clients = DB::table('master_clients_sync')
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($clients->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No synced clients found',
                'count' => 0,
                'data' => [],
            ]);
        }

        $formattedClients = $clients->map(function ($client) use ($mcId) {

            // Get all tenders for this client
            $tenders = DB::table('master_tender')
                ->where('mc_id', $mcId)
                ->where('authority', $client->id)
                ->get();

            $projectCount = $tenders->count();

            $billedValue = $tenders->sum('tender_value') ?? 0;

            // Get total billed amount from tender_bill for all tenders of this client
            $totalBilledAmount = DB::table('tender_bill')
                ->where('mc_id', $mcId)
                ->whereIn('t_id', $tenders->pluck('id'))
                ->sum('total_amount') ?? 0;

            // Pending amount = tender_value - total billed amount
            $pendingAmount = $billedValue - $totalBilledAmount;

            return [
                'id' => $client->id,
                'mc_db_id' => $client->mc_db_id,
                'gst_no' => $client->gst_no,
                'nick_name' => $client->nick_name,
                'business_legalname' => $client->business_legalname,
                'business_type' => $client->business_type,
                'register_date' => $client->register_date,
                'promotors_name' => $client->promotors_name,
                'income_annual' => $client->income_annual,
                'pan_no' => $client->pan_no,
                'phone_number' => $client->phone_number,
                'email' => $client->email,
                'address' => $client->address,
                'city' => $client->city,
                'state' => $client->state,
                'pincode' => $client->pincode,
                'project_count' => $projectCount,
                'billed_value' => $billedValue,
                'status' => $client->status,
                'pending_amount' => $pendingAmount,
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at,

            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Synced clients fetched successfully',
            'count' => $clients->count(),
            'data' => $formattedClients,
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        DB::table('master_clients')
            ->where('id', $mcId)
            ->update([
                'fcm_token' => $request->fcm_token,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully.',
        ]);
    }

    public function listNotifications(Request $request)
    {
        // Validate token and get mc_id (your existing helper)
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Fetch notifications for this user
        $notifications = DB::table('notifications')
            ->where('mc_id', $mcId)
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'title',
                'message',
                'type',
                'related_id',
                'status',
                'created_at',
            ]);

        return response()->json([
            'success' => true,
            'count' => $notifications->count(),
            'data' => $notifications,
        ]);
    }

    /**
     * (Optional) Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        $request->validate([
            'notification_id' => 'required|integer',
        ]);

        $updated = DB::table('notifications')
            ->where('id', $request->notification_id)
            ->where('mc_id', $mcId)
            ->update(['status' => 'read', 'updated_at' => now()]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found or already read.',
        ]);
    }

    public function collectBillAmount(Request $request)
    {

        Log::info('collect_amount', ['collect_amt' => $request->all()]);
        // Get mc_id from token/login
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate request
        $request->validate([
            'bill_id' => 'required|integer|exists:tender_bill,id',
            't_id' => 'required|integer|exists:master_tender,id',
            'bill_date' => 'required|date',
            'amount' => 'required|numeric',
            'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'remark' => 'nullable|string',
        ]);

        $attachmentPath = null;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'bill_attachment_'.$mcId.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('collect_bill_attachments'), $filename);
            $attachmentPath = 'collect_bill_attachments/'.$filename;
        }

        try {
            // DB::beginTransaction();

            // 1️⃣ Insert collect bill record
            $id = DB::table('collectbillamount')->insertGetId([
                'mc_id' => $mcId,
                't_id' => $request->t_id,
                'bill_id' => $request->bill_id,
                'bill_date' => $request->bill_date,
                'amount' => $request->amount,
                'attachment' => $attachmentPath,
                'remark' => $request->remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2️⃣ Update tender_bill status to 'collected'
            DB::table('tender_bill')
                ->where('id', $request->bill_id)
                ->update(['status' => 'collected', 'updated_at' => now()]);

            // DB::commit();

            // 3️⃣ Return response with full attachment URL
            $attachmentUrl = $attachmentPath ? url($attachmentPath) : null;

            return response()->json([
                'success' => true,
                'message' => 'Bill amount collected successfully',
                // 'data' => [
                //     'id' => $id,
                //     'mc_id' => $mcId,
                //     't_id' => $request->t_id,
                //     'bill_id' => $request->bill_id,
                //     'bill_date' => $request->bill_date,
                //     'amount' => $request->amount,
                //     'attachment' => $attachmentUrl,
                //     'remark' => $request->remark,
                // ],
            ]);
        } catch (\Exception $e) {
            // DB::rollBack();
            Log::info('error data', ['error_collect_amt' => $e->getMessage(), 'error_line' => $e->getLine()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to collect bill amount: '.$e->getMessage(),
            ], 500);
        }
    }

    public function createEmdRemainder(Request $request)
    {
        // Validate token and get mc_id
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Validate request
        $request->validate([
            't_id' => 'required|exists:master_tender,id',
            'b_id' => 'required|exists:tender_bill,id',
            'remainder_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Verify tender belongs to user
        $tender = DB::table('master_tender')
            ->where('id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return response()->json([
                'success' => false,
                'message' => 'Tender not found or does not belong to you',
            ], 404);
        }

        // Verify bill belongs to user and tender
        $bill = DB::table('tender_bill')
            ->where('id', $request->b_id)
            ->where('t_id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found or does not belong to this tender',
            ], 404);
        }

        // Get client info for notification
        $client = DB::table('master_clients')
            ->where('id', $mcId)
            ->select('phone_number', 'fcm_token', 'business_legalname')
            ->first();

        DB::beginTransaction();
        try {

            $rem_gen_date = Carbon::parse($request->remainder_date)->addDays(30);
            // 1️⃣ Insert EMD Remainder record
            $reminderId = DB::table('emd_remainder')->insertGetId([
                'mc_id' => $mcId,
                't_id' => $request->t_id,
                'b_id' => $request->b_id,
                'remainder_date' => $request->remainder_date,
                'rem_gen_date' => $rem_gen_date,
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2️⃣ Prepare notification data
            // $title = 'EMD Remainder Created';
            // $body = "EMD Remainder set for Tender #{$tender->tender_no} on {$request->remainder_date}";

            // if ($request->notes) {
            //     $body .= " - {$request->notes}";
            // }

            // 3️⃣ Insert notification into notifications table
            // $notificationId = DB::table('notifications')->insertGetId([
            //     'mc_id' => $mcId,
            //     'title' => $title,
            //     'message' => $body,
            //     'type' => 'emd_remainder',
            //     'related_id' => $reminderId,
            //     'status' => 'unread',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);

            // 4️⃣ Insert into remainder table (new table for general reminders)
            // $generalReminderId = DB::table('remainder')->insertGetId([
            //     'mc_id' => $mcId,
            //     'reminder_type' => 'emd_remainder',
            //     'related_id' => $reminderId,
            //     't_id' => $request->t_id,
            //     'b_id' => $request->b_id,
            //     'reminder_date' => $request->remainder_date,
            //     'title' => $title,
            //     'description' => $body,
            //     'notes' => $request->notes,
            //     'status' => 'pending', // pending, completed, cancelled
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);

            // 5️⃣ Send FCM notification if token exists
            // if ($client && ! empty($client->fcm_token)) {
            //     try {
            //         $fcm = new Fcm;
            //         $fcm->send_notify($client->fcm_token, $title, $body);
            //         Log::info("✅ FCM Notification sent to mc_id {$mcId} for EMD Remainder");
            //     } catch (\Throwable $e) {
            //         Log::error('❌ Failed to send FCM for EMD Remainder: '.$e->getMessage());
            //     }
            // } else {
            //     Log::warning("⚠️ No FCM token found for mc_id {$mcId}");
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'EMD Remainder created successfully with notification',
                // 'data' => [
                //     'emd_remainder_id' => $reminderId,
                //     'notification_id' => $notificationId,
                //     'reminder_id' => $generalReminderId,
                //     'mc_id' => $mcId,
                //     't_id' => $request->t_id,
                //     'b_id' => $request->b_id,
                //     'remainder_date' => $request->remainder_date,
                //     'notes' => $request->notes,
                //     'tender_no' => $tender->tender_no,
                //     'project_name' => $tender->project_name,
                // ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EMD Remainder creation failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'EMD Remainder creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getAllReminders(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $reminders = DB::table('remainder as r')
            ->leftJoin('master_tender as t', 'r.t_id', '=', 't.id')
            ->leftJoin('tender_bill as b', 'r.b_id', '=', 'b.id')
            ->where('r.mc_id', $mcId)
            ->select(
                'r.*',
                't.tender_no',
                't.project_name',
                'b.payment_type',
                'b.total_amount as bill_amount'
            )
            ->orderBy('r.reminder_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $reminders->count(),
            'data' => $reminders,
        ]);
    }

    // Get Active Reminders for Today (for popup display)
    public function getActiveReminders(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $now = now();
        $today = $now->format('Y-m-d');

        // Get reminders that should be shown now
        $reminders = DB::table('remainder as r')
            ->leftJoin('master_tender as t', 'r.t_id', '=', 't.id')
            ->leftJoin('tender_bill as b', 'r.b_id', '=', 'b.id')
            ->where('r.mc_id', $mcId)
            ->where('r.status', 'pending')
            ->where('r.is_seen', false)
            ->where(function ($query) use ($today, $now) {
                $query->where(function ($q) use ($today) {
                    // Original reminder date is today or past
                    $q->where('r.reminder_date', '<=', $today)
                        ->whereNull('r.next_reminder_at');
                })
                    ->orWhere(function ($q) use ($now) {
                        // Snoozed reminder time has arrived
                        $q->whereNotNull('r.next_reminder_at')
                            ->where('r.next_reminder_at', '<=', $now);
                    });
            })
            ->select(
                'r.*',
                't.tender_no',
                't.project_name',
                'b.payment_type',
                'b.total_amount as bill_amount'
            )
            ->orderBy('r.reminder_date', 'asc')
            ->get();

        // Update last_shown_at for these reminders
        if ($reminders->isNotEmpty()) {
            $reminderIds = $reminders->pluck('id')->toArray();
            DB::table('remainder')
                ->whereIn('id', $reminderIds)
                ->update(['last_shown_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'count' => $reminders->count(),
            'show_popup' => $reminders->isNotEmpty(),
            'data' => $reminders,
        ]);
    }

    // Mark Reminder as Seen/Updated
    public function markReminderAsSeen(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $request->validate([
            'reminder_id' => 'required|integer',
        ]);

        $reminder = DB::table('remainder')
            ->where('id', $request->reminder_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $reminder) {
            return response()->json([
                'success' => false,
                'message' => 'Reminder not found',
            ], 404);
        }

        try {
            DB::table('remainder')
                ->where('id', $request->reminder_id)
                ->where('mc_id', $mcId)
                ->update([
                    'is_seen' => true,
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder marked as seen and completed',
            ]);
        } catch (\Exception $e) {
            Log::error('Mark reminder as seen failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update reminder: '.$e->getMessage(),
            ], 500);
        }
    }

    // Snooze Reminder (Ping me in X days)
    public function snoozeReminder(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $request->validate([
            'reminder_id' => 'required|integer',
            'snooze_days' => 'required|integer|min:1|max:30', // 1 to 30 days
        ]);

        $reminder = DB::table('remainder')
            ->where('id', $request->reminder_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $reminder) {
            return response()->json([
                'success' => false,
                'message' => 'Reminder not found',
            ], 404);
        }

        try {
            // Calculate next reminder time
            $nextReminderAt = now()->addDays($request->snooze_days);

            DB::table('remainder')
                ->where('id', $request->reminder_id)
                ->where('mc_id', $mcId)
                ->update([
                    'next_reminder_at' => $nextReminderAt,
                    'snooze_count' => DB::raw('snooze_count + 1'),
                    'is_seen' => true, // Hide current popup
                    'updated_at' => now(),
                ]);

            // Create a notification for the snooze
            DB::table('notifications')->insert([
                'mc_id' => $mcId,
                'title' => 'Reminder Snoozed',
                'message' => "Reminder snoozed for {$request->snooze_days} days. You'll be reminded on ".$nextReminderAt->format('Y-m-d'),
                'type' => 'reminder_snooze',
                'related_id' => $request->reminder_id,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Reminder snoozed for {$request->snooze_days} days",
                'data' => [
                    'reminder_id' => $request->reminder_id,
                    'snooze_days' => $request->snooze_days,
                    'next_reminder_at' => $nextReminderAt->format('Y-m-d H:i:s'),
                    'snooze_count' => $reminder->snooze_count + 1,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Snooze reminder failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to snooze reminder: '.$e->getMessage(),
            ], 500);
        }
    }

    // Dismiss Reminder (without completing)
    public function dismissReminder(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $request->validate([
            'reminder_id' => 'required|integer',
        ]);

        try {
            DB::table('remainder')
                ->where('id', $request->reminder_id)
                ->where('mc_id', $mcId)
                ->update([
                    'is_seen' => true,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder dismissed',
            ]);
        } catch (\Exception $e) {
            Log::error('Dismiss reminder failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss reminder: '.$e->getMessage(),
            ], 500);
        }
    }

    // Get Reminder Statistics
    public function getReminderStats(Request $request)
    {
        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        $today = now()->format('Y-m-d');

        $stats = [
            'total_pending' => DB::table('remainder')
                ->where('mc_id', $mcId)
                ->where('status', 'pending')
                ->count(),

            'overdue' => DB::table('remainder')
                ->where('mc_id', $mcId)
                ->where('status', 'pending')
                ->where('reminder_date', '<', $today)
                ->whereNull('next_reminder_at')
                ->count(),

            'today' => DB::table('remainder')
                ->where('mc_id', $mcId)
                ->where('status', 'pending')
                ->where('reminder_date', '=', $today)
                ->count(),

            'upcoming' => DB::table('remainder')
                ->where('mc_id', $mcId)
                ->where('status', 'pending')
                ->where('reminder_date', '>', $today)
                ->count(),

            'completed' => DB::table('remainder')
                ->where('mc_id', $mcId)
                ->where('status', 'completed')
                ->count(),

            'snoozed' => DB::table('remainder')
                ->where('mc_id', $mcId)
                ->where('status', 'pending')
                ->whereNotNull('next_reminder_at')
                ->where('next_reminder_at', '>', now())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Generate Invoice with Complete Details
    public function generateInvoice(Request $request)
    {
        log::info('generate_bill-4280', ['request' => $request->all()]);

        $mcId = $this->validateTokenAndGetMcId($request);

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Get bill_id from request body
        $billId = $request->input('bill_id');
        if (! $billId) {
            return response()->json([
                'success' => false,
                'message' => 'Bill ID is required',
            ], 400);
        }

        // Get bill details with tender information
        $bill = DB::table('tender_bill as tb')
            ->join('master_tender as mt', 'tb.t_id', '=', 'mt.id')
            ->where('tb.id', $billId)
            ->where('tb.mc_id', $mcId)
            ->select(
                'tb.*',
                'mt.tender_no',
                'mt.project_name',
                'mt.contractor',
                'mt.authority',
                'mt.scheme',
                'mt.location',
                'mt.status as tender_status',
                'mt.as_no',
                'mt.as_date',
                'mt.ts_date',
                'mt.tender_value',
                'mt.bid_value',
                'mt.emd_value',
                'mt.gst_applicable',
                'mt.hsn_code',
                'mt.year_end_date',
                'mt.emd_type',
                'mt.emd_date',
                'mt.reference_id',
                'mt.bank_name',
                'mt.account_no'
            )
            ->first();

        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill record not found',
            ], 404);
        }

        // Get logged-in user details (client/contractor)
        $client = DB::table('master_clients')
            ->where('id', $mcId)
            ->select(
                'id as mc_id',
                'gst_number',
                'business_legalname',
                'promotors_name',
                'pan_number',
                'phone_number',
                'email',
                'address',
                'turn_over',
                'contractor_type'
            )
            ->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Client details not found',
            ], 404);
        }

        // Get authority/customer details if exists
        $authority = null;
        if ($bill->authority) {
            $authority = DB::table('master_clients_sync')
                ->where('id', $bill->authority)
                ->select(
                    'id',
                    'gst_no',
                    'business_legalname',
                    'promotors_name',
                    'pan_no',
                    'phone_number',
                    'email',
                    'address',
                    'city',
                    'state',
                    'pincode',
                )
                ->first();
        }

        // Convert file paths to full URLs
        $baseUrl = url('public/');
        $fileFields = ['collection_proof'];
        foreach ($fileFields as $field) {
            if (! empty($bill->$field)) {
                $bill->$field = $baseUrl.'/'.ltrim($bill->$field, '/');
            }
        }

        // Calculate bill breakdown
        $workDoneAmount = $bill->work_done_amount ?? 0;
        $taxableAmount = $bill->taxable_amount ?? 0;
        $itAmount = $bill->it_amount ?? 0;
        $cgstAmount = $bill->cgst_amount ?? 0;
        $sgstAmount = $bill->sgst_amount ?? 0;
        $lwfAmount = $bill->lwf_amount ?? 0;
        $othersAmount = $bill->others_amount ?? 0;
        $withheldAmount = $bill->withheld_amount ?? 0;
        $totalAmount = $bill->total_amount ?? 0;

        // Calculate total deductions
        $totalDeductions = $itAmount + $cgstAmount + $sgstAmount + $lwfAmount + $othersAmount + $withheldAmount;

        // Get all collections for this bill (if any)
        $collections = DB::table('collectbillamount')
            ->where('bill_id', $billId)
            ->where('mc_id', $mcId)
            ->select('id', 'bill_date', 'amount', 'attachment', 'remark', 'created_at')
            ->orderBy('bill_date', 'desc')
            ->get();

        // Convert collection attachments to URLs
        foreach ($collections as $collection) {
            if (! empty($collection->attachment)) {
                $collection->attachment = $baseUrl.'/'.ltrim($collection->attachment, '/');
            }
        }

        $totalCollected = $collections->sum('amount');
        $pendingAmount = $totalAmount - $totalCollected;

        // Prepare invoice response
        $invoiceData = [
            // Invoice metadata
            'invoice_number' => 'INV-'.$bill->id.'-'.date('Ymd'),
            'invoice_date' => now()->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),

            // Bill details
            'bill_details' => [
                'bill_id' => $bill->id,
                'payment_type' => $bill->payment_type,
                'work_done_amount' => $workDoneAmount,
                'taxable_amount' => $taxableAmount,
                'it_amount' => $itAmount,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'lwf_amount' => $lwfAmount,
                'others_amount' => $othersAmount,
                'withheld_amount' => $withheldAmount,
                'total_deductions' => $totalDeductions,
                'total_amount' => $totalAmount,
                'collection_proof' => $bill->collection_proof,
                'remarks' => $bill->remarks,
                'created_at' => $bill->created_at,
            ],

            // Tender details
            'tender_details' => [
                't_id' => $bill->t_id,
                'tender_no' => $bill->tender_no,
                'project_name' => $bill->project_name,
                'contractor' => $bill->contractor,
                'scheme' => $bill->scheme,
                'location' => $bill->location,
                'tender_status' => $bill->tender_status,
                'as_no' => $bill->as_no,
                'as_date' => $bill->as_date,
                'ts_date' => $bill->ts_date,
                'tender_value' => $bill->tender_value,
                'bid_value' => $bill->bid_value,
                'emd_value' => $bill->emd_value,
                'gst_applicable' => $bill->gst_applicable,
                'hsn_code' => $bill->hsn_code,
                'year_end_date' => $bill->year_end_date,
                'emd_type' => $bill->emd_type,
                'emd_date' => $bill->emd_date,
                'reference_id' => $bill->reference_id,
                'bank_name' => $bill->bank_name,
                'account_no' => $bill->account_no,
            ],

            // Contractor/Client details (From)
            'contractor_details' => [
                'mc_id' => $client->mc_id,
                'gst_number' => $client->gst_number,
                'business_name' => $client->business_legalname,
                'promotors_name' => $client->promotors_name,
                'pan_number' => $client->pan_number,
                'phone_number' => $client->phone_number,
                'email' => $client->email,
                'address' => $client->address,
                'contractor_type' => $client->contractor_type,
            ],

            // Authority/Customer details (To)
            'authority_details' => $authority ? [
                'id' => $authority->id,
                'gst_no' => $authority->gst_no,
                'business_name' => $authority->business_legalname,
                'promotors_name' => $authority->promotors_name,
                'pan_no' => $authority->pan_no,
                'phone_number' => $authority->phone_number,
                'email' => $authority->email,
                'address' => $authority->address,
                'city' => $authority->city,
                'state' => $authority->state,
                'pincode' => $authority->pincode,
            ] : null,

            // Collection summary
            'collection_summary' => [
                'total_bill_amount' => $totalAmount,
                'total_collected' => $totalCollected,
                'pending_amount' => $pendingAmount,
                'collection_count' => $collections->count(),
                'collections' => $collections,
            ],

            // Payment breakdown
            // 'payment_breakdown' => [
            //     [
            //         'description' => 'Work Done Amount',
            //         'amount' => $workDoneAmount
            //     ],
            //     [
            //         'description' => 'Taxable Amount',
            //         'amount' => $taxableAmount
            //     ],
            //     [
            //         'description' => 'Income Tax (IT)',
            //         'amount' => -$itAmount
            //     ],
            //     [
            //         'description' => 'CGST',
            //         'amount' => -$cgstAmount
            //     ],
            //     [
            //         'description' => 'SGST',
            //         'amount' => -$sgstAmount
            //     ],
            //     [
            //         'description' => 'LWF',
            //         'amount' => -$lwfAmount
            //     ],
            //     [
            //         'description' => 'Others',
            //         'amount' => -$othersAmount
            //     ],
            //     [
            //         'description' => 'Withheld Amount',
            //         'amount' => -$withheldAmount
            //     ],
            //     [
            //         'description' => 'Net Payable',
            //         'amount' => $totalAmount,
            //         'is_total' => true
            //     ]
            // ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Invoice generated successfully',
            'data' => $invoiceData,
        ]);
    }

    public function edit_client(Request $request, $client_id = null)
    {

        if (isset($client_id)) {

            $master_sync = DB::table('master_clients_sync')->where('id', $client_id)->first();

            $data = [
                'client_id' => $master_sync->id,
                'business_legalname' => $master_sync->business_legalname,
                'promotors_name' => $master_sync->promotors_name,
                'pan_no' => $master_sync->pan_no,
                'email' => $master_sync->email,
                'address' => $master_sync->address,
                'city' => $master_sync->city,
                'state' => $master_sync->state,
                'pincode' => $master_sync->pincode,
                'nick_name' => $master_sync->nick_name,

            ];

            return response()->json([
                'success' => true,
                'message' => 'Client Datas',
                'data' => $data,
            ]);

        }

        // log::info($request->all());

        try {
            // Validate input
            $request->validate([
                // 'user_phone' => 'required|string',
                // 'client_phone' => 'required|string',
                'business_legalname' => 'required|string',
                'promotors_name' => 'nullable|string',
                'pan_number' => 'nullable|string',
                'email' => 'nullable|string',
                'nick_name' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'pincode' => 'nullable|string',
                'client_id' => 'required',
            ]);

            $master_sync = DB::table('master_clients_sync')->where('id', $request->client_id)->first();

            // STEP 2: Save CLIENT details (NOT login user number)
            $syncId = DB::table('master_clients_sync')->where('id', $request->client_id)->update([
                // 'mc_id' => $userid->id ?? null,
                // 'mc_db_id' => $master_client_db ?? null,                         // link to master_clients
                'business_legalname' => $request->business_legalname,
                'promotors_name' => $request->promotors_name,
                'pan_no' => $request->pan_number,
                // 'phone_number' => $request->client_phone,        // SAVE CLIENT NUMBER
                'email' => $request->email,
                'nick_name' => $request->nick_name,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,

                // 'created_at' => now(),
                'updated_at' => now(),
            ]);

            $master_client_db = DB::table('master_clients_db')->where('id', $master_sync->mc_db_id)->update([

                'legal_name' => $request->business_legalname,
                'promoters' => $request->promotors_name,
                'pan_number' => $request->pan_number,
                'email' => $request->email,
                'address' => $request->address,
                'updated_at' => now(),
            ]);

        } catch (\Exception $e) {
            log::info('error', ['error' => $e->getMessage(), 'Line' => $e->getLine()]);
        }

        // STEP 3: Response
        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            // 'sync_id' => $syncId,
            // 'data' => [
            //     'mc_db_id' => $user->id ?? null,
            //     'business_legalname' => $request->business_legalname,
            //     'promotors_name' => $request->promotors_name,
            //     'pan_no' => $request->pan_number,
            //     'client_phone' => $request->client_phone,
            //     'email' => $request->email,
            //     'address' => $request->address,
            //     'nick_name' => $request->nick_name,

            //     'city' => $request->city,
            //     'state' => $request->state,
            //     'pincode' => $request->pincode,

            // ],
        ]);
    }

    // function for collect teh deposit amount

    public function collect_notify(Request $request)
    {

        $tender = DB::table('master_tender')->where('id', $request->tender_id)->first();

        $user = DB::table('master_clients')->where('id', $tender->mc_id)->first();

        $data = [
            'title' => 'Deposit Amount Collected',
            'body' => 'Deposit amount has been collected for Tender No: '.$tender->tender_no,

        ];

        $update_status = DB::table('emd_remainder')->where('mc_id', $tender->mc_id)->where('t_id', $tender->id)->update([

            'status' => 'collected',
            'updated_at' => now(),

        ]);

        $update_tender = DB::table('master_tender')->where('id', $request->tender_id)->update([

            'tender_status' => 'collected',
            'updated_at' => now(),

        ]);

        DB::table('notifications')->insert([

            'mc_id' => $tender->mc_id,
            'title' => 'Deposit Amount Collected',
            'message' => 'Deposit amount has been collected for Tender No: '.$tender->tender_no,
            'type' => 'emd_collection',
            'related_id' => $tender->id,
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        if (! empty($user->fcm_token)) {
            $fcm = new Fcm;
            $fcm->send_notify($user->fcm_token, $data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
        ]);
        // $fcm->send_notify('fcm_token_here', 'Test Title', 'Test Body');
    }
}
