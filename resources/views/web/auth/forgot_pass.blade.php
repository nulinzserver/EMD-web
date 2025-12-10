<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Forgot Password</title>

    @include('web.layout.styles')

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

                        <h2 class="fs-1 fw-semibold mb-0 text-center">Forgot Passoword</h2>
                        <p class="my-3 mt-2 text-center">Please enter your registered Mobile <br> Number to reset your password.</p>

                        <form method="post" action="{{ route('forgot_pass_submit') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Mobile Number</label>
                                <input type="text" class="form-control radius-15 border-1" maxlength="10" minlength="10" name="phone_number"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Enter registered phone number">
                            </div>
                            <button class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Send OTP</button>
                        </form>
                        <p class="fs-14 mt-4 text-center">Back to <a href="{{ route('login') }}" class="fs-17 text-blue text-decoration-none ms-1">Sign In</a></p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>

</body>

</html>
