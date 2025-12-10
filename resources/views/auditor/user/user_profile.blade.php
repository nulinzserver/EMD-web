@extends('auditor.layout.app')
@section('page_name', 'Profile')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Profile</strong></h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalToggleAcccess">Remove Access</button>
                </div>
            </div>
            <div class="row">
                {{-- fulltime driver table --}}
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md-5">
                                    <ul class="dash-list m-0 p-0">
                                        <div class="d-flex align-items-center">
                                            <li class="letter-spacing-05 mt-0 p-0"><strong class="fs-4">IronPeak Developers Pvt. Ltd.</strong></li>
                                        </div>
                                        <li class="fs-15 text-muted">Sri Bala - <span>GST987456123</span></li>
                                        <li class="fs-15 text-muted">+91 8438298692</li>
                                        <li class="fs-15 text-muted">invoka@gmail.com</li>
                                        <li class="fs-15 text-muted">near reliance mall, 5 roads, salem</li>
                                    </ul>
                                </div>

                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="text-muted letter-spacing-05 mb-2">In Bidding</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">Dropped</p>
                                                <h3 class="fs-18">987</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block">
                                                <p class="strong letter-spacing-05 mb-2">In Progress</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">Hold</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">Completed</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block">
                                                <p class="strong letter-spacing-05 mb-2">Closed</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mb-3"><strong>Bills</strong></h3>
                <div class="col-12">

                    <div class="card">
                        <div class="card-body">

                            <table id="datatables-reponsive" class="table-striped table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Work Done Amount</th>
                                        <th>Taxable Amount</th>
                                        <th>IT</th>
                                        <th>GST</th>
                                        <th>LWT</th>
                                        <th>Deposit</th>
                                        <th>Withheld Amount</th>
                                        <th>OTHERS</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 37,532</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td><span>&#8377;</span> 42,091</td>
                                        <td>Pending</td>
                                        <td class="d-flex gap-2">
                                            <a data-bs-toggle="modal" data-bs-target="#centeredModalStatus"><img src="{{ asset('assets/images/icons/Edit.png') }}" width="20px"></a>
                                            <a data-bs-toggle="modal" data-bs-target="#exampleModalToggleProfile"><img src="{{ asset('assets/images/icons/export.png') }}"
                                                    width="20px"></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- remove access --}}
    <div class="modal fade" id="exampleModalToggleAcccess" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xss modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="my-4 text-center">
                        <h4>Confirm Removal</h4>
                        <p>Are you sure you want to remove this User? This action can’t be undone.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary w-50">Remove</button>
                        <button type="button" class="btn btn-primary w-50" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- profile --}}
    <div class="modal fade" id="exampleModalToggleProfile" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fs-4 fw-bold modal-title">Project Profile</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- tender details --}}
                    <section class="row justify-content-between align-items-center">
                        <div class="col-md-5">
                            <ul class="dash-list m-0 p-0">
                                <div class="d-flex align-items-center">
                                    <li class="mt-0 p-0"><strong class="fs-4">Slum Clearance Project</strong></li>
                                    <a><span class="badge rounded-pill text-bg-warning ms-5 px-2">In Progress</span></a>
                                </div>
                                <li>#EMD1204 / <span>Government</span></li>
                                <li>Nulinz</li>
                                <li>Scheme: <span>Single Point Registration Scheme</span></li>
                            </ul>
                        </div>

                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-4 my-2">
                                    <div class="d-block br-1">
                                        <p class="mb-1">Tender Value</p>
                                        <h4>₹ 16,987</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="d-block br-1">
                                        <p class="mb-1">Bid Value</p>
                                        <h4>₹ 16,987</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="d-block">
                                        <p class="mb-1">EMD Value</p>
                                        <h4>₹ 16,987</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="d-block br-1">
                                        <p class="mb-1">Work Done Amount </p>
                                        <h4>₹ 16,987</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="d-block br-1">
                                        <p class="mb-1">Taxable Amount</p>
                                        <h4>₹ 16,987</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="d-block">
                                        <p class="mb-1">Deductions</p>
                                        <h4>₹ 16,987</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Tender Details --}}
                    <section class="mx-2 my-3">
                        <h5 class="card-title text-dark">Tender Details</h5>
                        <!-- Row 1 -->
                        <div class="row dotted-row py-2">
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">Location</label>
                                <p class="fw-medium text-dark mb-0">Salem</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">ASN No</label>
                                <p class="fw-medium text-dark mb-0">#EMD1204</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">ASN Date</label>
                                <p class="fw-medium text-dark mb-0">12/08/2025</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">TS No</label>
                                <p class="fw-medium text-dark mb-0">123456</p>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="row dotted-row py-2">
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">TS Date</label>
                                <p class="fw-medium text-dark mb-0">15/08/2025</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">EMD Type</label>
                                <p class="fw-medium text-dark mb-0">Online Payment</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">EMD Date</label>
                                <p class="fw-medium text-dark mb-0">12/08/2025</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">GST Applicable</label>
                                <p class="fw-medium text-dark mb-0">18%</p>
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="row dotted-row py-2">
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">HSN Code</label>
                                <p class="fw-medium text-dark mb-0">9945</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-secondary mb-0">Year End Date</label>
                                <p class="fw-medium text-dark mb-0">15/08/2025</p>
                            </div>
                        </div>
                    </section>

                    {{-- attachment --}}
                    <section>
                        <h5 class="card-title text-dark mb-3">Attachment</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <article class="d-flex align-items-center justify-content-between br-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('assets/images/icons/Files (1).png') }}" width="25px" height="30px" alt="">
                                        <p class="fs-4 mb-0 ms-3">Tender Notic</p>
                                    </div>
                                    <a class="mb-0"><img src="{{ asset('assets/images/icons/Download.png') }}" width="25px"></a>
                                </article>
                            </div>
                            <div class="col-md-4">
                                <article class="d-flex align-items-center justify-content-between br-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('assets/images/icons/Files.png') }}" width="25px" height="30px" alt="">
                                        <p class="fs-4 mb-0 ms-3">Tender Notic</p>
                                    </div>
                                    <a class="mb-0"><img src="{{ asset('assets/images/icons/Download.png') }}" width="25px"></a>
                                </article>
                            </div>
                            <div class="col-md-4">
                                <article class="d-flex align-items-center justify-content-between br-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('assets/images/icons/Files (2).png') }}" width="25px" height="30px" alt="">
                                        <p class="fs-4 mb-0 ms-3">Tender Notic</p>
                                    </div>
                                    <a class="mb-0"><img src="{{ asset('assets/images/icons/Download.png') }}" width="25px"></a>
                                </article>
                            </div>
                        </div>

                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- bill status -->
    <div class="modal fade" id="centeredModalStatus" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="fs-4 fw-bold modal-title">Bill Status</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Bill Status</label>
                            <select name="" id="" class="form-select">
                                <option value="" selected disabled>Select</option>
                                <option value="">Filed</option>
                                <option value="">Not Filed</option>
                                <option value="">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Sales Invoice No</label>
                            <input type="text" name="" id="" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Remark</label>
                            <textarea class="form-control" name="" id="" rows="3"></textarea>
                        </div>
                        <div class="d-flex gap-2 border-0 p-0">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Close</button>
                            <input type="button" class="btn btn-primary w-50" name="" value="Save">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Datatables Responsive
            $("#datatables-reponsive").DataTable({
                responsive: true,
                ordering: false,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    ["5", "10", "25", "50", "All"]
                ]
            });
        });
    </script>
@endpush
