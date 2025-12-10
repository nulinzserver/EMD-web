    <!DOCTYPE html>
    <html lang="en">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>Sign Up</title>

        @include('web.layout.styles')

        <style>
            body {
                overflow: hidden;
            }

            .bg-img {
                background-image: url('{{ asset('assets/images/signup-banner.png') }}');
                background-size: cover;
                background-position: center;
                height: 100vh;
            }
        </style>
    </head>
    </style>
    </head>

    <body class="reg-bg">
        <div class="min-vh-100 d-flex align-items-center">
            <div class="container-fluid">
                <div class="row justify-content-center align-items-center">
                    <!-- Left Side -->
                    <div class="col-md-6 bg-img">
                    </div>

                    <!-- Right Side -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h2 class="fs-2 fw-bold my-4 text-center">GST Details</h2>

                            @php
                                $gst = session('gst_details');
                                $otp = session('otp');
                            @endphp

                            @if ($gst)
                                <form action="{{ route('signup_otp') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="otp" value="{{ $otp }}">
                                    <input type="hidden" name="gst" value="{{ $gst['gst_number'] }}">
                                    <input type="hidden" name="phone_number" value="{{ $gst['phone_number'] }}">

                                    <h3 class="fw-normal fs-16 mb-1">Business Legal Name</h3>
                                    <p class="fw-normal fs-16 text-muted mb-1">{{ $gst['business_legalname'] }}</p>

                                    <ul class="list-unstyled step-list">
                                        <li class="d-flex align-items-start">
                                            {{-- <i class="fa fa-fw fa-circle fs-4 step-icon me-2"></i> --}}
                                            <div>
                                                <h5 class="fs-16 fw-normal mb-0">Promoters Name</h5>
                                                <p class="fs-16 fw-normal text-muted mb-0">{{ $gst['promotors_name'] }}</p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            {{-- <i class="fa fa-fw fa-circle fs-4 step-icon me-2"></i> --}}
                                            <div>
                                                <h5 class="fs-16 fw-normal mb-0">Pan Number</h5>
                                                <p class="fs-16 fw-normal text-muted mb-0">{{ $gst['pan_number'] }}</p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            {{-- <i class="fa fa-fw fa-circle fs-4 step-icon me-2"></i> --}}
                                            <div>
                                                <h5 class="fs-16 fw-normal mb-0">Phone Number</h5>
                                                <p class="fs-16 fw-normal text-muted mb-0">{{ $gst['phone_number'] }}</p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            {{-- <i class="fa fa-fw fa-circle fs-4 step-icon me-2"></i> --}}
                                            <div>
                                                <h5 class="fs-16 fw-normal mb-0">Email Address</h5>
                                                <p class="fs-16 fw-normal text-muted mb-0">{{ $gst['email'] }}</p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            {{-- <i class="fa fa-fw fa-circle fs-4 step-icon me-2"></i> --}}
                                            <div>
                                                <h5 class="fs-16 fw-normal mb-0">Address</h5>
                                                <p class="fs-16 fw-normal text-muted mb-0">{{ $gst['address'] }}</p>
                                            </div>
                                        </li>
                                    </ul>

                                    <div class="mt-3">
                                        <button class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Next</a>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/js/app.js') }}"></script>
    </body>

    </html>
