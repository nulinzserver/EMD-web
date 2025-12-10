<style>
    #logoPreview,
    #signaturePreview {
        object-fit: contain;
        border: 1px solid #dee2e6;
    }
</style>
<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">

        <ul class="navbar-nav navbar-align">

            {{-- <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                    <div class="position-relative">
                        <i class="align-middle" data-feather="bell"></i>
                        <span class="indicator">4</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
                    <div class="dropdown-menu-header text-start">
                        New Notifications
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item py-2">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <i class="text-danger" style="width: 25px; height:25px" data-feather="alert-circle"></i>
                                </div>
                                <div class="col-10">
                                    <div class="text-dark">Update completed</div>
                                    <div class="text-muted small mt-1">Restart server 12 to complete the update.</div>
                                    <div class="text-muted small">30m ago</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModalAlert" class="list-group-item py-2">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <i class="text-warning" style="width: 25px; height:25px" data-feather="bell"></i>
                                </div>
                                <div class="col-10">
                                    <div class="text-dark">Reminder</div>
                                    <div class="text-muted small mt-1">Restart server 12 to complete the update.</div>
                                    <div class="text-muted small">30m ago</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </li> --}}

            <li class="nav-item dropdown">
                <a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">

                </a>
                <a class="nav-icon pe-md-0 dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('assets/images/avatar.jpg') }}" class="avatar img-fluid rounded" />

                        <span class="fs-5 ms-1">{{ auth()->user()->business_legalname }}</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end">

                    <a class='dropdown-item' data-bs-target="#exampleModalToggleProf" data-bs-toggle="modal"><img src="{{ asset('assets/images/icons/Edit.png') }}" class="me-1"
                            width="20px">Edit Profile</a>
                    <a data-bs-toggle="modal" class="dropdown-item" data-bs-target="#centeredModal"><img src="{{ asset('assets/images/icons/password-check.png') }}" class="me-1"
                            width="20px"> Change
                        Password</a>
                    <a class="dropdown-item" href="{{ route('add_user') }}"><img src="{{ asset('assets/images/icons/profile-add.png') }}" class="me-1" width="20px"> Add
                        User</a>
                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cameraModal"><img src="{{ asset('assets/images/icons/edit-2.png') }}" class="me-1"
                            width="20px"> Attachment</a>
                    {{-- <a class='dropdown-item' href='pages-settings.html' data-bs-toggle="modal" data-bs-target="#paymentModal"><img
                            src="{{ asset('assets/images/icons/wallet.png') }}" class="me-1" width="20px"> Paymemt</a> --}}

                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModalToggleLog"><img src="{{ asset('assets/images/icons/logout.png') }}" class="me-1"
                            width="20px"> Log
                        out</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

{{-- alarm --}}
<div class="modal fade" id="exampleModalAlert" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body pt-0">
                <div class="d-flex align-items-start px-2 pb-3 pt-0">
                    <img src="{{ asset('assets/images/icons/Icon Ilustration.png') }}" width="40px" alt="">
                    <div class="ms-3">
                        <div class="">
                            <p class="fs-12 text-muted mb-0">#EMD1204</p>
                        </div>
                        <p class="fs-15 text-dark fw-bold mb-0">Slum Clearance Project</p>

                        <p class="fs-14 text-muted mb-0 pt-1">Create text categories and populate them with your own text strings. You can also upload up to 20
                            PNGs
                            or JPEGs to
                            create image content. As a Content Creator you may choose to share it publicly in the Content Library or keep it private.</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary radius w-50">Update</button>
                    <button type="button" class="btn btn-primary radius w-50">Ping me in 2 days</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- change password -->
<div class="modal fade" id="centeredModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="fs-4 fw-bold modal-title">Change Password</h5>

            </div>
            <div class="modal-body pt-0">
                <form action="{{ route('change_password') }}" method="post">
                    @csrf
                    {{-- <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Old Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" name="old_password" id="old_password" value="{{ $password_change->password }}" required>
                            <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('old_password', 'icon_old')">
                                <i class="fas fa-eye-slash" id="icon_old"></i>
                            </span>
                        </div>
                    </div> --}}

                    <div class="col-md-12 mb-3">
                        <label class="form-label fs-15 fw-bold">New Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                            <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('new_password', 'icon_new')">
                                <i class="fas fa-eye-slash" id="icon_new"></i>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label fs-15 fw-bold">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('confirm_password', 'icon_confirm')">
                                <i class="fas fa-eye-slash" id="icon_confirm"></i>
                            </span>
                        </div>
                        <div id="passwordError" class="text-danger mt-1" style="display: none;"></div>
                    </div>

                    <div class="d-flex gap-2 border-0 p-0">
                        <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary w-50">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- log out --}}
<div class="modal fade" id="exampleModalToggleLog" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xss modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="my-4 text-center">
                    <h4>Ready to head out?</h4>
                    <p>You’re about to log out. See you next time!</p>
                </div>
                <div class="d-flex gap-2">
                    <form class="w-50" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary w-100">Logout</button>
                    </form>
                    <button type="button" class="btn btn-primary w-50" data-bs-dismiss="modal"> Stay Logged In</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- change logo and sign -->
<div class="modal fade" id="cameraModal" aria-hidden="true" aria-labelledby="cameraModalLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraModalLabel">Add Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('add_attach') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Logo</label>
                            <input type="file" name="logo" id="logoInput" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Signature</label>
                            <input type="file" name="signature" id="signatureInput" class="form-control" accept="image/*">
                        </div>

                        <div class="d-flex justify-content-center flex-wrap">
                            <div class="col-md-6 mb-3 text-center">
                                <label class="form-label fw-bold d-block">Attached Logo</label>
                                @if (!empty($attachemnt->logo) && file_exists(public_path($attachemnt->logo)))
                                    <img src="{{ asset($attachemnt->logo) }}" alt="Logo" width="250" height="250" style="border: 2px dashed #ccc;" id="logoPreview">
                                @else
                                    <p>No image</p>
                                @endif

                            </div>

                            <div class="col-md-6 mb-3 text-center">
                                <label class="form-label fw-bold d-block">Attached Signature</label>

                                @if (!empty($attachemnt->logo) && file_exists(public_path($attachemnt->logo)))
                                    <img src="{{ asset($attachemnt->signature) }}" alt="Signature Preview" width="250" height="250" style="border: 2px dashed #ccc;"
                                        id="signaturePreview">
                                @else
                                    <p>No image</p>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-end gap-2 border-0 p-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- payment -->
<div class="modal fade" id="paymentModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        {{-- <div class="modal-content">
            <div class="modal-header p-0" >
                <img src="{{ asset('assets/images/image 46.png') }}" class="pay-pop">

            </div>
            <div class="modal-body">
                <p class="text-muted fs-5">Enjoy unlimited access to premium features designed to make your journey smoother.</p>
                <form action="" method="post">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check1 form-check-inline1">
                                <input class="form-check-input1" type="checkbox" name="inlineRadioOptions">
                                <label class="form-check-label1 text-muted">Whats app</label>
                            </div>
                            <p class="fs-4 mb-0"><span>&#8377 </span>200</p>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check1 form-check-inline1">
                                <input class="form-check-input1" type="checkbox" name="inlineRadioOptions">
                                <label class="form-check-label1 text-muted" for="inlineRadio1">User-2</label>
                            </div>
                            <p class="fs-4 mb-0"><span>&#8377 </span>200</p>
                        </div>
                    </div>
                    <div class="col-md-12 bb-1 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="fs-5 mb-0">GST(18%)</p>
                            <p class="fs-4 mb-0"><span>&#8377 </span>200</p>
                        </div>
                    </div>
                    <div class="col-md-12 bb-1 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="fs-5 mb-0">Total</p>
                            <p class="fs-4 mb-0"><span>&#8377 </span>20550</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 border-0 p-0">
                        <input type="button" class="btn btn-primary w-100 radius" name="" value="Subscription Now">
                    </div>
                </form>
            </div>
        </div> --}}
        <div class="modal-content">
            <div class="modal-header position-relative p-0">
                <img src="{{ asset('assets/images/image 46.png') }}" class="pay-pop w-100">
                <div class="overlay-text">₹2999+GST/year</div>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-14">Enjoy unlimited access to premium features designed to make your journey smoother.</p>
                <form action="" method="post">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check1 form-check-inline1">
                                <input class="form-check-input1" type="checkbox" name="inlineRadioOptions">
                                <label class="form-check-label1 text-muted letter-spacing-05">Whats app</label>
                            </div>
                            <p class="fs-15 fw-bold mb-0"><span>&#8377 </span>200</p>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check1 form-check-inline1">
                                <input class="form-check-input1" type="checkbox" name="inlineRadioOptions">
                                <label class="form-check-label1 text-muted letter-spacing-05" for="inlineRadio1">User-2</label>
                            </div>
                            <p class="fs-15 fw-bold mb-0"><span>&#8377 </span>200</p>
                        </div>
                    </div>
                    <div class="col-md-12 bb-1 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="fs-5 mb-0">GST(18%)</p>
                            <p class="fs-15 fw-bold mb-0"><span>&#8377 </span>200</p>
                        </div>
                    </div>
                    <div class="col-md-12 bb-1 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="fs-5 mb-0">Total</p>
                            <p class="fs-15 fw-bold mb-0"><span>&#8377 </span>20550</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 border-0 p-0">
                        <input type="button" class="btn btn-primary w-100 radius" name="" value="Subscription Now">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- edit profile --}}
<div class="modal fade" id="exampleModalToggleProf" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="fs-4 fw-bold modal-title">Edit Profile</h5>
            </div>
            <div class="modal-body">

                <form action="{{ route('update_profile') }}" method="post">
                    @csrf

                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Business Legal Name</label>
                            <input type="text" name="legal_name" value="{{ $update_profile->business_legalname }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Business Type</label>
                            <input type="text" name="business_type" value="{{ $update_profile->nature_of_business }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Promoter Name</label>
                            <input type="text" name="promoter_name" value="{{ $update_profile->promotors_name }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Register Date</label>
                            <input type="date" name="register_date" value="{{ $update_profile->date_of_registration }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Annual Income</label>
                            {{-- <input type="text" name="annual_income" value="{{ $update_profile->turn_over }}" class="form-control" readonly> --}}
                            <select class="form-select border-1" name="turn_over">
                                <option value="" disabled>Select</option>

                                <option value="Below 1 crore" @selected($update_profile->turn_over == 'Below 1 crore')>
                                    Below 1 crore
                                </option>

                                <option value="1 to 3 crore" @selected($update_profile->turn_over == '1 to 3 crore')>
                                    1 to 3 crore
                                </option>

                                <option value="Above 3 crore" @selected($update_profile->turn_over == 'Above 3 crore')>
                                    Above 3 crore
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">PAN Number</label>
                            <input type="text" name="pan_number" value="{{ $update_profile->pan_number }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone_number" value="{{ $update_profile->phone_number }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="text" name="email" value="{{ $update_profile->email }}" class="form-control">
                        </div>
                        {{-- 
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control">{{ $update_profile->address }}</textarea>
                        </div> --}}
                    </div>

                    <div class="d-flex justify-content-end align-items-end gap-2 border-0 p-0">
                        <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary w-50">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
