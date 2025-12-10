@extends('admin.layout.app')
@section('page_name', 'Dashboard')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Dashboard</strong></h3>
                    <p class="fw-bold fs-4 mb-0">Access Code: <span class="fw-normal text-muted"> 98765</span></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h1 class="mb-2"><span>&#8377;</span> 2382</h1>
                                    <h5 class="fw-bold letter-spacing-05 mb-0">Sales</h5>

                                </div>

                                <div class="col-auto">
                                    <div class="text-primary">
                                        <img src="{{ asset('assets/images/icons/image 82.png') }}" width="55px" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h1 class="mb-2"><span>&#8377;</span> 23</h1>
                                    <h5 class="fw-bold letter-spacing-05 mb-0">Tender</h5>

                                </div>

                                <div class="col-auto">
                                    <div class="text-primary">
                                        <img src="{{ asset('assets/images/icons/image 80.png') }}" width="55px" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h1 class="mb-2"><span>&#8377;</span> 2382</h1>
                                    <h5 class="fw-bold letter-spacing-05 mb-0">Auditor</h5>

                                </div>

                                <div class="col-auto">
                                    <div class="text-primary">
                                        <img src="{{ asset('assets/images/icons/image 83.png') }}" width="55px" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col mt-0">
                                    <h1 class="mb-2"><span>&#8377;</span> 2382</h1>
                                    <h5 class="fw-bold letter-spacing-05 mb-0">This Week New User</h5>

                                </div>

                                <div class="col-auto">
                                    <div class="text-primary">
                                        <img src="{{ asset('assets/images/icons/image 84.png') }}" width="55px" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- new user --}}
            <div class="row">
                <div class="col-12">

                    <div class="card">
                        <div class="card-body">
                            <table id="datatables-reponsive" class="table-striped table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Mobile Number</th>
                                        <th>Email Id</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Nick Name</td>
                                        <td>Business Name</td>
                                        <td>Projects</td>
                                        <td>Billed value</td>
                                        <td>Pending</td>
                                    </tr>
                                </tbody>
                            </table>
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
                    [5, 10, 25, 50, -1],
                    ["5", "10", "25", "50", "All"]
                ]
            });
        });
    </script>
@endpush
