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
        <div class="min-vh-100 d-flex align-items-center" style="overflow-y: scroll;">
            <div class="container-fluid">
                <div class="row justify-content-center align-items-center">
                    <!-- Left Side -->
                    <div class="col-md-6 bg-img">
                    </div>

                    <!-- Right Side -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h2 class="fs-2 fw-bold my-4 text-center">User Details</h2>

                            <form action="{{ route('non_gst_submit') }}" method="post">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">Business Legal Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control radius-15 border-1" name="business_legalname" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">Promoters Name</label>
                                    <input type="text" class="form-control radius-15 border-1" name="promotors_name">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">GST Number</label>
                                    <input type="text" class="form-control radius-15 border-1" name="gst_number">
                                </div>

                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fs-15 fw-bold">Pan Number</label>
                                        <input type="text" class="form-control radius-15 border-1" name="pan_number">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fs-15 fw-bold">Phone Number</label>
                                        <input type="text" class="form-control radius-15 border-1" maxlength="10" minlength="10" name="phone_number"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="{{ $phone }}" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">Email Address</label>
                                    <input type="text" class="form-control radius-15 border-1" name="email">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">Address</label>
                                    <textarea class="form-control radius-15 border-1" name="address" id="" rows="1"></textarea>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Next</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/js/app.js') }}"></script>
    </body>

    </html>
