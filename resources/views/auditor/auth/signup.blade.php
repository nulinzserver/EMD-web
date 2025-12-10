<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Sign Up</title>

    @include('auditor.layout.styles')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css">
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
                        <h3 class="fs-1 fw-semibold mb-1 text-center">Sign Up</h3>
                        <p class="my-3 text-center">Please login your account</p>

                        <form action="" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Firm Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control radius-15 border-1" name="">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control radius-15 border-1" name="">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Mail Id <span class="text-danger">*</span></label>
                                <input type="text" class="form-control radius-15 border-1" name="">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control radius-15 border-1" name="">
                            </div>
                            <a type="submit" href="{{ route('signup_otp_auditor') }}" class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Submit</a>

                        </form>
                        <p class="fs-14 mt-3 text-center">
                            Already have an account? <a href="{{ route('signin_auditor') }}" class="fs-17 text-blue"> Sign-in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
