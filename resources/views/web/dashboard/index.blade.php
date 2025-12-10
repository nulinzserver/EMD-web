@extends('web.layout.app')
@section('page_name', 'Dashboard')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Dashboard</strong></h3>
                    {{-- <p class="fw-bold fs-4 mb-0">Access Code: <span class="fw-normal text-muted"> 98765</span></p> --}}
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
                                            <li class="letter-spacing-05 mt-0 p-0"><strong class="fs-4">{{ $user->business_legalname }}</strong></li>
                                        </div>
                                        <li class="fs-15 text-muted">{{ $user->gst_number }}</li>
                                        <li class="fs-15 text-muted">{{ $user->phone_number }}</li>
                                        <li class="fs-15 text-muted">{{ $user->email }}</li>
                                        <li class="fs-15 text-muted">{{ $user->address }}</li>
                                    </ul>
                                </div>

                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="text-muted letter-spacing-05 mb-2">Trunover</p>
                                                <h3 class="fs-18">₹ {{ $turnValue }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">Client</p>
                                                <h3 class="fs-18">{{ $clientsCount }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block">
                                                <p class="strong letter-spacing-05 mb-2">Tenders</p>
                                                <h3 class="fs-18">{{ $tendersCount }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">EMD</p>
                                                <h3 class="fs-18">₹ {{ number_format($totalAmount) }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">Withheld Amount</p>
                                                <h3 class="fs-18">₹ {{  number_format($withHeld) }}</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block">
                                                <p class="strong letter-spacing-05 mb-2">Other Receivable</p>
                                                <h3 class="fs-18">{{  number_format($otherAmount) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Overall Expense --}}
                <div class="col-md-6">
                    <div class="card" style="height: 400px">
                        <div class="card-body">
                            <div class="row justify-content-between align-items-center">
                                <h5 class="col-md-6 card-title text-dark">Overall Expense</h5>

                                <div>
                                    <div id="simpleDonutChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Overall Expense Category --}}
                <div class="col-md-6">
                    <div class="card" style="height: 400px">
                        <div class="card-head p-4 pb-0">
                            <h5 class="col-md-6 card-title text-dark">Overall Expense Category</h5>
                        </div>
                        <div class="card-body overflow-y no-scrollbar overflow-y-scroll p-0">
                            <ul class="row justify-content-between align-items-center m-0 p-0">
                                @foreach ($grouped as $cat => $total)
                                    <li class="d-flex align-items-center justify-content-between br-2 p-3">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="35px" alt="">
                                            <p class="fs-15 letter-spacing-05 mb-0 ms-3">{{ $cat }}</p>
                                        </div>
                                        <h4 class="fs-17 mb-0">
                                            ₹ <span>{{ $total }}</span>
                                        </h4>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="card" style="height:450px">
                        <div class="card-head p-4 pb-0">
                            <h5 class="col-md-6 card-title text-dark">Upcoming Collection</h5>
                        </div>
                        <div class="card-body no-scrollbar overflow-y-scroll p-0">
                            <ul class="row m-0 p-0">
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-center br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="d-flex align-items-start justify-content-between w-100">
                                            <div class="ms-3">
                                                <p class="fs-4 mb-0">#EMD1204</p>
                                                <p class="fs-13 mb-0">Slum Clearance Project</p>
                                                <p class="fs-11 text-muted mb-0">Government</p>
                                            </div>
                                            <h5 class="fs-15 mb-0 mt-1">&#8377; <span>524566</span></h5>

                                        </div>
                                    </div>
                                </li>
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-center br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="d-flex align-items-start justify-content-between w-100">
                                            <div class="ms-3">
                                                <p class="fs-4 mb-0">#EMD1204</p>
                                                <p class="fs-13 mb-0">Slum Clearance Project</p>
                                                <p class="fs-11 text-muted mb-0">Government</p>
                                            </div>
                                            <h5 class="fs-15 mb-0 mt-1">&#8377; <span>524566</span></h5>

                                        </div>
                                    </div>
                                </li>
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-center br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="d-flex align-items-start justify-content-between w-100">
                                            <div class="ms-3">
                                                <p class="fs-4 mb-0">#EMD1204</p>
                                                <p class="fs-13 mb-0">Slum Clearance Project</p>
                                                <p class="fs-11 text-muted mb-0">Government</p>
                                            </div>
                                            <h5 class="fs-15 mb-0 mt-1">&#8377; <span>524566</span></h5>

                                        </div>
                                    </div>
                                </li>
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-center br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="d-flex align-items-start justify-content-between w-100">
                                            <div class="ms-3">
                                                <p class="fs-4 mb-0">#EMD1204</p>
                                                <p class="fs-13 mb-0">Slum Clearance Project</p>
                                                <p class="fs-11 text-muted mb-0">Government</p>
                                            </div>
                                            <h5 class="fs-15 mb-0 mt-1">&#8377; <span>524566</span></h5>

                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card" style="height:450px">
                        <div class="card-head p-4 pb-0">
                            <h5 class="col-md-6 card-title text-dark">Auditor Update</h5>
                        </div>
                        <div class="card-body no-scrollbar overflow-y-scroll p-0">
                            <ul class="row m-0 p-0">
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-start br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="d-flex align-items-start justify-content-between w-100">
                                            <div class="ms-3">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <p class="fs-4 text-dark mb-0">#EMD1204</p>

                                                    <h4 class="text-muted fs-13 mb-0">S12355</h4>
                                                </div>
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <p class="fs-5 mb-0">Slum Clearance Project</p>

                                                    <h4 class="fs-15 mb-0">&#8377; <span>524566</span></h4>
                                                </div>
                                                <p class="fs-12 text-muted mb-0 pt-1">Create text categories and populate them with your own text strings. You can also upload up to 20
                                                    PNGs
                                                    or JPEGs to
                                                    create image content. As a Content Creator you may choose to share it publicly in the Content Library or keep it private.</p>
                                            </div>

                                        </div>
                                    </div>
                                </li>
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-start br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="d-flex align-items-start justify-content-between w-100">
                                            <div class="ms-3">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <p class="fs-4 text-dark mb-0">#EMD1204</p>

                                                    <h4 class="text-muted fs-13 mb-0">S12355</h4>
                                                </div>
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <p class="fs-5 mb-0">Slum Clearance Project</p>

                                                    <h4 class="fs-15 mb-0">&#8377; <span>524566</span></h4>
                                                </div>
                                                <p class="fs-12 text-muted mb-0 pt-1">Create text categories and populate them with your own text strings. You can also upload up to 20
                                                    PNGs
                                                    or JPEGs to
                                                    create image content. As a Content Creator you may choose to share it publicly in the Content Library or keep it private.</p>
                                            </div>

                                        </div>
                                    </div>
                                </li>
                                <li class="px-3 py-1">
                                    <div class="d-flex align-items-start br-3 p-2">
                                        <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="40px" alt="">
                                        <div class="ms-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <p class="fs-4 text-dark mb-0">#EMD1204</p>

                                                <h4 class="text-muted fs-13 mb-0">S12355</h4>
                                            </div>
                                            <div class="d-flex align-items-start justify-content-between">
                                                <p class="fs-5 mb-0">Slum Clearance Project</p>

                                                <h4 class="fs-15 mb-0">&#8377; <span>524566</span></h4>
                                            </div>
                                            <p class="fs-12 text-muted mb-0 pt-1">Create text categories and populate them with your own text strings. You can also upload up to 20
                                                PNGs
                                                or JPEGs to
                                                create image content. As a Content Creator you may choose to share it publicly in the Content Library or keep it private.</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> --}}

            </div>
        </div>
    </main>
@endsection

@push('script')
    <script>
        var expenseLabels = @json($chartLabels);
        var expenseAmounts = @json($chartAmounts);

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
                style: {
                    fontSize: '14px',
                    colors: ['#fff']
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#simpleDonutChart"), options);
        chart.render();
    </script>
@endpush
