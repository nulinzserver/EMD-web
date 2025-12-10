@extends('web.layout.app')
@section('page_name', 'Client')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Access</strong></h3>

                    <p class="text-muted fs-5 mb-0"><strong class="text-dark me-1">Access Code:</strong> 546868</p>
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
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Nick Name</td>
                                        <td>20/10/2025</td>
                                        <td>10:25 AM</td>
                                        <td><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalToggle">Remove Access</button> </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <!-- remote access -->
    <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xss modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">

                    <div class="mt-2 mb-4 text-center">
                        <h4>Confirm Removal</h4>
                        <p>Are you sure you want to remove this Access?
                            This action can’t be undone.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50" >Remove</button>
                        <button class="btn btn-primary w-50" data-bs-dismiss="modal">Cancel</button>
                    </div>
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
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    ["5", "10", "25", "50", "All"]
                ]
            });
        });
    </script>
@endpush
