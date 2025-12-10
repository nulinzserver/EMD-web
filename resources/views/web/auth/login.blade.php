<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Sign Up</title>

    @include('web.layout.styles')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css">

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
                        <h2 class="fs-1 fw-semibold mb-3 text-center">Welcome Back</h2>
                        <p class="my-3 text-center">Please login your account</p>

                        <form class="" method="POST" action="{{ route('signin_check') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold fs-15">Mobile Number</label>
                                <input type="text" class="form-control radius-15 border-1" name="mobile_number" placeholder="Enter your number">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Password</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control radius-15 border-1" name="password" id="password" placeholder="Enter your password" required>
                                    <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('password')">
                                        <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                                <div id="passwordError" class="text-danger mt-1" style="display: none;"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="{{ route('forgot_pass') }}" class="text-decoration-none">Forgot Password?</a>

                            </div>
                            <button class="btn btn-primary fw-bold fs-17 w-100 radius-15">Sign In</button>
                        </form>
                        <p class="fs-14 mt-3 text-center">
                            Dont have an account? <a data-bs-toggle="modal" data-bs-target="#centeredModal" class="fs-17 text-blue ms-1"> Sign-up</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (session('login_error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div class="toast show align-items-center text-white bg-danger border-0">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('login_error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div> @endif

    <!-- change password -->
<div class="modal
        fade" id="centeredModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header w-100">
                <h5 class="fs-4 fw-bold m-0 mx-auto text-center">Choose Signup Option</h5>

            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 border-0 p-0">
                    <a href="{{ route('signup_gst') }}" class="btn btn-outline-secondary w-50">With GST</a>
                    <a href="{{ route('signup_no_gst') }}" class="btn btn-primary w-50">Without GST</a>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById('togglePasswordIcon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
    </body>

</html>
