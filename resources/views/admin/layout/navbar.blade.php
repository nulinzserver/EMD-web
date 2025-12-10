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

            <li class="nav-item dropdown">
                <a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">

                </a>
                <a class="nav-icon pe-md-0 dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('assets/images/avatar.jpg') }}" class="avatar img-fluid rounded" />

                        <span class="fs-5 ms-1">User Name</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end">

                    <div class="sidebar-user mb-2 px-2 py-0">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="position-relative flex-shrink-0">
                                <img src="{{ asset('assets/images/avatar.jpg') }}" class="img-fluid me-1 rounded" width="35px" height="35px" id="profileImage">
                            </div>
                            <div class="flex-grow-1 ps-2">
                                <a>Charles Hall</a>
                                <div class="sidebar-user-subtitle">Designer</div>
                            </div>
                        </div>
                    </div>

                    <a data-bs-toggle="modal" class="dropdown-item" data-bs-target="#centeredModal"><img src="{{ asset('assets/images/icons/password-check.png') }}" class="me-1"
                            width="20px"> Change
                        Password</a>

                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModalToggleLog"><img src="{{ asset('assets/images/icons/logout.png') }}" class="me-1"
                            width="20px"> Log
                        out</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- change password -->
<div class="modal fade" id="centeredModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="fs-4 fw-bold modal-title">Change Password</h5>

            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="text" name="" class="form-control" minlength="6" data-toggle="tooltip" data-placement="top"
                            title="Password needs to be at least 6 characters long">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Confirm Password</label>
                        <input type="text" name="" class="form-control" minlength="6">
                    </div>
                    <div class="d-flex gap-2 border-0 p-0">
                        <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Close</button>
                        <input type="button" class="btn btn-primary w-50" name="" value="Save">
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
                    <button class="btn btn-outline-dark w-50">Logout</button>
                    <button type="button" class="btn btn-primary w-50">Stay Logged In</button>
                </div>
            </div>
        </div>
    </div>
</div>
