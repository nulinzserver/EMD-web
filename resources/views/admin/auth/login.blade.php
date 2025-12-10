<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Sign Up</title>

    @include('admin.layout.styles')

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
                        <h2 class="fs-1 fw-semibold mb-3 text-center">Admin Panel</h2>
                        <p class="my-3 text-center">Please login your account</p>

                        <form class="" action="" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold fs-15">Mobile Number</label>
                                <input type="text" class="form-control radius-15 border-1" maxlength="10" minlength="10" name="contact"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Enter your number">
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
                                <a href="{{ route('forgot_pass_admin') }}" class="text-decoration-none">Forgot Password?</a>

                            </div>
                            <button type="submit" class="btn btn-primary fw-bold fs-17 w-100 radius-15">Sign In</button>
                        </form>
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
