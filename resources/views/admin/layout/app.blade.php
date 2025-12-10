<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('page_name')</title>

    <link rel="stylesheet" href="{{ asset('assets/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/light.css') }}">
    {{-- font CDN --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />

    @stack('style')
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <div class="wrapper">
        @include('admin.layout.sidebar')
        <div class="main">

            @include('admin.layout.navbar')
            @yield('content')

        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    @stack('script')

    {{-- toast and form disable --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
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

</body>

</html>
