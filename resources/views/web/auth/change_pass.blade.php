    <!DOCTYPE html>
    <html lang="en">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>Sign Up</title>

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
                            <h3 class="fs-1 fw-semibold mb-1 text-center">Reset Password</h3>
                            <p class="fw-normal text-muted mb-3 text-center">Password must be at least 8 <br> characters with letters & numbers.</p>
                            <form action="{{ route('forgot_password_reset') }}" method="POST" id="passwordForm">
                                @csrf

                                <input type="hidden" name="phone_number" value="{{ $phone }}">

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">Password</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control radius-15 border-1" name="password" id="password" required>
                                        <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('password')">
                                            <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-15 fw-bold">Confirm Password</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control radius-15 border-1" name="confirm_password" id="confirm_password" required>
                                        <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye-slash" id="toggleConfirmPasswordIcon"></i>
                                        </span>
                                    </div>
                                    <div id="passwordError" class="text-danger mt-1" style="display: none;"></div>
                                </div>

                                <button class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Reset Password</button>
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
                const icon = document.getElementById(fieldId === 'password' ? 'togglePasswordIcon' : 'toggleConfirmPasswordIcon');

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

            // Real-time password matching check
            document.getElementById('confirm_password').addEventListener('input', function() {
                const pass = document.getElementById('password').value;
                const conf = this.value;
                const errorDiv = document.getElementById('passwordError');

                if (pass && conf && pass !== conf) {
                    errorDiv.textContent = 'Passwords do not match!';
                    errorDiv.style.display = 'block';
                } else {
                    errorDiv.style.display = 'none';
                }
            });

            // Prevent form submission if passwords don't match
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                const pass = document.getElementById('password').value;
                const conf = document.getElementById('confirm_password').value;
                const errorDiv = document.getElementById('passwordError');

                if (pass !== conf) {
                    e.preventDefault();
                    errorDiv.textContent = 'Passwords must match to submit!';
                    errorDiv.style.display = 'block';
                    return false;
                }
            });
        </script>
    </body>

    </html>
