<?php

namespace App\Http\Controllers\web;

use App\Models\CollectBillAmount;
use App\Models\EmdReminder;
use App\Models\MasterClient;
use App\Models\MasterClientsSync;
use App\Models\MasterExpense;
use App\Models\MasterTender;
use App\Models\TenderBill;
use App\Models\TenderCollection;
use App\Models\TenderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;

class TenderControllerWeb
{
    public function tender_list()
    {
        $user = Auth::guard('client')->user();

        $tender = $user->tenders;

        return view('web.tender.list', compact('tender'));
    }

    public function tender_profile(Request $request)
    {
        $tender_prof = MasterTender::with('tender_profile')->where('id', $request->id)->first();

        // tender bill
        $tender_bill = $tender_prof->tender_bill;

        $tender = DB::table('master_tender as t')
            ->leftJoin('master_clients_sync as c', 't.authority', '=', 'c.id')
            ->where('t.id', $request->id)
            ->where('t.mc_id', auth()->id())
            ->select(
                't.*',
                DB::raw('c.nick_name as authority')
            )
            ->first();

        $billSummary = DB::table('tender_bill')
            ->where('t_id', $request->id)
            ->where('mc_id', auth()->id())
            ->select(
                DB::raw('SUM(work_done_amount) as total_work_done'),
                DB::raw('SUM(taxable_amount) as total_taxable'),
                DB::raw('SUM(withheld_amount+cgst_amount+sgst_amount+it_amount+lwf_amount+others_amount) as total_deduction'),
                DB::raw('(SUM(cgst_amount) + SUM(sgst_amount)) as total_gst'),
                DB::raw('SUM(lwf_amount) as total_lwf'),
                DB::raw('SUM(others_amount) as total_others')
            )
            ->first();

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

        $collect_bill = CollectBillAmount::where('mc_id', auth()->id())->whereIn('bill_id', $tender_bill->pluck('id'))->get();

        // tender status
        $tender_stat = $tender_prof->te_status;

        $expense = $tender_prof->tender_exp;

        $exp_values = MasterExpense::where('t_id', $request->id)
            ->select('expense_category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('expense_category')
            ->pluck('total_amount', 'expense_category');


        // $totalDeductions = DB::table('tender_bill')
        //     ->where('t_id', $tenderId)
        //     ->where('mc_id', $mcId)
        //     ->sum('withheld_amount');

        $exp_label = $exp_values->keys();
        $exp_total = $exp_values->values();

        $reminder = EmdReminder::where('t_id', $tender_prof->id)->select('id', 'status')->get();

        return view('web.tender.profile', compact('tender_prof', 'tender_bill', 'tender_stat', 'exp_values', 'exp_label', 'exp_total', 'collect_bill', 'tenderDetails', 'tender', 'reminder'));
    }

    public function add_tender()
    {
        // clients
        $user = Auth::guard('client')->user();

        $nick_name = MasterClientsSync::where('mc_id', $user->id)
            ->select('id', 'nick_name')
            ->distinct()
            ->get();

        $scheme = MasterTender::distinct()->pluck('scheme');

        return view('web.tender.add-tender1', compact('nick_name', 'scheme'));
    }

    public function edit_tender($id)
    {
        $user = Auth::guard('client')->user();

        $tender_details = MasterTender::where('id', $id)->first();

        $schemes = MasterTender::select('scheme')->distinct()->get();


        $nick_name = MasterClientsSync::where('mc_id', $user->id)
            ->select('id', 'nick_name')
            ->distinct()
            ->get();

        return view('web.tender.edit-tender', compact('tender_details', 'schemes', 'nick_name'));
    }

    public function post_tender_one(Request $request)
    {
        $request->validate([
            'mc_id' => 'required',
            'tender_id' => 'required',
            'project_name' => 'required',
            'client' => 'required',
            'year_range' => 'required',
            'reminder_date' => 'required|date'
        ]);

        $tender_add = DB::table('master_tender')->insertGetId([
            'mc_id' => $request->mc_id,
            'tender_no'  => $request->tender_id,
            'project_name'  => $request->project_name,
            'contractor' => $request->contract_type,
            'authority'  => $request->client,
            'scheme'  => $request->scheme,
            'year_end_date'  => $request->year_range,
            'location'  => $request->location,
            'status'  => $request->status,
            'remainder_date'  => $request->reminder_date
        ]);

        $id = $tender_add;

        return to_route('add_tender_two', ['id' => $id]);
    }

    public function update_tender(Request $request)
    {

        // $request->validate([
        //     'id'           => 'required|integer',
        //     'tender_no'    => 'required|string',
        //     'project_name' => 'required|string',
        //     'client'       => 'required|string',
        //     'year_range'   => 'required|string',
        //     'reminder_date' => 'required|date',
        //     'contract_type' => 'required|string',
        //     'scheme'        => 'required|string',
        //     'location'      => 'required|string',
        //     'status'        => 'required|string',
        // ]);


        DB::table('master_tender')
            ->where('id', $request->id)
            ->update([
                'tender_no'      => $request->tender_no,
                'project_name'   => $request->project_name,
                'contractor'     => $request->contract_type,
                'authority'      => $request->client,
                'scheme'         => $request->scheme,
                'year_end_date'  => $request->year_range,
                'location'       => $request->location,
                'status'         => $request->status,
                'remainder_date' => $request->reminder_date,
                'updated_at'     => now(),
            ]);


        return redirect()->route('edit_tender_two', ['id' => $request->id]);
    }

    public function add_tender_two($id)
    {
        return view('web.tender.add-tender2',  compact('id'));
    }

    public function post_tender_two(Request $request)
    {
        $id = $request->tender_ins_id;

        // Base fields (common)
        $data = [
            'as_no'         => $request->ans_no,
            'as_date'       => $request->ans_date,
            'ts_no'         => $request->ts_no,
            'ts_date'       => $request->ts_date,
            'tender_value'  => $request->ts_value,
            'bid_value'     => $request->bid_value,
            'emd_value'     => $request->emd_value,
            'gst_applicable' => $request->gst,
            'hsn_code'      => $request->hsn,
            'year_end_date' => $request->end_date,
            'emd_type'      => $request->emd_type,
            'emd_date'      => $request->emd_date,
        ];

        // Reset all variable EMD Fields to NULL
        $emdReset = [
            'reference_id'      => null,
            'bank_name'         => null,
            'account_no'        => null,
            'fd_maturity_date'  => null,
            'bg_issue_date'     => null,
            'bg_expire_date'    => null,
            'dd_no'             => null,
            'dd_date'           => null,
            'challan_no'        => null,
            'challan_date'      => null,
        ];

        $data = array_merge($data, $emdReset);

        // Apply EMD-type specific logic
        switch ($request->emd_type) {

            case 'Online Payment':
                $data['reference_id'] = $request->online_ref_id;
                $data['bank_name']    = $request->online_bank_name;
                $data['account_no']   = $request->online_account_name;
                break;

            case 'Fixed Deposit':
                $data['reference_id']      = $request->fd_ref_id;
                $data['bank_name']         = $request->fd_bank_name;
                $data['account_no']        = $request->fd_acc_name;
                $data['fd_maturity_date']  = $request->fd_maturity;
                break;

            case 'Bank Guarantee':
                $data['reference_id']      = $request->bank_ref_id;
                $data['bank_name']         = $request->bank_name;
                $data['account_no']        = $request->bank_acc_number;
                $data['bg_issue_date']     = $request->bank_issue_date;
                $data['bg_expire_date']    = $request->bank_expire;
                break;

            case 'Damand Draft':
                $data['reference_id']  = $request->dd_ref_id;
                $data['bank_name']     = $request->dd_bank_name;
                $data['account_no']    = $request->dd_account;
                $data['dd_date']       = $request->dd_date;
                break;

            case 'Others':
                $data['reference_id']  = $request->other_ref_id;
                $data['bank_name']     = $request->other_bank;
                $data['account_no']    = $request->other_acc;
                break;

            case 'Cash':
                $data['challan_no']    = $request->cash_challan;
                $data['challan_date']  = $request->cash_challan_date;
                break;
        }

        // Update DB
        DB::table('master_tender')->where('id', $id)->update($data);

        return redirect()->route('add_tender_three', $id);
    }

    public function edit_tender_two($id)
    {
        $tender_details = MasterTender::where('id', $id)->first();

        return view('web.tender.edit-tender2',  compact('tender_details'));
    }

    public function update_tender_two(Request $request)
    { {
            $id = $request->tender_ins_id;

            // Common fields
            $data = [
                'as_no'         => $request->ans_no,
                'as_date'       => $request->ans_date,
                'ts_no'         => $request->ts_no,
                'ts_date'       => $request->ts_date,
                'tender_value'  => $request->ts_value,
                'bid_value'     => $request->bid_value,
                'emd_value'     => $request->emd_value,
                'gst_applicable' => $request->gst,
                'hsn_code'      => $request->hsn,
                'emd_type'      => $request->emd_type,
                'emd_date'      => $request->emd_date,
            ];

            // Reset dynamic fields
            $reset = [
                'reference_id'     => null,
                'bank_name'        => null,
                'account_no'       => null,
                'fd_maturity_date' => null,
                'bg_issue_date'    => null,
                'bg_expire_date'   => null,
                'dd_no'            => null,
                'dd_date'          => null,
                'challan_no'       => null,
                'challan_date'     => null,
            ];

            $data = array_merge($data, $reset);

            // Apply EMD Based Updates
            switch ($request->emd_type) {

                case 'Online Payment':
                    $data['reference_id'] = $request->online_ref_id;
                    $data['bank_name']    = $request->online_bank_name;
                    $data['account_no']   = $request->online_account_name;
                    break;

                case 'Fixed Deposit':
                    $data['reference_id']      = $request->fd_ref_id;
                    $data['bank_name']         = $request->fd_bank_name;
                    $data['account_no']        = $request->fd_acc_name;
                    $data['fd_maturity_date']  = $request->fd_maturity;
                    break;

                case 'Bank Guarantee':
                    $data['reference_id']   = $request->bank_ref_id;
                    $data['bank_name']      = $request->bank_name;
                    $data['account_no']     = $request->bank_acc_number;
                    $data['bg_issue_date']  = $request->bank_issue_date;
                    $data['bg_expire_date'] = $request->bank_expire;
                    break;

                case 'Damand Draft':
                    $data['dd_no']     = $request->dd_ref_id;
                    $data['bank_name'] = $request->dd_bank_name;
                    $data['account_no'] = $request->dd_account;
                    $data['dd_date']   = $request->dd_date;
                    break;

                case 'Others':
                    $data['reference_id'] = $request->ref_id;
                    $data['bank_name']    = $request->other_bank;
                    $data['account_no']   = $request->other_acc;
                    break;

                case 'Cash':
                    $data['challan_no']   = $request->cash_challan;
                    $data['challan_date'] = $request->cash_challan_date;
                    break;
            }

            // Update DB
            DB::table('master_tender')->where('id', $id)->update($data);

            return redirect()->route('edit_tender_three', ['id' => $id]);
        }
    }

    public function add_tender_three($id)
    {
        return view('web.tender.add-tender3', compact('id'));
    }

    public function post_tender_three(Request $request)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $id = $request->tender_id;

        $mcId = MasterTender::where('id', $request->tender_id)->value('mc_id');

        // FILE KEYS
        $fileKeys = [
            'as_copy',
            'ts_copy',
            'work_order',
            'contract',
            'emd_scan',
            'other'
        ];

        $paths = [];

        foreach ($fileKeys as $key) {

            if ($request->hasFile($key)) {

                $file = $request->file($key);

                $directory = public_path("tender_documents/{$key}");
                // Custom name
                $filename = "{$key}_{$mcId}_" . time() . '.' . $file->getClientOriginalExtension();
                // Move to custom location
                $file->move($directory, $filename);
                // Store DB path
                $paths[$key] = "tender_documents/{$key}/" . $filename;
            }
        }

        // UPDATE QUERY
        DB::table('master_tender')
            ->where('id', $id)
            ->update([
                'as_copy'             => $paths['as_copy'] ?? null,
                'estimation_copy'     => $paths['ts_copy'] ?? null,
                'tendors_notes'       => $paths['work_order'] ?? null,
                'bg_emd_scans'        => $paths['emd_scan'] ?? null,
                'contract_agreements' => $paths['contract'] ?? null,
                'others'              => $paths['other'] ?? null,
                'notes'               => $request->notes,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

        return redirect()->route('tender_list')
            ->with([
                'status' => 'Success',
                'message' => 'Tender added successfully'
            ]);
    }

    public function edit_tender_three($id)
    {
        $tender_details = MasterTender::where('id', $id)->first();

        return view('web.tender.edit-tender3', compact('tender_details'));
    }

    public function update_tender_three(Request $request)
    {
        $request->validate([
            'tender_id' => 'required|integer',
            'notes' => 'required|string',
        ]);

        $id = $request->tender_id;

        // Fetch existing tender
        $tender = MasterTender::findOrFail($id);

        // FILE KEYS
        $fileKeys = [
            'as_copy',
            'ts_copy',
            'work_order',
            'contract',
            'emd_scan',
            'other'
        ];

        $updateData = [
            'notes' => $request->notes,
            'updated_at' => now(),
        ];

        foreach ($fileKeys as $key) {

            if ($request->hasFile($key)) {

                $file = $request->file($key);
                $directory = public_path("tender_documents/{$key}");
                $filename = "{$key}_{$tender->mc_id}_" . time() . '.' . $file->getClientOriginalExtension();

                // Move to custom path
                $file->move($directory, $filename);

                // Store in DB path
                $updateData[$this->mapField($key)] = "tender_documents/{$key}/{$filename}";
            }
        }

        // UPDATE QUERY
        DB::table('master_tender')
            ->where('id', $id)
            ->update($updateData);

        return redirect()->route('tender_list')
            ->with([
                'status' => 'Success',
                'message' => 'Tender Updated Successfully'
            ]);
    }

    /**
     * Map request input key to DB field name
     */
    private function mapField($key)
    {
        return [
            'as_copy'   => 'as_copy',
            'ts_copy'   => 'estimation_copy',
            'work_order' => 'tendors_notes',
            'contract'  => 'contract_agreements',
            'emd_scan'  => 'bg_emd_scans',
            'other'     => 'others',
        ][$key];
    }


    public function add_exp(Request $request)
    {
        $request->validate([
            'mc_id' => 'required',
            'tender_id' => 'required',
            'exp_cat' => 'required',
            'amount' => 'required',
        ]);

        $exp = MasterExpense::create([
            'mc_id' => $request->mc_id,
            't_id' => $request->tender_id,
            'expense_category' => $request->exp_cat,
            'amount' => $request->amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // if ($exp) {
        //     return back()->with(
        //         'success',
        //         'Expense added successfully'
        //     );
        if ($exp) {
            return back()->with([
                'status' => 'Success',
                'message' => 'Expense added successfully'
            ]);
        }
    }

    public function add_billing(Request $request)
    {
        //     $request->validate([
        //         'tender_id' => 'required',
        //         'mc_id' => 'required',
        //         'paymentType' => 'required',
        //         'work_done' => 'required',
        //         'taxable_amount' => 'required',
        //         'IT' => 'required',
        //         'csgt' => 'required',
        //         'sgst' => 'required',
        //         'lwf' => 'required',
        //         'others' => 'required',
        //         'withheld' => 'required',
        //         'collection_proof' => 'required|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        //         'remarks' => 'required',
        //         'grand_total' => 'required'
        //     ]);

        // FILE UPLOAD
        $collectionProofPath = null;
        if ($request->hasFile('collection_proof')) {
            $file = $request->file('collection_proof');

            $filename = 'collection_proof_' . $request->mc_id . '_' . $request->tender_id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('tender_bills/collection_proofs'), $filename);

            $collectionProofPath = 'tender_bills/collection_proofs/' . $filename;
        }

        $bill = Db::table('tender_bill')->insert([
            'mc_id' => $request->mc_id,
            't_id' => $request->tender_id, // OR 'tender_id' if your table uses that name
            'payment_type' => $request->paymentType,
            'work_done_amount' => $request->work_done,
            'taxable_amount' => $request->taxable_amount,
            'it_amount' => $request->IT,
            'cgst_amount' => $request->csgt,
            'sgst_amount' => $request->sgst,
            'lwf_amount' => $request->lwf,
            'others_amount' => $request->others,
            'withheld_amount' => $request->withheld,
            'collection_proof' => $collectionProofPath,
            'remarks' => $request->remarks,
            'total_amount' => $request->grand_total,
        ]);

        if ($bill) {
            return back()->with([
                'status' => 'Success',
                'message' => 'Bill added successfully'
            ]);
        }
    }

    public function collect_amount(Request $request)
    {

        $request->validate([
            'bill_id' => 'required',
            'mc_id' => 'required',
            'tender_id' => 'required',
            'date' => 'required',
            'amount' => 'required',
            'attachment' => 'required',
            'remark' => 'required',
        ]);

        $attachmentPath = null;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'bill_attachment_' . $request->mc_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('collect_bill_attachments'), $filename);
            $attachmentPath = 'collect_bill_attachments/' . $filename;
        }

        $bill = CollectBillAmount::insert([
            't_id' => $request->tender_id,
            'mc_id'  => $request->mc_id,
            'bill_id' => $request->bill_id,
            'bill_date'  => $request->date,
            'amount' => $request->amount,
            'attachment' => $attachmentPath,
            'remark'  => $request->remark,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        $bill_status = TenderBill::where('id', $request->bill_id)
            ->update(['status' => 'collected', 'updated_at' => now()]);

        if ($bill_status) {
            return back()->with([
                'status' => 'Success',
                'message' => 'Collection added successfully'
            ]);
        }
    }

    public function tender_status(Request $request)
    {
        $request->validate([
            'tender_id' => 'required',
            'status' => 'required',
            'date' => 'required'
        ]);

        MasterTender::where('id', $request->tender_id)->update([
            'status' => $request->status,
            'updated_at' => $request->date
        ]);

        $stauts_insert = TenderStatus::insert([
            't_id' => $request->tender_id,
            'status' => $request->status,
            'status_date' => $request->date,
            'created_at' => now()
        ]);

        if ($stauts_insert) {
            return back()->with([
                'status' => 'Success',
                'message' => 'Stauts updates successfully'
            ]);
        }
    }
    public function createEmdReminderWeb(Request $request)
    {

        $request->validate([
            't_id' => 'required',
            'b_id' => 'required',
            'remainder_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Make sure tender belongs to logged in user
        $mcId = auth()->id();

        $tender = DB::table('master_tender')
            ->where('id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $tender) {
            return back()->with('error', 'Tender not found or not authorized.');
        }

        $bill = DB::table('tender_bill')
            ->where('id', $request->b_id)
            ->where('t_id', $request->t_id)
            ->where('mc_id', $mcId)
            ->first();

        if (! $bill) {
            return back()->with('error', 'Bill not found or not authorized.');
        }

        $rem_gen_date = Carbon::parse($request->remainder_date)->addDays(30);

        DB::table('emd_remainder')->insert([
            'mc_id' => $mcId,
            't_id' => $request->t_id,
            'b_id' => $request->b_id,
            'remainder_date' => $request->remainder_date,
            'rem_gen_date' => $rem_gen_date,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with([
            'status' => 'success',
            'message' => 'EMD Reminder created successfully!'
        ]);
    }

    public function collect_notify(Request $request)
    {

        $tender = DB::table('master_tender')->where('id', $request->tender_id)->first();

        $user = DB::table('master_clients')->where('id', $tender->mc_id)->first();

        $data = [
            'title' => 'Deposit Amount Collected',
            'body' => 'Deposit amount has been collected for Tender No: ' . $tender->tender_no,
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
            'message' => 'Deposit amount has been collected for Tender No: ' . $tender->tender_no,
            'type' => 'emd_collection',
            'related_id' => $tender->id,
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        // if (! empty($user->fcm_token)) {
        //     $fcm = new Fcm;
        //     $fcm->send_notify($user->fcm_token, $data);
        // }

        return redirect()->back()->with([
            'status' => 'Success',
            'message' => 'Notification sent successfully',
        ]);
    }
}
