@extends('admin.layout.app')
@section('page_name', 'Auditor')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Auditor</strong></h3>
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
                                        <th>Firm Name</th>
                                        <th>Mobile Number</th>
                                        <th>Mail Id</th>
                                        <th>Location</th>
                                        <th>Clients</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Nick Name</td>
                                        <td>Projects</td>
                                        <td>Billed value</td>
                                        <td>Pending</td>
                                        <td>Pending</td>
                                        <td>Pending</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
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
