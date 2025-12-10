<?php

namespace App\Http\Controllers\web;

use App\Models\MasterClientsSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ClientControllerWeb
{
    // public function client_list()
    // {

    //         $user = auth()->user()->id;

    //     $client = MasterClientsSync::where('mc_id', $user->id)->get();


    //     $formattedClients = $client->map(function ($cl) use ($user->id) {

    //             // Get all tenders for this client
    //             $tenders = DB::table('master_tender')
    //                 ->where('mc_id', $user->id)
    //                 ->where('authority', $cl->id)
    //                 ->get();

    //             $projectCount = $tenders->count();

    //             $billedValue = $tenders->sum('tender_value') ?? 0;

    //             // Get total billed amount from tender_bill for all tenders of this client
    //             $totalBilledAmount = DB::table('tender_bill')
    //                 ->where('mc_id', $user->id)
    //                 ->whereIn('t_id', $tenders->pluck('id'))
    //                 ->sum('total_amount') ?? 0;

    //             // Pending amount = tender_value - total billed amount
    //             $pendingAmount = $billedValue - $totalBilledAmount;

    //         return [
    //             'client'            => $cl,
    //             'project_count'     => $projectCount,
    //             'total_tender_value' => $billedValue,
    //             'total_billed'      => $totalBilledAmount,
    //             'pending_amount'    => $pendingAmount,
    //         ];
    //     });

    //     return view('web.client.list', compact('client', 'formattedClients'));
    // }

    public function client_list()
    {
        $userId = auth()->user()->id;

        // Get all clients under this MC
        $clients = MasterClientsSync::where('mc_id', $userId)->get();

        // Format each client with extra details
        $formattedClients = $clients->map(function ($cl) use ($userId) {

            // Get all tenders of this client
            $tenders = DB::table('master_tender')
                ->where('mc_id', $userId)
                ->where('authority', $cl->id)
                ->get();

            $projectCount = $tenders->count();
            $billedValue  = $tenders->sum('tender_value') ?? 0;

            // Get total billed amount for all tenders of this client
            $totalBilledAmount = DB::table('tender_bill')
                ->where('mc_id', $userId)
                ->whereIn('t_id', $tenders->pluck('id'))
                ->sum('total_amount') ?? 0;

            $pendingAmount = $billedValue - $totalBilledAmount;

            return [
                'client'            => $cl,
                'project_count'     => $projectCount,
                'total_tender_value' => $billedValue,
                'total_billed'      => $totalBilledAmount,
                'pending_amount'    => $pendingAmount,
            ];
        });

        return view('web.client.list', compact('formattedClients'));
    }


    public function verifyGstSync(Request $request)
    {

        $request->validate([
            'gst_no' => 'required|string'
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
                'updated_at' => now()
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
                    'pincode' => $addressParts['pincode']
                ]
            ]);
        }

        // GST not found locally - Fetch from IDFY API
        return $this->fetchFromApi($gst_no);
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
                'isdetails' => true
            ]
        ];

        $client = new Client();

        try {
            $response = $client->post('https://eve.idfy.com/v3/tasks/async/retrieve/gst_info', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'api-key' => '6f0425e9-63af-4f1f-b96e-1f01bff888f2',
                    'account-id' => 'd46db310a92c/7e109c77-5501-4330-ad0a-2f1274c23374'
                ],
                'json' => $payload
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $requestId = $res['request_id'] ?? null;

            if (!$requestId) {
                return response()->json(['success' => false, 'message' => 'Request ID not found'], 400);
            }

            sleep(7); // Wait for async response

            $pollResponse = $client->get('https://eve.idfy.com/v3/tasks', [
                'headers' => [
                    'Accept' => 'application/json',
                    'api-key' => '6f0425e9-63af-4f1f-b96e-1f01bff888f2',
                    'account-id' => 'd46db310a92c/7e109c77-5501-4330-ad0a-2f1274c23374'
                ],
                'query' => [
                    'request_id' => $requestId
                ],
            ]);

            $data = json_decode($pollResponse->getBody()->getContents(), true);
            $details = $data[0]['result']['details'] ?? null;

            if (!$details) {
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
                'updated_at' => now()
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
                'updated_at' => now()
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
                    'pincode' => $addressParts['pincode']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("GST verification failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'GST verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Step 2: Add Nick Name
    public function addNickName(Request $request)
    {
        $request->validate([
            'sync_id' => 'required',
            'nick_name' => 'required'
        ]);

        // get mc_id
        $user = auth()->user()->id;

        if (!$user) {
            Log::error('Client guard returned null user in addNickName');
            return back()->with('error', 'User not authenticated');
        }

        $mcId = $user;

        Log::info('MC ID extracted in addNickName:', ['mc_id' => $mcId]);

        $syncId = $request->sync_id;
        $nickName = $request->nick_name;

        // Check if sync record exists
        $syncRecord = DB::table('master_clients_sync')->where('id', $syncId)->first();


        // Update nick name
        DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->update([
                'nick_name' => $nickName,
                'updated_at' => now()
            ]);

        // Update sync table with mc_id (obtained from token)
        DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->update(['mc_id' => $mcId]);

        return to_route('client_list')->with([
            'status' => 'Success',
            'message' => 'Client added successfully'
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
                'Ladakh'
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
                    if ($state && stripos($part, $state) !== false) continue;
                    if ($pincode && strpos($part, $pincode) !== false) continue;
                    // Skip if it's too short or contains numbers at the start
                    if (strlen($part) < 3 || preg_match('/^\d/', $part)) continue;

                    // This is likely the city
                    if (!$city && strlen($part) > 2) {
                        $city = $part;
                    }
                }
            }
        }

        return [
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode
        ];
    }

    public function add_client()
    {
        return view('web.client.add_client');
    }


    public function addNonGstClient(Request $request)
    {
        $request->validate([
            'phone_number'   => 'required|string',
            'business_name'  => 'required|string',
            'gst_no'         => 'required|string',
            'pan_no'         => 'required|string',
            'promoter_name'  => 'nullable|string',
            'email'          => 'nullable|email',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string',
            'state'          => 'nullable|string',
            'pincode'        => 'nullable|string',
        ]);

        $data = $request->only([
            'phone_number',
            'business_name',
            'pan_no',
            'gst_no',
            'promoter_name',
            'email',
            'address',
            'city',
            'state',
            'pincode'
        ]);

        $mcDbId = DB::table('master_clients_db')->insertGetId([
            'business_name'             => $data['business_name'],
            'legal_name'                => $data['business_name'],
            'gst_no'                    => $data['gst_no'],
            'pan_number'                => $data['pan_no'],
            'promoters'                 => $data['promoter_name'] ?? null,
            'address'                   => $data['address'] ?? null,
            'email'                     => $data['email'] ?? null,
            'phone_number'              => $data['phone_number'],
            'constitution_of_business'  => null,
            'state_jurisdiction'        => $data['state'] ?? null,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        $syncId = DB::table('master_clients_sync')->insertGetId([
            'mc_db_id'         => $mcDbId,
            'gst_no'           => $data['gst_no'],
            'business_legalname' => $data['business_name'],
            'business_type'    => null,
            'register_date'    => null,
            'promotors_name'   => $data['promoter_name'] ?? null,
            'income_annual'    => null,
            'pan_no'           => $data['pan_no'],
            'phone_number'     => $data['phone_number'],
            'email'            => $data['email'] ?? null,
            'address'          => $data['address'] ?? null,
            'city'             => $data['city'] ?? null,
            'state'            => $data['state'] ?? null,
            'pincode'          => $data['pincode'] ?? null,
            'status'           => 'without',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Non-GST client added successfully',
            'sync_id' => $syncId
        ]);
    }


    public function update_nick_name(Request $request)
    {
        $request->validate([
            'sync_id' => 'required',
            'nick_name' => 'required'
        ]);

        // get mc_id
        $user = auth()->user()->id;

        $mcId = $user;

        $syncId = $request->sync_id;
        $nickName = $request->nick_name;

        // Check if sync record exists
        $syncRecord = DB::table('master_clients_sync')->where('id', $syncId)->first();

        // Update nick name
        DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->update([
                'nick_name' => $nickName,
                'updated_at' => now()
            ]);

        // Update sync table with mc_id (obtained from token)
        DB::table('master_clients_sync')
            ->where('id', $syncId)
            ->update(['mc_id' => $mcId]);

        return to_route('client_list')->with([
            'status' => 'Success',
            'message' => 'Non GST client added successfully'
        ]);
    }

    public function update_client(Request $request)
    {

        $request->validate([
            'business_name' => 'required|string',
            'promoter_name' => 'nullable|string',
            'pan_no' => 'nullable|string',
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
            // link to master_clients
            'business_legalname' => $request->business_name,
            'promotors_name' => $request->promoter_name,
            'pan_no' => $request->pan_no,
            'email' => $request->email,
            'nick_name' => $request->nick_name,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
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

        return redirect()->back()->with([
            'status' => 'Success',
            'message' => 'Client edited successfully'
        ]);
    }
}
