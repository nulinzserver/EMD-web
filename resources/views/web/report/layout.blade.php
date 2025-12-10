<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        tr:nth-child(even) td {
            border-color: #444;
        }

        tr:nth-child(odd) td {
            border-color: #888;
        }

        tr.red-border td {
            border-color: #e74c3c;
        }

        tr.blue-border td {
            border-color: #3498db;
        }

        .no-border {
            border: none !important;
        }

        .totals td {
            border: 1px solid #000;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }

        .addr-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .addr-list li {
            padding: 2px 0;
        }
    </style>
</head>

<body>

<h2 style="text-align:center;">Tax Invoice</h2>

{{-- contractor details --}}
<p class="mb-1"><strong>Contractor:</strong> {{ $client->business_legalname ?? 'N/A' }}</p>
<p><strong>GSTIN:</strong> {{ $client->gst_number ?? 'N/A' }}</p>
<p><strong>Phone:</strong> {{ $client->phone_number ?? 'N/A' }}</p>
<p><strong>Email:</strong> {{ $client->email ?? 'N/A' }}</p>

{{-- invoice meta --}}
<p><strong>Invoice No:</strong> {{ $invoice_number ?? 'N/A' }}</p>
<p><strong>Date:</strong> {{ $invoice_date ?? now()->format('Y-m-d') }}</p>

{{-- Bill details --}}
<table>
    <tr>
        <th>Work Done</th>
        <td>{{ number_format($bill->work_done_amount ?? 0, 2) }}</td>
    </tr>
    <tr>
        <th>Taxable Amount</th>
        <td>{{ number_format($bill->taxable_amount ?? 0, 2) }}</td>
    </tr>
    <tr>
        <th>Total Amount</th>
        <td><strong>{{ number_format($bill->total_amount ?? 0, 2) }}</strong></td>
    </tr>
</table>

{{-- Tender Info --}}
<h3>Tender Details</h3>
<p><strong>Tender No:</strong> {{ $bill->tender_no ?? 'N/A' }}</p>
<p><strong>Project:</strong> {{ $bill->project_name ?? 'N/A' }}</p>
<p><strong>Scheme:</strong> {{ $bill->scheme ?? 'N/A' }}</p>

{{-- Collection Summary --}}
<h3>Collections</h3>
<table>
    <tr>
        <th>Date</th>
        <th>Amount</th>
    </tr>

    @forelse($collections as $col)
        <tr>
            <td>{{ $col->bill_date ?? '-' }}</td>
            <td>{{ number_format($col->amount ?? 0, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="2" style="text-align:center;">No collections found</td>
        </tr>
    @endforelse

</table>

{{-- The rest of your HTML layout below remains **exactly the same** with no changes --}}
<main class="p-2">
    <article style="border:1px solid #000; padding:6px; border-bottom: 0;">
        <h2 class="text-decoration-underline mb-2" style="text-align: center">Tax Invoice</h2>
        <h4 style="margin-bottom: 0">Contractor Address:</h4>
        <p style="width: 50% margin-bottom: 0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloremque voluptates soluta voluptatem ipsam deleniti ipsa!</p>
        <ul class="addr-list">
            <li><strong>GSTIN:</strong> 46646SDFDGESDF</li>
            <li><strong>CellNo:</strong> 987854552</li>
            <li><strong>MailID:</strong> sfd@mail.com</li>
        </ul>
    </article>

    <table>
        <tr>
            <td style="width:33.3%;">
                <p><strong>Invoice No:</strong> {{ $invoice_number ?? 'N/A' }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice_date ?? now()->format('Y-m-d') }}</p>
                <p><strong>Terms:</strong> Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
            </td>
            <td style="width:33.3%;">
                <p><strong>Date of Receipt:</strong> {{ $invoice_date ?? now()->format('Y-m-d') }}</p>
            </td>
            <td style="width:33.3%;">
                <p><strong>Place of Supply:</strong> {{ $bill->location ?? 'N/A' }}</p>
            </td>
        </tr>
    </table>

    <table>
        <tr style="background-color: rgb(49, 49, 245); color:#fff;">
            <td style="width:50%;"><strong>BillTo</strong></td>
            <td style="width:50%;"><strong>ShipTo</strong></td>
        </tr>
        <tr>
            <td>
                <p><strong>The Project Director, Salem DRDA, Salem -623503.</strong></p>
                <p><strong>GSTIN:</strong> 33CHED04618F1D4</p>
            </td>
            <td>
                <p><strong>GSTIN:</strong> 33CHED04618F1D4</p>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr style="background-color: rgb(49, 49, 245); color:#fff;">
                <th style="width:5%;">S.No.</th>
                <th style="width:34%;">Item & Description</th>
                <th style="width:10%;">HSN/SAC</th>
                <th style="width:6%;">Qty</th>
                <th style="width:11%;">Taxable Value</th>
                <th colspan="2" style="width:10%;">CGST</th>
                <th colspan="2" style="width:10%;">SGST</th>
                <th style="width:10%;">Amount</th>
            </tr>
            <tr style="background-color: rgb(49, 49, 245); color:#fff;">
                <th colspan="5"></th>
                <th>%</th>
                <th>Amt</th>
                <th>%</th>
                <th>Amt</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr class="red-border">
                <td>1</td>
                <td>This Bettel</td>
                <td>{{ $bill->hsn_code ?? '5455' }}</td>
                <td>22</td>
                <td>{{ number_format($bill->taxable_amount ?? 0, 2) }}</td>
                <td>8%</td>
                <td>25555</td>
                <td>8%</td>
                <td>55555</td>
                <td>{{ number_format($bill->total_amount ?? 0, 2) }}</td>
            </tr>
            <tr class="blue-border">
                <td>2</td>
                <td>Another Item</td>
                <td>7890</td>
                <td>10</td>
                <td>15000</td>
                <td>9%</td>
                <td>1350</td>
                <td>9%</td>
                <td>1350</td>
                <td>17700</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td style="width:70%;">
                    <strong>Total in Words:</strong> ₹ {{ number_format($bill->total_amount ?? 0, 2) }} Only<br><br>
                    <strong>Notes</strong><br>Payment due within 7 days.<br><br>
                    <strong>Balance Due:</strong> ₹ {{ $pending_amount ?? 0 }}<br><br>
                    <em>Thanks for your Business</em>
                </td>
                <td style="width:30%;">
                    <table>
                        <tr>
                            <td>Sub Total:</td>
                            <td style="text-align:right;">₹ {{ number_format($bill->taxable_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>CGST:</td>
                            <td style="text-align:right;">₹ 0</td>
                        </tr>
                        <tr>
                            <td>SGST:</td>
                            <td style="text-align:right;">₹ 0</td>
                        </tr>
                        <tr>
                            <td>Rounded Off:</td>
                            <td style="text-align:right;">₹ 0</td>
                        </tr>
                        <tr style="background-color: rgb(49, 49, 245); color:#fff;">
                            <td><strong>Total Value:</strong></td>
                            <td style="text-align:right;"><strong>₹ {{ number_format($bill->total_amount ?? 0, 2) }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="signature">
        Authorized Signature
    </div>
</main>
</body>

</html>
