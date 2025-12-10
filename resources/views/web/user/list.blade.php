@extends('web.layout.app')
@section('page_name', 'User')

@push('style')
    <style>
        .form-check-inline {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            transform: scale(1.3);
            margin-right: 8px;
            vertical-align: middle;
        }

        .form-check-label {
            font-size: 1rem;
            line-height: 1.5;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Add User</strong></h3>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalToggle">
                        <i class="fa-solid fa-plus fs-4 me-1"></i> Add User
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">

                    <div class="card">
                        <div class="card-body">
                            <table id="datatables-reponsive" class="table-striped table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Mobile Number</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $us)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $us->name }}</td>
                                            <td>{{ $us->mobile_number }}</td>
                                            <td>{{ $us->role }}</td>
                                            <td>
                                                <a href="#" class="editUserBtn" data-id="{{ $us->id }}" data-name="{{ $us->name }}" data-role="{{ $us->role }}"
                                                    data-mobile="{{ $us->mobile_number }}" data-status="{{ $us->status }}" data-permission="{{ $us->permission }}"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">

                                                    <img src="{{ asset('assets/images/icons/Edit.png') }}" width="20px" alt="">
                                                </a>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="fs-4 fw-bold modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="userForm" action="{{ route('post_update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" id="name" name="name" class="form-control">
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Role</label><br>

                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="role" id="partPayment" value="Manager">
                                    <label class="form-check-label" for="partPayment">Manager</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="role" id="fullPayment" value="Accountant">
                                    <label class="form-check-label" for="fullPayment">Accountant</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Mobile Number</label>
                            <input type="text" name="mobile_number" id="mobile_number" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                                class="form-control">
                        </div>

                        {{-- <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Password</label>
                            <input type="text" id="password" name="password" class="form-control">
                        </div>
`
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Confirm Password</label>
                            <input type="text" id="confirm_password" class="form-control">
                        </div> --}}

                        <div class="col-md-12 mb-2">
                            <label class="form-label fs-15 fw-bold">Password</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" name="password" id="password" required>
                                <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('password')">
                                    <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label fs-15 fw-bold">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye-slash" id="toggleConfirmPasswordIcon"></i>
                                </span>
                            </div>
                            <div id="passwordError" class="text-danger mt-1" style="display: none;"></div>
                        </div>

                        {{-- <small id="passError" class="text-danger"></small> --}}

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Permission</label><br>

                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="permission[]" id="dashpay" value="Dashboard">
                                    <label class="form-check-label" for="dashpay">Dashboard</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="permission[]" id="repopay" value="Report">
                                    <label class="form-check-label" for="repopay">Report</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="" selected disabled>Select</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        @if ($user_limit >= 2)
                            <p class="fw-bold fs-13 text-danger mx-auto mt-3 text-center">“You need to pay ₹500 to add a user”</p>
                        @endif

                        <div class="d-flex gap-2 border-0 p-0">
                            <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Close</button>
                            @if ($user_limit >= 2)
                                <button class="btn btn-primary w-50 saveUserBtn">Paynow</button>
                            @else
                                <button class="btn btn-primary w-50 saveUserBtn">Save</button>
                            @endif
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editModal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form action="{{ route('update_user') }}" method="POST">
                        @csrf
                        <input type="hidden" id="edit_id" name="id">

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" id="edit_name" name="name" class="form-control">
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Role</label><br>
                            <div class="d-flex gap-3">
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="role" value="Manager" id="edit_role_manager">
                                    Manager
                                </label>

                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="role" value="Accountant" id="edit_role_accountant">
                                    Accountant
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Mobile Number</label>
                            <input type="text" id="edit_mobile" name="mobile_number" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                                class="form-control">
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold">Permission</label><br>

                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-check-inline">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permission[]" value="Dashboard" id="edit_perm_dashboard">
                                        Dashboard
                                    </label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permission[]" value="Report" id="edit_perm_report">
                                        Report
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <button class="btn btn-primary w-100">Update</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Datatables Responsive
            $("#datatables-reponsive").DataTable({
                responsive: true,
                ordering: false,
                pageLength: 10,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    ["5", "10", "25", "50", "All"]
                ]
            });
        });
    </script>
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
    </script>

    <script>
        $(document).on("click", ".editUserBtn", function() {

            // --- Data Retrieval ---
            let status = $(this).data("status"); // e.g., "Active" or "Inactive"
            let role = $(this).data("role");
            let permission = $(this).data("permission")?.split(',') ?? [];

            // --- Set Simple Inputs ---
            $("#edit_id").val($(this).data("id"));
            $("#edit_name").val($(this).data("name"));
            $("#edit_mobile").val($(this).data("mobile"));

            // --- Set Status (Dropdown) ---
            // Ensure the value 'status' exactly matches the option value
            $("#edit_status").val(status);
            // Note: The .change() is often unnecessary for simple value setting

            console.log("Status from data attribute:", status);
            console.log("Status set on select element:", $("#edit_status").val());


            // --- Set Role (Radio Buttons) ---
            $("#edit_role_manager").prop("checked", role == "Manager");
            $("#edit_role_accountant").prop("checked", role == "Accountant");

            // --- Set Permission (Checkboxes) ---
            $("#edit_perm_dashboard").prop("checked", permission.includes("Dashboard"));
            $("#edit_perm_report").prop("checked", permission.includes("Report"));

        });
    </script>
@endpush
