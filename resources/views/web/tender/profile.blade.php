@extends('web.layout.app')
@section('page_name', 'Tender Profile')

@push('style')
    <style>
        .scroll-hide::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari */
        }

        .scroll-hide {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .card {
            border-radius: 8px;
            /* box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); */
            background-color: #fff;
        }

        .form-check-inline {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            transform: scale(1.3);
            margin-right: 8px;
            vertical-align: middle;
        }

        .form-check-label {
            font-size: 1rem;
            line-height: 1.5;
            vertical-align: middle;
        }

        /* /scroll bar */
        .custom-scrollbar-x {
            overflow-x: auto;
            overflow-y: hidden;
        }

        /* Scrollbar visible */
        .custom-scrollbar-x::-webkit-scrollbar {
            height: 8px;
            /* scrollbar height */
        }

        .custom-scrollbar-x::-webkit-scrollbar-track {
            background: #e0e0e0;
            /* track color */
            border-radius: 4px;
        }

        .custom-scrollbar-x::-webkit-scrollbar-thumb {
            background: #888;
            /* scrollbar color */
            border-radius: 4px;
        }

        .custom-scrollbar-x::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Tender Profile - <span>{{ $tender_prof->tender_no }}</span></strong></h3>

                    <div id="action-buttons">
                        {{-- @if ($tender_bill->where('payment_type', 'Full Payment')->where('status', 'collected')->isNotEmpty()) --}}
                        @if (!$reminder->where('status', 'collected'))
                            <form action="{{ route('collect_notify') }}" method="post">
                                @csrf

                                <input type="hidden" name="tender_id" value={{ $tender_prof->id }}>
                                {{-- <input type="text" name="b_id" value={{ $tender_prof->id }}> --}}
                                <button class="btn btn-primary">
                                    <i class="fa-solid fa-plus fs-4 me-1" id="collectEbdBtn"></i> Collect EMD
                                </button>
                            </form> 
                            {{-- data-bs-toggle="modal" data-bs-target="#exampleModalToggleStatus" --}}
                        @endif
                        {{-- @endif --}}

                        @if (!$tender_bill->contains('payment_type', 'Full Payment'))
                            <button id="billingBtn" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalToggleBill">
                                <i class="fa-solid fa-plus fs-4 me-1"></i> Billing
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- report tabs --}}
                <div class="col-md-12 col-xl-12">
                    <div class="nav nav-tabs d-flex justify-content-end align-items-center gap-xl-3 mb-3 gap-x-3" role="tablist">
                        <a class="active" data-bs-toggle="tab" href="#Profile" role="tab" aria-selected="true">Profile</a>
                        <a class="" data-bs-toggle="tab" href="#Billing" role="tab" aria-selected="false" tabindex="-1">Billing</a>
                        <a class="" data-bs-toggle="tab" href="#Expense" role="tab" aria-selected="false" tabindex="-1">Expense</a>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="Profile" role="tabpanel">
                            @include('web.tender.profile-tab')
                        </div>

                        <div class="tab-pane fade" id="Billing" role="tabpanel">
                            @include('web.tender.billing-tab')
                        </div>

                        <div class="tab-pane fade" id="Expense" role="tabpanel">
                            @include('web.tender.expense-tab')
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Datatables Responsive
            $("#datatables-reponsive").DataTable({
                responsive: true,
                ordering: false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    ["10", "25", "50", "All"]
                ]
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Datatables Responsive
            $("#datatables-reponsive-2").DataTable({
                responsive: true,
                ordering: false,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    ["5", "10", "25", "50", "All"]
                ]
            });


            // 3) WHEN the “Permissions” tab is actually shown, force a recalc on Table #2:
            $('a[data-bs-toggle="tab"][href="#Permissions"]').on("shown.bs.tab", function(e) {
                // Explicitly adjust only the second DataTable:
                $("#datatables-reponsive-2")
                    .DataTable()
                    .columns.adjust()
                    .responsive.recalc();
            });
        });

        // button view
        document.addEventListener('DOMContentLoaded', function() {
            const collectBtn = document.getElementById('collectEbdBtn');
            const billingBtn = document.getElementById('billingBtn');

            // Initially, show only Collect EMD
            collectBtn.style.display = 'inline-block';
            billingBtn.style.display = 'none';

            // Get all tab links
            const tabLinks = document.querySelectorAll('.nav-tabs a[data-bs-toggle="tab"]');

            tabLinks.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('href'); // tab target ID

                    if (target === '#Profile') {
                        collectBtn.style.display = 'inline-block';
                        billingBtn.style.display = 'none';
                    } else if (target === '#Billing') {
                        collectBtn.style.display = 'none';
                        billingBtn.style.display = 'inline-block';
                    } else {
                        // hide both for other tabs
                        collectBtn.style.display = 'none';
                        billingBtn.style.display = 'none';
                    }
                });
            });
        });

        // expense chart
        document.addEventListener("DOMContentLoaded", function() {

            var expenseLabels = @json($exp_label);
            var expenseAmounts = @json($exp_total);

            // convert to numbers
            expenseAmounts = expenseAmounts.map(Number);

            var options = {
                series: expenseAmounts,
                chart: {
                    type: 'donut',
                    height: 350
                },
                labels: expenseLabels,
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return opts.w.globals.series[opts.seriesIndex];
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#expense_chart"), options);

            // Render only when tab becomes visible
            document.addEventListener("shown.bs.tab", function(e) {
                if (e.target.getAttribute("href") === "#Expense") {
                    chart.render();
                }
            });

        });
    </script>
    {{-- model data pass --}}
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     document.querySelectorAll('.openCollectModal').forEach(button => {
        //         button.addEventListener('click', function() {
        //             let id = this.getAttribute('data-id');
        //             let date = this.getAttribute('data-date');
        //             let amount = this.getAttribute('data-amount');

        //             document.getElementById('modal_bill_id').value = id;
        //             document.getElementById('modal_bill_date').value = date; // works now
        //             document.getElementById('modal_bill_amount').value = amount; // works now
        //         });
        //     });
        // });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.openRemninderModal').forEach(button => {
                button.addEventListener('click', function() {
                    let bill_id = this.getAttribute('data-bill-id');
                    let tender_id = this.getAttribute('data-tender-id');

                    console.log("Clicked!", bill_id, tender_id);

                    document.getElementById('modal_bill_id').value = bill_id;
                    document.getElementById('modal_tender_id').value = tender_id; // works now
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.openCollectModal').forEach(button => {
                button.addEventListener('click', function() {

                    let bill_id = this.getAttribute('data-id');
                    let date = this.getAttribute('data-date');
                    let amount = this.getAttribute('data-amount');

                    document.getElementById('modal_bill_ide').value = bill_id;
                    document.getElementById('modal_bill_date').value = date;
                    document.getElementById('modal_bill_amount').value = amount;

                    console.log("Clicked!", bill_id, date, amount);

                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function num(id) {
                return parseFloat(document.getElementById(id)?.value || 0);
            }

            function calculateTotals() {

                // === TOTAL DEDUCTION ===
                let deduction =
                    num("IT") +
                    num("CGST") +
                    num("SGST") +
                    num("LWF") +
                    num("WITHHELD") +
                    num("OTHERS");

                // show deduction
                document.getElementById("total_deduction").value = deduction;

                // === GRAND TOTAL (editable but recalculates when fields change) ===
                let grand = num("WORK_DONE") - deduction;

                // only auto-update if user didn’t manually edit after last calculation
                if (!grandTotalEdited) {
                    document.getElementById("grand_total").value = grand;
                }
            }

            let grandTotalEdited = false;

            // Detect user manual edit on grand total
            document.getElementById("grand_total").addEventListener("input", function() {
                grandTotalEdited = true;
            });

            // Recalculate on any input change
            [
                "WORK_DONE",
                "TAXABLE",
                "IT",
                "CGST",
                "SGST",
                "LWF",
                "WITHHELD",
                "OTHERS"
            ].forEach(function(id) {
                let el = document.getElementById(id);
                if (el) {
                    el.addEventListener("input", function() {
                        grandTotalEdited = false;
                        calculateTotals();
                    });
                }
            });

        });
    </script>
@endpush
