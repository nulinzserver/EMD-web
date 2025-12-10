@extends('web.layout.app')
@section('page_name', 'Client')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Client</strong></h3>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gstModalToggle">
                        <i class="fa-solid fa-plus fs-4 me-1"></i> Add Client
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
                                        <th>Nick Name</th>
                                        <th>Business Name</th>
                                        <th>Projects</th>
                                        <th>Billed value</th>
                                        <th>Pending</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($formattedClients as $cl)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $cl['client']->nick_name }}</td>
                                            <td>{{ $cl['client']->business_legalname }}</td>

                                            <td>{{ $cl['project_count'] }}</td>
                                            <td>{{ number_format($cl['total_tender_value']) }}</td>
                                            <td>{{ number_format($cl['pending_amount']) }}</td>
                                            <td>
                                                @if ($cl['client']->status == 'without')
                                                    <a data-bs-toggle="modal" data-id="{{ $cl['client']->id }}" data-name="{{ $cl['client']->business_legalname }}"
                                                        data-pan="{{ $cl['client']->pan_no }}" data-promoter="{{ $cl['client']->promotors_name }}"
                                                        data-nick="{{ $cl['client']->nick_name }}" data-email="{{ $cl['client']->email }}"
                                                        data-address="{{ $cl['client']->address }}" data-city="{{ $cl['client']->city }}" data-state="{{ $cl['client']->state }}"
                                                        data-pincode="{{ $cl['client']->pincode }}" data-bs-target="#EditNonGstModalToggle"><img
                                                            src="{{ asset('assets/images/icons/Edit.png') }}" width="20px"></a>
                                                @endif
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
    <div class="modal fade" id="gstModalToggle" aria-hidden="true" aria-labelledby="gstModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header w-100">
                    <h5 class="fs-4 fw-bold m-0 mx-auto text-center">Choose Signup Option</h5>

                </div>
                <div class="modal-body">
                    <div class="d-flex gap-2 border-0 p-0">
                        <a data-bs-toggle="modal" data-bs-target="#exampleModalToggle" class="btn btn-outline-secondary w-50">With GST</a>
                        <a data-bs-toggle="modal" data-bs-target="#NonGstModalToggle" class="btn btn-primary w-50">Without GST</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- with gst --}}
    <!-- GST Verification Modal -->
    <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gstForm" action="{{ route('verifyGstSync') }}" method="post">
                        @csrf
                        <img src="{{ asset('assets/images/tax.png') }}" class="mx-auto" height="300px" alt="">

                        <div class="mb-3">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_no" class="form-control py-2" placeholder="GST987456512">
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary w-50">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Modal (appears after GST for GST details) -->
    <div class="modal fade" id="verifyModal" aria-hidden="true" aria-labelledby="verifyModalLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg text-center">
                <div class="modal-header border-0">
                    <h5 class="modal-title">GST Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">GST Number</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="gst_number_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Business Leagel Name</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="business_name_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Business Type</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="business_type_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Register Date</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="register_date_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Promoters Name</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="promoter_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Mail Id</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="email_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Address</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="address_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">City</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="city_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">State</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="state_text"></p>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="fs-5 fw-normal">Pincode</label>
                            <p class="fs-5 text-muted border-bottom-dotted mb-1 pb-2" id="pincode_text"></p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" data-bs-target="#verifyModal2" data-bs-toggle="modal">Verify</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Modal (appears after GST details for nick name) -->
    <div class="modal fade" id="verifyModal2" aria-hidden="true" aria-labelledby="verifyModalLabel" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Add Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <form action="{{ route('addNickName') }}" method="post">
                        @csrf
                        <input type="text" id="sync_id" name="sync_id">

                        <div class="my-3 text-start">
                            <label class="form-label">Nick Name</label>
                            <input type="text" name="nick_name" class="form-control py-2">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary w-50">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- without gst --}}
    {{-- non gst details model --}}
    <div class="modal fade" id="NonGstModalToggle" aria-hidden="true" aria-labelledby="NonGstModalToggle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg text-center">
                <div class="modal-header">
                    <h5 class="modal-title">Client Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nonGstForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Phone Number</label>
                                <input type="text" class="form-control mb-2" name="phone_number" maxlength="10" minlength="10"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Business Legal Name</label>
                                <input type="text" class="form-control mb-2" name="business_name">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">PAN Number</label>
                                <input type="text" class="form-control mb-2" name="pan_no" placeholder="ABCDE1234F">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">GST Number</label>
                                <input type="text" class="form-control mb-2" name="gst_no">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Promoters Name</label>
                                <input type="text" class="form-control mb-2" name="promoter_name">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Mail Id</label>
                                <input type="text" class="form-control mb-2" name="email">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Address</label>
                                <input type="text" class="form-control mb-2" name="address">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">City</label>
                                <input type="text" class="form-control mb-2" id="city_text" name="city">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">State</label>
                                <input type="text" class="form-control mb-2" id="state_text" name="state">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Pincode</label>
                                <input type="text" class="form-control mb-2" id="pincode_text" name="pincode" maxlength="6" minlength="6"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3 gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary w-50">Submit</button>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- non gst nick name modal --}}
    <div class="modal fade" id="verifyModal3" aria-hidden="true" aria-labelledby="verifyModalLabel" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Add Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <form action="{{ route('update_nick_name') }}" method="post">
                        @csrf
                        <input type="hidden" id="sync_id" name="sync_id">

                        <div class="my-3 text-start">
                            <label class="form-label">Nick Name</label>
                            <input type="text" name="nick_name" class="form-control py-2">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary w-50">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- edit non gst model --}}
    <div class="modal fade" id="EditNonGstModalToggle" aria-hidden="true" aria-labelledby="NonGstModalToggle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg text-center">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Client Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editNonGstForm" action="{{ route('update_client') }}" method="post">
                        @csrf
                        <input type="hidden" name="client_id" value="">

                        <div class="row">
                            {{-- <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Phone Number</label>
                                <input type="text" class="form-control mb-2" name="phone_number" maxlength="10" minlength="10"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div> --}}

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Business Legal Name</label>
                                <input type="text" class="form-control mb-2" name="business_name">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">PAN Number</label>
                                <input type="text" class="form-control mb-2" name="pan_no" placeholder="ABCDE1234F">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Nick Name</label>
                                <input type="text" class="form-control mb-2" name="nick_name">

                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Promoters Name</label>
                                <input type="text" class="form-control mb-2" name="promoter_name">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Mail Id</label>
                                <input type="text" class="form-control mb-2" name="email">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Address</label>
                                <input type="text" class="form-control mb-2" name="address">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">City</label>
                                <input type="text" class="form-control mb-2" id="city_text" name="city">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">State</label>
                                <input type="text" class="form-control mb-2" id="state_text" name="state">
                            </div>

                            <div class="col-md-6 text-start">
                                <label class="form-label fs-5 fw-normal">Pincode</label>
                                <input type="text" class="form-control mb-2" id="pincode_text" name="pincode" maxlength="6" minlength="6"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3 gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary w-50">Submit</button>
                        </div>
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
        $(document).ready(function() {
            $("#gstForm").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('verifyGstSync') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {

                            let d = response.data;


                            // Set sync_id into Nick Name modal hidden field
                            $("#sync_id").val(response.sync_id);

                            // Fill GST Details Modal
                            $("#gst_number_text").text(d.gst_no);
                            $("#business_name_text").text(d.business_legalname);
                            $("#business_type_text").text(d.business_type);
                            $("#register_date_text").text(d.register_date);
                            $("#promoter_text").text(d.promotors_name);
                            $("#email_text").text(d.email);
                            $("#address_text").text(d.address);
                            $("#city_text").text(d.city);
                            $("#state_text").text(d.state);
                            $("#pincode_text").text(d.pincode);

                            // Close GST Input Modal
                            $("#exampleModalToggle").modal("hide");

                            // Open GST Details Modal
                            $("#verifyModal").modal("show");
                        }
                    },
                    error: function(xhr) {
                        alert("GST number invalid or API error");
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#nonGstForm').on('submit', function(e) {
                e.preventDefault(); // prevent default form submit

                var formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('addNonGstClient') }}',
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            // Close first modal
                            $('#NonGstModalToggle').modal('hide');

                            // Set the sync_id in the second modal
                            $('#verifyModal3 #sync_id').val(res.sync_id);

                            // Open the second modal
                            var verifyModal3 = new bootstrap.Modal(document.getElementById('verifyModal3'));
                            verifyModal3.show();
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('Something went wrong. Please check your inputs.');
                    }
                });
            });
        });
    </script>
    <script>
        // for filling fields in edit model
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-bs-target="#EditNonGstModalToggle"]')) {

                let btn = e.target.closest('[data-bs-target="#EditNonGstModalToggle"]');

                // Target the new form ID: #editNonGstForm
                let form = document.querySelector('#editNonGstForm');

                form.querySelector('input[name="client_id"]').value = btn.dataset.id;
                form.querySelector('input[name="business_name"]').value = btn.dataset.name;
                form.querySelector('input[name="pan_no"]').value = btn.dataset.pan;
                form.querySelector('input[name="nick_name"]').value = btn.dataset.nick;


                form.querySelector('input[name="promoter_name"]').value = btn.dataset.promoter;
                form.querySelector('input[name="email"]').value = btn.dataset.email;
                form.querySelector('input[name="address"]').value = btn.dataset.address;
                form.querySelector('input[name="city"]').value = btn.dataset.city;
                form.querySelector('input[name="state"]').value = btn.dataset.state;
                form.querySelector('input[name="pincode"]').value = btn.dataset.pincode;
            }
        });
    </script>
@endpush
