<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('page_name')</title>

    <link rel="shortcut icon" href="{{ asset('assets/images/TN SYMBOL.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/light.css') }}">
    {{-- font CDN --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />

    @stack('style')
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <div class="wrapper">
        @include('web.layout.sidebar')
        <div class="main">

            @include('web.layout.navbar')
            @yield('content')

        </div>
    </div>

    @if (session('message'))
        <div aria-live="polite" aria-atomic="true" class="position-relative" style="z-index: 1100;">
            <div class="toast-container position-fixed end-0 top-0 p-3">
                <div class="toast align-items-center text-bg-success show border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('message') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>

    @stack('script')

    {{-- toast and form disable --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('successToast');
            // var toast = new bootstrap.Toast(toastEl);
            // toast.show();
        });
    </script>
    {{-- disable button --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btn = document.getElementById("submit_btn");

            if (btn) {
                btn.addEventListener("click", function() {
                    btn.disabled = true;
                    btn.innerHTML = "Submitting..."; // optional
                    this.closest("form").submit();
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle=" tooltip"]')) tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new
                bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Logo preview
            const logoInput = document.getElementById('logoInput');
            const logoPreview = document.getElementById('logoPreview');

            logoInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        logoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    logoPreview.src = ''; // Clear preview if invalid file
                }
            });

            // Signature preview
            const signatureInput = document.getElementById('signatureInput');
            const signaturePreview = document.getElementById('signaturePreview');

            signatureInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        signaturePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    signaturePreview.src = ''; // Clear preview if invalid file
                }
            });
        });
    </script>
    <script>
        function togglePassword(fieldId, iconId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        // Password match validation (new_password vs confirm_password)
        document.getElementById('confirm_password').addEventListener('input', function() {
            const pass = document.getElementById('new_password').value;
            const conf = this.value;
            const errorDiv = document.getElementById('passwordError');

            if (pass && conf && pass !== conf) {
                errorDiv.textContent = "Passwords do not match!";
                errorDiv.style.display = "block";
            } else {
                errorDiv.style.display = "none";
            }
        });
    </script>

</body>

</html>
