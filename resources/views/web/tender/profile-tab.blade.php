<div class="card">
    <div class="card-body">
        {{-- tender details --}}
        <section class="row justify-content-between align-items-start">
            <div class="col-md-5">
                <ul class="dash-list m-0 p-0">
                    <div class="d-flex align-items-center">
                        <li class="mt-0 p-0"><strong class="fs-4">{{ $tender_prof->project_name }}</strong></li>
                        <a data-bs-toggle="modal" data-bs-target="#exampleModalToggle"><span
                                class="badge rounded-pill {{ $tender_prof->status == 'collected' ? 'text-bg-success' : 'text-bg-warning' }} ms-5 px-2">{{ $tender_prof->status }}</span></a>
                    </div>
                    <li class="fw-bold fs-15">{{ $tender_prof->authority }}</li>
                    <li class="fw-bold fs-15 mb-1">{{ $tender_prof->scheme }}</li>
                    {{-- <li>{{ $tender_prof-> }}</li> --}}
                    <li>{{ $tender_prof->tender_profile->address }}</li>
                </ul>
            </div>

            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-4 my-2">
                        <div class="d-block br-1">
                            <p class="mb-1">Tender Value</p>
                            <h4>₹ {{ number_format($tender->tender_value, 0) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block br-1">
                            <p class="mb-1">Bid Value</p>
                            <h4>₹ {{ number_format($tender->bid_value, 0) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block">
                            <p class="mb-1">EMD Value</p>
                            <h4>₹ {{ number_format($tender->emd_value, 0) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block br-1">
                            <p class="mb-1">Work Done Amount </p>
                            <h4>₹ {{ $tender->total_work_done }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block br-1">
                            <p class="mb-1">Taxable Amount</p>
                            <h4>₹ {{ $tender->total_taxable }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block">
                            <p class="mb-1">Deductions</p>
                            <h4>₹ {{ $tender->total_deduction }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block">
                            <p class="mb-1">GST</p>
                            <h4>₹ {{ $tender->total_gst }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block">
                            <p class="mb-1">LWF</p>
                            <h4>₹ {{ $tender->total_lwf }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 my-2">
                        <div class="d-block">
                            <p class="mb-1">Others</p>
                            <h4>₹ {{ $tender->total_others }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- status details --}}
        <section class="my-3">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="card-title text-dark mb-3">Collect EMD</h5>

                    <article class="br-3 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">

                                <img src="{{ asset('assets/images/icons/Files (1).png') }}" width="35px" height="45px" alt="">
                                <p class="fs-4 mb-0 ms-3">Tender Notice</p>
                                {{-- </div>
                            @if ($tender_prof->tendors_notes && file_exists(public_path($tender_prof->tendors_notes)))
                                <a href="{{ asset($tender_prof->tendors_notes) }}" download>
                                    <img src="{{ asset('assets/images/icons/Download.png') }}" width="25px">
                                </a>
                            @else --}}

                                {{-- @endif --}}

                            </div>
                            <p class="badge {{ optional($reminder->first())->status == 'collected' ? 'text-bg-success' : 'text-bg-warning'  }} mb-0">
                                {{ optional($reminder->first())->status ?? 'No status' }}</p>

                            {{-- <p class="text-justi fs-13 mb-0 mt-2">Lorem ipsum dolor sit, atext-justilit. Error magni perspiciatis optio voluptatum ullam eligendi.</p> --}}
                    </article>
                </div>
                <div class="col-md-8">
                    <h5 class="card-title text-dark mb-3">Status Timeline</h5>

                    <!-- Scrollable wrapper -->
                    <div class="d-flex custom-scrollbar-x gap-3 overflow-auto" style="white-space: nowrap; padding-bottom: 10px;">
                        @foreach ($tender_stat->chunk(2) as $chunk)
                            <div class="col-md-4 flex-shrink-0" style="min-width: 300px;">
                                <table class="table-bordered mb-0 table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($chunk as $ts)
                                            <tr>
                                                <td>{{ $ts->status_date }}</td>
                                                <td>{{ $ts->status }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </section>

        {{-- Tender Details --}}
        <section class="my-3">
            <h5 class="card-title text-dark">Tender Details</h5>
            <!-- Row 1 -->
            <div class="row dotted-row py-2">
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Remainder Date</label>
                    <p class="fw-medium text-dark mb-0">{{ date('d-m-Y', strtotime($tender_prof->remainder_date)) }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Location</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->location }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">ASN No</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->as_no }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">ASN Date</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->as_date }}</p>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="row dotted-row py-2">
                <div class="col-md-3">
                    <label class="text-secondary mb-0">TS No</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->ts_no }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">TS Date</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->ts_date }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">EMD Type</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->emd_type }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">EMD Date</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->emd_date }}</p>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="row dotted-row py-2">
                <div class="col-md-3">
                    <label class="text-secondary mb-0">GST Applicable</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->gst_applicable }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">HSN Code</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->hsn_code }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Year End Date</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->year_end_date }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Reference Id</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->reference_id }}</p>
                </div>
            </div>

            <!-- Row 4 -->
            <div class="row py-2">
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Bank Name</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->bank_name }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Account Number</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->account_no }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary mb-0">Date</label>
                    <p class="fw-medium text-dark mb-0">{{ $tender_prof->date }}</p>
                </div>
            </div>
        </section>

        {{-- attachment --}}
        <section>
            <h5 class="card-title text-dark mb-3">Attachment</h5>

            <div class="row">

                {{-- BG / EMD Scans --}}
                <div class="col-md-4">
                    <article class="d-flex align-items-center justify-content-between br-3 p-3">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('assets/images/icons/Files (1).png') }}" width="35" height="45" alt="">
                            <p class="fs-4 mb-0 ms-3">BG / EMD Scans</p>
                        </div>

                        @if ($tender_prof->bg_emd_scans)
                            <a href="{{ asset($tender_prof->bg_emd_scans) }}" download>
                                <img src="{{ asset('assets/images/icons/Download.png') }}" width="25">
                            </a>
                        @else
                            <span>No File</span>
                        @endif
                    </article>
                </div>

                {{-- Contract Agreements --}}
                <div class="col-md-4">
                    <article class="d-flex align-items-center justify-content-between br-3 p-3">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('assets/images/icons/Files (1).png') }}" width="35" height="45" alt="">
                            <p class="fs-4 mb-0 ms-3">Contract Agreements</p>
                        </div>

                        @if ($tender_prof->contract_agreements)
                            <a href="{{ asset($tender_prof->contract_agreements) }}" download>
                                <img src="{{ asset('assets/images/icons/Download.png') }}" width="25">
                            </a>
                        @else
                            <span>No File</span>
                        @endif
                    </article>
                </div>

                {{-- AS Copy --}}
                <div class="col-md-4">
                    <article class="d-flex align-items-center justify-content-between br-3 p-3">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('assets/images/icons/Files (1).png') }}" width="35" height="45" alt="">
                            <p class="fs-4 mb-0 ms-3">AS Copy</p>
                        </div>

                        @if ($tender_prof->as_copy)
                            <a href="{{ asset($tender_prof->as_copy) }}" download>
                                <img src="{{ asset('assets/images/icons/Download.png') }}" width="25">
                            </a>
                        @else
                            <span>No File</span>
                        @endif
                    </article>
                </div>

                {{-- Estimation Copy --}}
                <div class="col-md-4 mt-3">
                    <article class="d-flex align-items-center justify-content-between br-3 p-3">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('assets/images/icons/Files (1).png') }}" width="35" height="45" alt="">
                            <p class="fs-4 mb-0 ms-3">Estimation Copy</p>
                        </div>

                        @if ($tender_prof->estimation_copy)
                            <a href="{{ asset($tender_prof->estimation_copy) }}" download>
                                <img src="{{ asset('assets/images/icons/Download.png') }}" width="25">
                            </a>
                        @else
                            <span>No File</span>
                        @endif
                    </article>
                </div>

            </div>
        </section>

    </div>
</div>

<!-- status Modal -->
<div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fw-bold">Change Status</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('tender_status') }}" method="post">
                    @csrf
                    <input type="hidden" name="tender_id" value="{{ $tender_prof->id }}">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" id="">
                            <option value="" selected disabled>Select</option>
                            <option value="In Bidding">In Bidding</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Work Completed">Work Completed</option>
                            <option value="Completed">Completed</option>
                            <option value="Closed">Closed</option>
                            <option value="Dropped">Dropped</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary w-50">Close</button>
                        <button class="btn btn-primary w-50">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- collect emd -->
<div class="modal fade" id="exampleModalToggleStatus" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fw-bold">Collect EMD Amount</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" min="0">
                </div>

                <div class="mb-3">
                    <label class="form-label">Attachment</label>
                    <input type="file" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="" id=""rows="2"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary w-50">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
