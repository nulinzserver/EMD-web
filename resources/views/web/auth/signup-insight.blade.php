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
                        <h3 class="fs-1 fw-semibold mb-4 text-center">Just Insight</h3>
                        <form action="{{ route('update_insights') }}" method="POST">
                            @csrf
                            <input type="text" name="gst" value="{{ $gst }}">
                            <input type="text" name="phone" value="{{ $phone }}">

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">How Many Project Will You Done</label>
                                <input type="text" class="form-control radius-15 border-1" name="projects">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Turnover</label>
                                <select class="form-select radius-15 border-1" name="turn_over" id="">
                                    <option value="" selected disabled>select</option>
                                    <option value="Below 1 crore">Below 1 crore</option>
                                    <option value="1 to 3 crore">1 to 3 crore</option>
                                    <option value="Above 3 crore">Above 3 crore</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Contactor Type</label>
                                <select class="form-select radius-15 border-1" name="contractor_type" id="">
                                    <option value="" selected disabled>select</option>
                                    <option value="Electrical Contractor">Electrical Contractor</option>
                                    <option value="Contractor Type">Contractor Type</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-15 fw-bold">Challange</label>
                                <select class="form-select radius-15 border-1" name="challenge" id="">
                                    <option value="" selected disabled>select</option>
                                    <option value="Static 1">Static 1</option>
                                    <option value="Static 2">Static 2</option>
                                    <option value="Static 3">Static 3</option>
                                    <option value="Static 4">Static 4</option>
                                </select>
                            </div>

                            <button class="btn btn-primary fw-bold fs-17 w-100 radius-15 mt-2">Next</button>

                            <a href="{{ route('dashboard') }}" class="fw-bold fs-15 w-100 d-block mt-3 text-center">Skip</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
