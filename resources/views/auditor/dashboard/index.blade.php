@extends('auditor.layout.app')
@section('page_name', 'Dashboard')

@push('style')
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Dashboard</strong></h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalToggleAcccess">Get Access</button>
                </div>
            </div>
            <div class="row">
                {{-- fulltime driver table --}}
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md-5">
                                    <ul class="dash-list m-0 p-0">
                                        <div class="d-flex align-items-center">
                                            <li class="letter-spacing-05 mt-0 p-0"><strong class="fs-4">Slum Clearance Project</strong></li>
                                        </div>
                                        <li class="fs-15 text-muted">91876545678</li>
                                        <li class="fs-15 text-muted">invoka@gmail.com</li>
                                        <li class="fs-15 text-muted">near reliance mall, 5 roads, salem</li>
                                    </ul>
                                </div>

                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="text-muted letter-spacing-05 mb-2">Client</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block br-1">
                                                <p class="strong letter-spacing-05 mb-2">Total Bill</p>
                                                <h3 class="fs-18">987</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block">
                                                <p class="strong letter-spacing-05 mb-2">Pending Bill</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 my-2">
                                            <div class="d-block">
                                                <p class="strong letter-spacing-05 mb-2">Today New Bill</p>
                                                <h3 class="fs-18">16</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">

                    <div class="card">
                        <div class="card-body">
                            <table id="datatables-reponsive" class="table-striped table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Buissness Legal Name</th>
                                        <th>Project Name</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Name</td>
                                        <td>Projects</td>
                                        <td>Billed value</td>
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

    {{-- get access --}}
    <div class="modal fade" id="exampleModalToggleAcccess" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form action="" method="post">
                        <h4 class="fw-bold mb-1 text-start">Enter Partner Code</h4>
                        <p class="text-muted fw-normal">Enter the remote user’s code to join securely.</p>
                        <div class="mb-4">
                            <div class="row gy-3 gx-4">
                                <div class="col-2">
                                    <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-2">
                                    <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-2">
                                    <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-2">
                                    <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-2">
                                    <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-2">
                                    <input type="text" class="form-control curved border-1 text-center" name="" id="" maxlength="1" minlength="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-end gap-2 border-0 p-0">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Cancel </button>
                            <input type="button" class="btn btn-primary w-50" name="" value="Submit">
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
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    ["5", "10", "25", "50", "All"]
                ]
            });
        });
    </script>
@endpush
