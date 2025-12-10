@extends('web.layout.app')
@section('page_name', 'Report')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Report</strong></h3>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ route('downloadReport') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Report Type</label>
                                        <select id="report_type" name="report_type" class="form-select">
                                            <option value="" selected disabled>Select</option>
                                            <option value="tender">Tender Status Report</option>
                                            <option value="emd_payment">EMD Payment Report</option>
                                            <option value="emd_refund">EMD Refund Tracking</option>
                                        </select>
                                    </div>

                                    <!-- Tender Status Report Dependent Inputs -->
                                    <div class="col-md-4">
                                        <label class="form-label">Category</label>
                                        <select id="category" name="category" class="form-select">
                                            <option value="" selected disabled>Select</option>
                                            {{-- Options will be filled dynamically --}}
                                        </select>
                                    </div>

                                    <!-- Scheme -->
                                    <div class="col-md-4 d-none" id="tender_scheme_div">
                                        <label class="form-label">Scheme</label>
                                        <select class="form-select" name="tender_scheme">
                                            <option value="" selected disabled>Select Scheme</option>
                                            @foreach ($schemes as $sch)
                                                <option value="{{ $sch }}">{{ $sch }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Authority / Client -->
                                    <div class="col-md-4 d-none" id="tender_authority_div">
                                        <label class="form-label">Authority/Client</label>
                                        <select class="form-select" name="tender_authority">
                                            <option value="" selected disabled>Select Authority/Client</option>
                                            @foreach ($authorities as $at)
                                                <option value="{{ $at }}">{{ $at }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-4 d-none" id="tender_status_div">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="tender_status">
                                            <option value="" selected disabled>Select Status</option>
                                            @foreach ($statuses as $st)
                                                <option value="{{ $st }}">{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- EMD Payment Dependent Inputs -->
                                    <!-- Scheme -->
                                    <div class="col-md-4 d-none" id="emd_payment_scheme_div">
                                        <label class="form-label">Scheme</label>
                                        <select class="form-select" name="emd_payment_scheme">
                                            <option value="" selected disabled>Select Scheme</option>
                                            @foreach ($schemes as $sch)
                                                <option value="{{ $sch }}">{{ $sch }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Date -->
                                    <div class="col-md-4 d-none" id="emd_payment_date_div">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" name="emd_payment_date">
                                    </div>

                                    <!-- Mode of Payment -->
                                    <div class="col-md-4 d-none" id="emd_payment_mode_div">
                                        <label class="form-label">Mode of Payment</label>
                                        <select class="form-select" name="emd_payment_mode">
                                            <option value="" selected disabled>Select Mode</option>
                                            @foreach ($emdTypes as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <!-- EMD Refund Dependent Inputs -->
                                    <!-- Scheme -->
                                    <div class="col-md-4 d-none" id="emd_refund_scheme_div">
                                        <label class="form-label">Scheme</label>
                                        <select class="form-select" name="emd_refund_scheme">
                                            <option value="" selected disabled>Select Scheme</option>
                                            @foreach ($schemes as $sch)
                                                <option value="{{ $sch }}">{{ $sch }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Authority / Client -->
                                    <div class="col-md-4 d-none" id="emd_refund_authority_div">
                                        <label class="form-label">Authority/Client</label>
                                        <select class="form-select" name="emd_refund_authority">
                                            <option value="" selected disabled>Select Authority/Client</option>
                                            @foreach ($authorities as $at)
                                                <option value="{{ $at }}">{{ $at }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <button type="submit" class="btn btn-primary w-25 mt-4">Download</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection

@push('script')
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportType = document.getElementById('report_type');
            const category = document.getElementById('category');

            function hideAll() {
                document.querySelectorAll('[id$="_div"]').forEach(div => div.classList.add('d-none'));
            }

            function showDependent(report, cat) {
                hideAll();
                if (report === 'tender') {
                    if (cat === 'scheme') document.getElementById('tender_scheme_div').classList.remove('d-none');
                    if (cat === 'authority_client') document.getElementById('tender_authority_div').classList.remove('d-none');
                    if (cat === 'status') document.getElementById('tender_status_div').classList.remove('d-none');
                } else if (report === 'emd_payment') {
                    if (cat === 'scheme') document.getElementById('emd_payment_scheme_div').classList.remove('d-none');
                    if (cat === 'date') document.getElementById('emd_payment_date_div').classList.remove('d-none');
                    if (cat === 'mode_of_payment') document.getElementById('emd_payment_mode_div').classList.remove('d-none');
                } else if (report === 'emd_refund') {
                    if (cat === 'scheme') document.getElementById('emd_refund_scheme_div').classList.remove('d-none');
                    if (cat === 'authority_client') document.getElementById('emd_refund_authority_div').classList.remove('d-none');
                }
            }

            reportType.addEventListener('change', function() {
                const categoryOptions = {
                    tender: ['scheme', 'authority_client', 'status'],
                    emd_payment: ['scheme', 'date', 'mode_of_payment'],
                    emd_refund: ['scheme', 'authority_client']
                };

                const selectedReport = this.value;
                const options = categoryOptions[selectedReport] || [];

                category.innerHTML = '<option value="" selected disabled>Select</option>';
                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt;
                    option.textContent = opt.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    category.appendChild(option);
                });

                hideAll(); // hide dependent fields when report type changes
            });

            category.addEventListener('change', function() {
                showDependent(reportType.value, this.value);
            });
        });
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const reportType = document.getElementById('report_type');
            const category = document.getElementById('category');

            function hideAll() {
                document.querySelectorAll('[id$="_div"]').forEach(div => div.classList.add('d-none'));
            }

            function showDependent(report, cat) {
                hideAll();
                if (cat === "all") return; // hide everything for ALL

                if (report === 'tender') {
                    if (cat === 'scheme') document.getElementById('tender_scheme_div').classList.remove('d-none');
                    if (cat === 'authority_client') document.getElementById('tender_authority_div').classList.remove('d-none');
                    if (cat === 'status') document.getElementById('tender_status_div').classList.remove('d-none');
                } else if (report === 'emd_payment') {
                    if (cat === 'scheme') document.getElementById('emd_payment_scheme_div').classList.remove('d-none');
                    if (cat === 'date') document.getElementById('emd_payment_date_div').classList.remove('d-none');
                    if (cat === 'mode_of_payment') document.getElementById('emd_payment_mode_div').classList.remove('d-none');
                } else if (report === 'emd_refund') {
                    if (cat === 'scheme') document.getElementById('emd_refund_scheme_div').classList.remove('d-none');
                    if (cat === 'authority_client') document.getElementById('emd_refund_authority_div').classList.remove('d-none');
                }
            }

            reportType.addEventListener('change', function() {

                const categoryOptions = {
                    tender: ['scheme', 'authority_client', 'status'],
                    emd_payment: ['scheme', 'date', 'mode_of_payment'],
                    emd_refund: ['scheme', 'authority_client']
                };

                const selectedReport = this.value;
                const options = categoryOptions[selectedReport] || [];

                // build category dropdown with ALL option
                category.innerHTML = `
            <option value="" selected disabled>Select</option>
            <option value="all">All</option>
        `;

                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt;
                    option.textContent = opt.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                    category.appendChild(option);
                });

                hideAll();
            });

            category.addEventListener('change', function() {
                showDependent(reportType.value, this.value);
            });

        });
    </script>

    <script>
        $(document).ready(function() {
            $('#inter').change(function() {
                const selected = $(this).val();
                if (selected === 'Online Payment') {
                    $('.inter-onl').show();
                } else {
                    $('.inter-onl').hide();
                }
                if (selected === 'Fixed Deposit') {
                    $('.inter-fxd').show();
                } else {
                    $('.inter-fxd').hide();
                }
                if (selected === 'Bank Guarantee') {
                    $('.inter-bnk').show();
                } else {
                    $('.inter-bnk').hide();
                }
                if (selected === 'Damand Draft') {
                    $('.inter-dd').show();
                } else {
                    $('.inter-dd').hide();
                }
                if (selected === 'Others') {
                    $('.inter-oth').show();
                } else {
                    $('.inter-oth').hide();
                }
                if (selected === 'Cash') {
                    $('.inter-cln').show();
                } else {
                    $('.inter-cln').hide();
                }

            });
            // Trigger on page load if already selected
            $('#inter').trigger('change');
        });
    </script>
@endpush
