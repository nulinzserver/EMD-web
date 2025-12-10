<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Sign Up</title>

    @include('auditor.layout.styles')

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
                        <form method="#" class="">
                            <h2 class="fs-2 fw-bold my-3 text-center">OTP</h2>

                            <h4 class="fw-bold mb-1 text-start">Enter code</h4>
                            <p class="text-muted fw-normal mb-0">Check your phone!</p>
                            <p class="text-muted fw-normal">Your activation code is on its way to via SMS.</p>
                            <div class="mb-3">
                                <div class="row gy-3 gx-4">
                                    <div class="col-3">
                                        <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                </div>
                            </div>
                            <p class="fs-5 fw-light text-dark mb-0 mt-4 text-center">Did'nt receive the code ?</p>

                            <span class="d-block fs-14 fw-bold mb-3 text-center"><a class="text-dark" href="">Resend
                                    OTP</a></span>

                            <a type="submit" href="{{ route('signup_pass_auditor') }}" class="btn btn-primary fw-bold fs-4 w-100 radius-15 mt-2">Verify</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
