<?php

namespace App\Http\Controllers\web;

use App\Models\MasterTender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportControllerWeb
{
    public function report()
    {
        $mcId = auth()->id();

        $schemes = MasterTender::where('mc_id', $mcId)
            ->whereNotNull('scheme')
            ->where('scheme', '!=', '')
            ->pluck('scheme')
            ->unique();

        $authorities = MasterTender::where('mc_id', $mcId)
            ->whereNotNull('authority')
            ->where('authority', '!=', '')
            ->pluck('authority')
            ->unique();

        $statuses = MasterTender::where('mc_id', $mcId)
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->pluck('status')
            ->unique();

        $emdTypes = MasterTender::where('mc_id', $mcId)
            ->whereNotNull('emd_type')
            ->where('emd_type', '!=', '')
            ->pluck('emd_type')
            ->unique();

        return view('web.report.index', compact('schemes', 'authorities', 'statuses', 'emdTypes'));
    }

    // public function report_pdf()
    // {
    //     return view('web.report.report_pdf');
    // }

    public function downloadReport(Request $request)
    {
        $mcId = auth()->id();

        if (! $mcId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ]);
        }

        $reportType = $request->report_type;

        // Base query
        $query = DB::table('master_tender')
            ->leftJoin('master_clients_sync', 'master_tender.authority', '=', 'master_clients_sync.id')
            ->where('master_tender.mc_id', $mcId)
            ->select(
                'master_tender.*',
                'master_clients_sync.business_legalname as authority_name'
            );

        // Apply filters based on page
        if ($reportType === 'tender') {

            if ($request->tender_scheme !== 'all' && $request->filled('tender_scheme')) {
                $query->where('scheme', $request->tender_scheme);
            }

            if ($request->tender_authority !== 'all' && $request->filled('tender_authority')) {
                $query->where('authority', $request->tender_authority);
            }

            if ($request->tender_status !== 'all' && $request->filled('tender_status')) {
                $query->where('status', $request->tender_status);
            }
        }


        if ($reportType === 'emd_payment') {

            if ($request->emd_payment_scheme !== 'all' && $request->filled('emd_payment_scheme')) {
                $query->where('scheme', $request->emd_payment_scheme);
            }

            if ($request->emd_payment_mode !== 'all' && $request->filled('emd_payment_mode')) {
                $query->where('emd_type', $request->emd_payment_mode);
            }

            if ($request->filled('emd_payment_date')) {
                $query->whereDate('master_tender.created_at', $request->emd_payment_date);
            }
        }


        if ($reportType === 'emd_refund') {

            if ($request->emd_refund_scheme !== 'all' && $request->filled('emd_refund_scheme')) {
                $query->where('scheme', $request->emd_refund_scheme);
            }

            if ($request->emd_refund_authority !== 'all' && $request->filled('emd_refund_authority')) {
                $query->where('authority', $request->emd_refund_authority);
            }

            $query->where('tender_status', 'collected');
        }


        $tenders = $query->get();

        if ($tenders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No records found for selected filters.'
            ]);
        }

        $tenderIds = $tenders->pluck('id')->toArray();

        // BILL details
        $bills = DB::table('tender_bill')
            ->where('mc_id', $mcId)
            ->whereIn('t_id', $tenderIds)
            ->get()
            ->groupBy('t_id');

        $tenders = $tenders->map(function ($t) use ($bills) {
            $t->bills = $bills->has($t->id)
                ? $bills[$t->id]->toArray()
                : [];

            return $t;
        });

        // Load user
        $client = DB::table('master_clients_sync')->where('mc_id', $mcId)->first();

        // Load PDF view
        $pdf = \PDF::loadView('web.report.report_pdf', [
            'tenders' => $tenders,
            'client' => $client,
            'generatedDate' => now()->format('d M Y'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Tender_Report_' . date('Ymd_His') . '.pdf');
    }

    public function generateInvoiceWeb($billId)
    {
        $mcId = auth()->id();

        if (! $mcId) {
            return redirect()->back()->with('error', 'Session expired, please login again.');
        }

        // Fetch bill with tender details
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
            return redirect()->back()->with('error', 'Bill not found.');
        }

        // Client (Contractor) details
        $client = DB::table('master_clients')
            ->where('id', $mcId)
            ->first();

        if (! $client) {
            return redirect()->back()->with('error', 'Client information missing.');
        }

        // Collections
        $collections = DB::table('collectbillamount')
            ->where('bill_id', $billId)
            ->where('mc_id', $mcId)
            ->orderBy('bill_date', 'desc')
            ->get();


            
        $totalCollected = $collections->sum('amount');
        $pendingAmount = $bill->total_amount - $totalCollected;

        // Pass all variables the Blade template expects
        $data = [
            'invoice_number' => 'INV-' . $bill->id . '-' . date('Ymd'),
            'invoice_date' => now()->format('Y-m-d'),
            'bill' => $bill,
            'client' => $client,
            'collections' => $collections,
            'total_collected' => $totalCollected,
            'pending_amount' => $pendingAmount,
        ];

        // RETURN PDF
        $pdf = \Pdf::loadView('web.report.invoice_pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download('Invoice_' . $data['invoice_number'] . '.pdf');
    }
}
