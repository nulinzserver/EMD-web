   <div class="row">

       @foreach ($tender_bill as $tb)
           <div class="col-md-6">
               <div class="card">
                   <div class="card-head p-4 pb-0">
                       <div class="d-flex justify-content-between align-items-center">
                           <div class="d-inline-flex">
                               <h5 class="m-0 p-0"><strong class="fs-4">{{ $tb->payment_type }}</strong></h5>
                               @if ($tb->status == 'pending')
                               @endif
                               <a><span
                                       class="badge rounded-pill {{ $tb->status == 'pending' ? 'bg-secondary' : 'bg-success  ' }} ms-3 px-3 text-white">{{ $tb->status }}</span></a>
                           </div>
                           {{-- {{ $tb->id }} --}}
                           <div>
                               @if ($tb->status == 'pending')
                                   {{-- <a class="btn btn-primary btn-sm openCollectModal" data-bs-toggle="modal" data-bs-target="#exampleModalToggleCLAmnt" data-id="{{ $tb->id }}"
                                       data-date="{{ date('Y-m-d', strtotime($tb->created_at)) }}" data-amount="{{ $tb->total_amount }}">Collect Amount</a> --}}
                                   <a class="btn btn-primary btn-sm openCollectModal" data-bs-toggle="modal" data-bs-target="#exampleModalToggleCLAmnt" data-id="{{ $tb->id }}"
                                       data-date="{{ date('Y-m-d', strtotime($tb->created_at)) }}" data-amount="{{ $tb->total_amount }}">
                                       Collect Amount
                                   </a>
                               @endif
                               @if ($tb->payment_type == 'Part Payment' && $tb->status == 'collected')
                                   {{-- <a class="btn btn-primary btn-sm" href="">Generate Invoice</a> --}}
                                   <a class="btn btn-primary btn-sm" href="{{ route('invoice.generate', $tb->id) }}">
                                       Generate Invoice
                                   </a>
                               @endif

                               @if ($tb->payment_type == 'Full Payment' && $tb->status == 'collected')
                                   <a class="btn btn-primary btn-sm" href="{{ route('invoice.generate', $tb->id) }}" target="_blank">
                                       Generate Invoice
                                   </a>
                                   <a class="btn btn-primary btn-sm openRemninderModal" data-bs-toggle="modal" data-bs-target="#exampleModalToggleRemin"
                                       data-bill-id="{{ $tb->id }}" data-tender-id="{{ $tb->t_id }}">EMD Reminder</a>
                               @endif
                           </div>
                       </div>
                   </div>
                   <div class="card-body pt-2">
                       <!-- Row 1 -->
                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium fs-16 text-dark mb-0">#</p>
                           </div>
                           <div class="col-md-4">
                               <p class="fw-medium fs-16 text-dark mb-0">Amount</p>
                           </div>
                           <div class="col-md-4">
                               <p class="fw-medium fs-16 text-dark mb-0">Status</p>
                           </div>
                       </div>

                       <!-- Row 2 -->
                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">Work Done </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->work_done_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">Taxable Amount </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->taxable_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">IT </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->it_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">CGST </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->cgst_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">SGST </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->sgst_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">LWF </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->lwf_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">Others </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->others_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">Withheld Amount </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->withheld_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       <div class="row dotted-row py-2">
                           <div class="col-md-4">
                               <p class="fw-medium text-dark mb-0">TOTAL </p>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">₹ {{ $tb->total_amount }}</label>
                           </div>
                           <div class="col-md-4">
                               <label class="text-secondary mb-0">--</label>
                           </div>
                       </div>

                       @php
                           $billRemarks = $collect_bill->where('bill_id', $tb->id);
                       @endphp

                       @if ($tb->status == 'collected' && $billRemarks->isNotEmpty())
                           <article class="pt-3">
                               <div class="row align-items-center">
                                   <div class="col-md-6">
                                       <div class="d-flex align-items-center justify-content-between">

                                           <div class="d-inline-flex align-items-center">
                                               <a href=""><img src="{{ asset('assets/images/icons/Files.png') }}" width="35px" height="35px" alt=""></a>
                                               <p class="fs-14 fw-bold mb-0 ms-3">Tender Notic</p>
                                           </div>

                                           <a class="mb-0" href="{{ asset($billRemarks->first()->attachment) }}" download><img
                                                   src="{{ asset('assets/images/icons/Download.png') }}" width="25px"></a>
                                       </div>
                                   </div>
                                   {{-- <div class="col-md-6 bl-1">
                                   <div class="d-flex align-items-center justify-content-between">

                                       <div class="d-inline-flex align-items-center">
                                           <h5 class="fs-14 fw-bold mb-0">Invoice No</h5>
                                           <h5 class="fs-14 fw-normal mb-0 ms-3">INC5455</h5>
                                       </div>

                                       <a class="mb-0" data-bs-toggle="modal" data-bs-target="#exampleModalToggleInvno"><img
                                               src="{{ asset('assets/images/icons/Edit.png') }}" width="25px"></a>
                                   </div>
                               </div> --}}
                               </div>
                               <h5 class="card-title text-dark mb-0 mt-3">Remark</h5>

                               @foreach ($billRemarks as $collect)
                                   <p class="text-justi fs-14 mb-0 mt-1">{{ $collect->remark }}</p>
                               @endforeach

                               {{-- <p class="text-justi fs-14 mb-0 mt-1">{{ $tender_prof->collected_bill_amounts->remark }}</p> --}}
                           </article>
                       @endif

                   </div>
               </div>
           </div>
       @endforeach

   </div>

   <!-- add bill -->
   <div class="modal fade" id="exampleModalToggleBill" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
       <div class="modal-dialog modal-lg modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h4 class="modal-title fw-bold">Add Bill</h4>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <form action="{{ route('add_billing') }}" method="post" enctype="multipart/form-data">
                       @csrf
                       <input type="hidden" name="mc_id" value={{ $tender_prof->mc_id }}>
                       <input type="hidden" name="tender_id" value={{ $tender_prof->id }}>

                       <div class="d-flex align-items-center gap-3">
                           <div class="form-check form-check-inline">
                               <input class="form-check-input" type="radio" name="paymentType" id="partPayment" value="Part Payment">
                               <label class="form-check-label" for="partPayment">Part Payment</label>
                           </div>

                           <div class="form-check form-check-inline">
                               <input class="form-check-input" type="radio" name="paymentType" id="fullPayment" value="Full Payment">
                               <label class="form-check-label" for="fullPayment">Full Payment</label>
                           </div>
                       </div>

                       <div class="row mt-3">
                           <div class="col-md-4 my-2">
                               <label class="form-label">Work Done Amount <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="work_done" id="WORK_DONE" min="0">
                           </div>
                           <div class="col-md-4 my-2">
                               <label class="form-label">Taxable Amount <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="taxable_amount" id="TAXABLE" min="0">
                           </div>
                       </div>
                       <div class="row">
                           <h6 class="fw-bold mt-2">Deduction</h6>
                           <div class="col-md-4 my-2">
                               <label class="form-label">IT <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="IT" id="IT" min="0">
                           </div>
                           <div class="col-md-4 my-2">
                               <label class="form-label">CGST <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="csgt" id="CGST" min="0">
                           </div>
                           <div class="col-md-4 my-2">
                               <label class="form-label">SGST <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="sgst" id="SGST" min="0">
                           </div>
                           <div class="col-md-4 my-2">
                               <label class="form-label">LWF <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="lwf" id="LWF" min="0">
                           </div>
                           <div class="col-md-4">
                               <label class="form-label">Withheld Amount <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="withheld" id="WITHHELD" min="0">
                           </div>
                           <div class="col-md-4 my-2">
                               <label class="form-label">Others <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" name="others" id="OTHERS" min="0">
                           </div>

                           <div class="col-md-4">
                               <label class="form-label">Collection Proof <span class="text-danger">*</span></label>
                               <input type="file" class="form-control" name="collection_proof">
                           </div>
                           <div class="col-md-4">
                               <label class="form-label">Remarks <span class="text-danger">*</span></label>
                               <input type="text" class="form-control" name="remarks">
                           </div>
                       </div>

                       <div class="row justify-content-end mt-4 gap-2">
                           <div class="col-md-4">
                               <div class="d-flex align-items-center" style="border-bottom: 2px dashed #ccc">
                                   <span class="me-2">&#8377; </span><input type="number" id="total_deduction" name="total_deduction"
                                       class="fs-4 border-bottom-dotted w-100 text-danger mb-0 pb-2" readonly />
                               </div>
                           </div>
                           <div class="col-md-4">
                               <div class="d-flex align-items-center" style="border-bottom: 2px dashed #ccc">
                                   <label class="form-label mb-0 me-3 pb-2">Total </label>
                                   <span class="me-2">&#8377; </span><input type="number" id="grand_total" name="grand_total"
                                       class="fs-4 border-bottom-dotted w-100 text-danger mb-0 pb-2" />
                               </div>
                           </div>
                       </div>

                       <div class="d-flex justify-content-end mt-4 gap-2">
                           <button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Close</button>
                           {{-- /<button class="btn btn-primary w w-25" data-bs-target="#verifyModal" data-bs-toggle="modal">Collect</button> --}}
                           <button id="submit_btn" class="btn btn-primary w-25">Collect
                           </button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>

   {{-- edit invoice --}}
   <div class="modal fade" id="exampleModalToggleInvno" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
       <div class="modal-dialog modal-xss modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="fs-4 fw-bold modal-title">Edit Invoice No</h5>

               </div>
               <div class="modal-body">

                   <form action="" method="post">
                       <div class="row">
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Invoice No</label>
                               <input type="text" name="" id="" class="form-control">
                           </div>
                       </div>

                       <div class="d-flex justify-content-end align-items-end gap-2 border-0 p-0">
                           <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                           <input type="button" class="btn btn-primary w-50" name="" value="Update">
                       </div>
                   </form>

               </div>
           </div>
       </div>
   </div>

   {{-- emd reminder --}}
   <div class="modal fade" id="exampleModalToggleRemin" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
       <div class="modal-dialog modal-xss modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="fs-4 fw-bold modal-title">EMD Remainder</h5>

                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">

                   <form action="{{ route('createEmdReminderWeb') }}" method="post">
                       @csrf
                       <input type="hidden" id="modal_bill_id" name="b_id">
                       <input type="hidden" id="modal_tender_id" name="t_id">

                       <div class="row">
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Date</label>
                               <input type="date" name="remainder_date" id="" class="form-control">
                           </div>
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Notes</label>
                               <input type="text" name="notes" id="" class="form-control">
                           </div>
                       </div>

                       <div class="d-flex justify-content-end align-items-end gap-2 border-0 p-0">
                           <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                           <button type="submit" class="btn btn-primary w-50" name="">Save</button>
                       </div>
                   </form>

               </div>
           </div>
       </div>
   </div>

   {{-- Collect Amount --}}
   <div class="modal fade" id="exampleModalToggleCLAmnt" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
       <div class="modal-dialog modal-xss modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="fs-4 fw-bold modal-title">Collect Amount</h5>

                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <form action="{{ route('collect_amount') }}" method="post" enctype="multipart/form-data">
                       @csrf
                       <input type="text" class="form-control" name="bill_id" id="modal_bill_ide">
                       <input type="hidden" name="mc_id" value={{ $tender_prof->mc_id }}>
                       <input type="hidden" name="tender_id" value={{ $tender_prof->id }}>

                       <div class="row">
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Date</label>
                               <input type="date" name="date" id="modal_bill_date" class="form-control">
                           </div>
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Amount</label>
                               <input type="number" name="amount" id="modal_bill_amount" min="0" class="form-control">
                           </div>
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Attachment</label>
                               <input type="file" name="attachment" class="form-control">
                           </div>
                           <div class="col-md-12 mb-3">
                               <label class="form-label fw-bold">Remark</label>
                               <input type="text" name="remark" class="form-control">
                           </div>
                       </div>

                       <div class="d-flex justify-content-end align-items-end gap-2 border-0 p-0">
                           <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                           <button class="btn btn-primary w-50">Save</button>
                       </div>
                   </form>

               </div>
           </div>
       </div>
   </div>
