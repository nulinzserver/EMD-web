@extends('web.layout.app')
@section('page_name', 'Edit Tender')

@push('style')
@endpush

@section('content')

    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-none d-sm-block col-auto">
                    <h3><strong>Edit Tender</strong></h3>
                </div>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('update_tender_three') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tender_id" value="{{ $tender_details->id }}">

                            <div class="row">

                                <!-- AS Copy -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">AS Copy <span class="text-danger">*</span></label>

                                    @if ($tender_details->as_copy)
                                        <p>
                                            <a href="{{ asset($tender_details->as_copy) }}" download class="text-primary fw-bold">
                                                Download AS Copy
                                            </a>
                                        </p>
                                    @endif

                                    <input type="file" name="as_copy" class="form-control form-control-lg border-1">
                                </div>

                                <!-- TS Copy -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">TS Copy <span class="text-danger">*</span></label>

                                    @if ($tender_details->estimation_copy)
                                        <p>
                                            <a href="{{ asset($tender_details->estimation_copy) }}" download class="text-primary fw-bold">
                                                Download TS Copy
                                            </a>
                                        </p>
                                    @endif

                                    <input type="file" name="ts_copy" class="form-control form-control-lg border-1">
                                </div>

                                <!-- Work Order -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Work Order <span class="text-danger">*</span></label>

                                    @if ($tender_details->tendors_notes)
                                        <p>
                                            <a href="{{ asset($tender_details->tendors_notes) }}" download class="text-primary fw-bold">
                                                Download Work Order
                                            </a>
                                        </p>
                                    @endif

                                    <input type="file" name="work_order" class="form-control form-control-lg border-1">
                                </div>

                                <!-- Contract Agreements -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Contract Agreements <span class="text-danger">*</span></label>

                                    @if ($tender_details->contract_agreements)
                                        <p>
                                            <a href="{{ asset($tender_details->contract_agreements) }}" download class="text-primary fw-bold">
                                                Download Contract
                                            </a>
                                        </p>
                                    @endif

                                    <input type="file" name="contract" class="form-control form-control-lg border-1">
                                </div>

                                <!-- EMD Scans -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">EMD Scans<span class="text-danger">*</span></label>

                                    @if ($tender_details->bg_emd_scans)
                                        <p>
                                            <a href="{{ asset($tender_details->bg_emd_scans) }}" download class="text-primary fw-bold">
                                                Download EMD Scan
                                            </a>
                                        </p>
                                    @endif

                                    <input type="file" name="emd_scan" class="form-control form-control-lg border-1">
                                </div>

                                <!-- Others -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Others <span class="text-danger">*</span></label>

                                    @if ($tender_details->others)
                                        <p>
                                            <a href="{{ asset($tender_details->others) }}" download class="text-primary fw-bold">
                                                Download Other File
                                            </a>
                                        </p>
                                    @endif

                                    <input type="file" name="other" class="form-control form-control-lg border-1">
                                </div>

                                <!-- Notes -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Notes <span class="text-danger">*</span></label>
                                    <input type="text" name="notes" class="form-control form-control-lg border-1" value="{{ old('notes', $tender_details->notes) }}" required>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-2 mt-2">
                                    <button class="btn btn-primary w-100" id="submit_btn">Submit</button>
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
@endpush
