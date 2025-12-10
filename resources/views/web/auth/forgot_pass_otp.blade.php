<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Forgot Password</title>

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
                        <form id="otpForm" action="{{ route('forgot_password_verifyotp') }}" method="post">
                            @csrf
                            <input type="hidden" name="ref_otp" value="{{ $otp }}">
                            <input type="hidden" id="phone_number" name="phone_number" value="{{ $phone }}">

                            <h2 class="fs-2 fw-bold my-3 text-center">OTP</h2>

                            <h4 class="fw-bold mb-1 text-start">Enter code</h4>
                            <p class="text-muted fw-normal mb-0">Check your phone!</p>
                            <p class="text-muted fw-normal">Your activation code is on its way to via SMS.</p>
                            <div class="mb-3">
                                <div class="row gy-3 gx-4">
                                    @for ($i = 1; $i <= 4; $i++)
                                        <div class="col-3">
                                            <input type="text" class="form-control curved border-1 otp-input text-center" maxlength="1" minlength="1"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); moveToNext(this, {{ $i }});">

                                        </div>
                                    @endfor
                                </div>
                                <input type="hidden" name="otp" id="otp">

                            </div>

                            <button class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Verify</button>
                        </form>

                        <form class="mt-3">
                            <input type="hidden" id="phone_number_resend" name="phone_number" value="{{ $phone }}">
                            <p class="fs-15 mb-0 text-center">OTP: {{ $otp }}</p>

                            <div class="d-flex align-items-center justify-content-center">

                                <p class="fs-5 fw-light text-dark mb-0 text-center">Did'nt receive the code ?</p>

                                <button class="text-dark fs-16" style="border:none;" id="resendBtn">Resend OTP</button>

                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        document.getElementById('resendBtn').addEventListener('click', function(e) {
            e.preventDefault();

            let phone = document.getElementById('phone_number_resend').value;

            fetch("{{ route('resend_otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        phone_number: phone
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("OTP Resent: " + data.otp); // remove in production
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error(error));
        });
    </script>
    <script>
        function moveToNext(current, index) {
            const inputs = document.querySelectorAll('.otp-input');
            if (current.value.length === 1 && index < inputs.length) {
                inputs[index].focus();
            }
        }

        document.getElementById('otpForm').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('.otp-input');
            let otp = '';
            inputs.forEach(input => otp += input.value);
            document.getElementById('otp').value = otp;
        });
    </script>
</body>

</html>
