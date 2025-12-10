@extends('web.layout.app')
@section('page_name', 'Add Tender')

@push('style')
@endpush

@section('content')

    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-none d-sm-block col-auto">
                    <h3><strong>Add Tender</strong></h3>
                </div>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('post_tender_two') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tender_ins_id" value={{ $id }}>
                            @csrf
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">ANS No </label>
                                    <input type="text" name="ans_no" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">ANS Date </label>
                                    <input type="date" name="ans_date" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">TS No </label>
                                    <input type="text" name="ts_no" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">TS Date </label>
                                    <input type="date" name="ts_date" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Tender Value <span class="text-danger">*</span></label>
                                    <input type="text" name="ts_value" class="form-control form-control-lg border-1" placeholder="" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Bid Value <span class="text-danger">*</span></label>
                                    <input type="text" name="bid_value" class="form-control form-control-lg border-1" placeholder="" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">EMD Value <span class="text-danger">*</span></label>
                                    <input type="text" name="emd_value" class="form-control form-control-lg border-1" placeholder="" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">GST Applicable<span class="text-danger">*</span></label>
                                    <input type="text" name="gst" class="form-control form-control-lg border-1" value={{ '18%' }} required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">HSN Code <span class="text-danger">*</span></label>
                                    <input type="text" name="hsn" class="form-control form-control-lg border-1" value={{ 9945 }} required>
                                </div>

                                {{-- <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Year End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control form-control-lg border-1" required>
                                </div> --}}

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">EMD Type <span class="text-danger">*</span></label>
                                    <select name="emd_type" class="form-select form-control-lg border-1" id="inter" required>
                                        <option value="" selected disabled>Select</option>
                                        <option value="Online Payment">Online Payment</option>
                                        <option value="Fixed Deposit">Fixed Deposit</option>
                                        <option value="Bank Guarantee">Bank Guarantee</option>
                                        <option value="Damand Draft">Damand Draft</option>
                                        <option value="Others">Others</option>
                                        <option value="Cash">Cash</option>
                                        <option value="EMD Exceptional">EMD Exceptional</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">EMD Date <span class="text-danger">*</span></label>
                                    <input type="date" name="emd_date" class="form-control form-control-lg border-1" placeholder="" required>
                                </div>

                                {{-- online payment --}}
                                <div class="col-md-3 inter-onl mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Reference ID <span class="text-danger">*</span></label>
                                    <input type="text" name="online_ref_id" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-onl mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="online_bank_name" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-onl mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="online_account_name" class="form-control form-control-lg border-1" required>
                                </div>

                                {{-- fixed deposit --}}
                                <div class="col-md-3 inter-fxd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Reference ID <span class="text-danger">*</span></label>
                                    <input type="text" name="fd_ref_id" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-fxd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="fd_bank_name" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-fxd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="fd_acc_name" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-fxd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Maturity Date <span class="text-danger">*</span></label>
                                    <input type="date" name="fd_maturity" class="form-control form-control-lg border-1" required>
                                </div>

                                {{-- Bank Guarantee --}}
                                <div class="col-md-3 inter-bnk mb-3" style="display: none;">
                                    <label class="form-label fw-bold">BG Reference ID <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_ref_id" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-bnk mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-bnk mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_acc_number" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-bnk mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Issue Date <span class="text-danger">*</span></label>
                                    <input type="date" name="bank_issue_date" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-bnk mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Expire Date <span class="text-danger">*</span></label>
                                    <input type="date" name="bank_expire" class="form-control form-control-lg border-1" required>
                                </div>

                                {{-- demand draft --}}

                                <div class="col-md-3 inter-dd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Reference ID <span class="text-danger">*</span></label>
                                    <input type="text" name="dd_ref_id" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-dd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="dd_bank_name" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-dd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="dd_account" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-dd mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="dd_date" class="form-control form-control-lg border-1">
                                </div>

                                {{-- other --}}

                                <div class="col-md-3 inter-oth mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Reference ID <span class="text-danger">*</span></label>
                                    <input type="text" name="ref_id" class="form-control form-control-lg border-1">
                                </div>

                                <div class="col-md-3 inter-oth mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="other_bank" class="form-control form-control-lg border-1">
                                </div>

                                <div class="col-md-3 inter-oth mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="other_acc" class="form-control form-control-lg border-1">
                                </div>

                                {{-- cash --}}
                                <div class="col-md-3 inter-cln mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Challan Identification Number <span class="text-danger">*</span></label>
                                    <input type="text" name="cash_challan" class="form-control form-control-lg border-1" required>
                                </div>

                                <div class="col-md-3 inter-cln mb-3" style="display: none;">
                                    <label class="form-label fw-bold">Challan Date <span class="text-danger">*</span></label>
                                    <input type="date" name="cash_challan_date" class="form-control form-control-lg border-1" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2 mt-2">
                                    <button class="btn btn-primary w-100" id="submit_btn">Next</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

@endsection

@push('script')
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
