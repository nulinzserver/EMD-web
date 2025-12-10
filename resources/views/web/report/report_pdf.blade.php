<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Tender Report</title>

    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif !important;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            padding: 20px 30px;
        }

        .header {
            background: #0d6efd;
            padding: 20px;
            color: #fff;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .header-info {
            text-align: right;
            font-size: 14px;
        }

        .section-title {
            font-size: 18px;
            margin-top: 25px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        table th {
            background: #0d6efd;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }

        .info-grid {
            width: 100%;
            margin-top: 10px;
        }

        .info-grid td {
            padding: 4px 0;
        }

        .small {
            color: #666;
            font-size: 13px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container">

        @foreach ($tenders as $tender)
            <!-- HEADER -->
            <div class="header">
                <h2>Tender Report</h2>

                <div class="header-info">
                    <b>Tender No: {{ $tender->tender_no ?? 'N/A' }}</b><br>
                    Generated: {{ $generatedDate }}
                </div>
            </div>

            <!-- TENDER INFORMATION -->
            <div class="section-title">TENDER INFORMATION</div>

            <table class="info-grid">
                <tr>
                    <td><b>Project Name:</b></td>
                    <td>{{ $tender->project_name }}</td>
                </tr>
                <tr>
                    <td><b>Authority:</b></td>
                    <td>{{ $tender->authority_name }}</td>
                </tr>
                <tr>
                    <td><b>Scheme:</b></td>
                    <td>{{ $tender->scheme }}</td>
                </tr>
                <tr>
                    <td><b>Status:</b></td>
                    <td>{{ $tender->status }}</td>
                </tr>
                <tr>
                    <td><b>Tender Value:</b></td>
                    <td>₹{{ number_format($tender->tender_value) }}</td>
                </tr>
                <tr>
                    <td><b>Bid Value:</b></td>
                    <td>₹{{ number_format($tender->bid_value) }}</td>
                </tr>
                <tr>
                    <td><b>EMD Value:</b></td>
                    <td>₹{{ number_format($tender->emd_value ?? 0) }}</td>
                </tr>
            </table>

            <!-- BILL DETAILS -->
            <div class="section-title">BILL DETAILS</div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>TYPE</th>
                        <th>DATE</th>
                        <th>WORK DONE</th>
                        <th>TAXABLE</th>
                        <th>WITHHELD</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($tender->bills as $index => $bill)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $bill->payment_type }}</td>
                            <td>{{ $bill->created_at }}</td>
                            <td>₹{{ number_format($bill->work_done_amount) }}</td>
                            <td>₹{{ number_format($bill->taxable_amount) }}</td>
                            <td>₹{{ number_format($bill->withheld_amount) }}</td>
                            <td>₹{{ number_format($bill->total_amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="page-break-after: always;"></div>
        @endforeach

    </div>

</body>

</html>
