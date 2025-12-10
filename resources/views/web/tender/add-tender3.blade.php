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
                        <form action="{{ route('post_tender_three') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tender_id" value={{ $id }}>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">AS Copy</label>
                                    <input type="file" name="as_copy" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">TS Copy (Estimation Copy) </label>
                                    <input type="file" name="ts_copy" class="form-control form-control-lg border-1" placeholder="" >
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Work Order (Tender Notice) </label>
                                    <input type="file" name="work_order" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Contract Agreements </label>
                                    <input type="file" name="contract" class="form-control form-control-lg border-1" placeholder="" >
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">EMD Scans </label>
                                    <input type="file" name="emd_scan" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Others </label>
                                    <input type="file" name="other" class="form-control form-control-lg border-1" placeholder="">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Notes <span class="text-danger">*</span></label>
                                    <input type="text" name="notes" class="form-control form-control-lg border-1" placeholder="" required>
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
