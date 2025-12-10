@extends('web.layout.app')
@section('page_name', 'Tender')

@push('style')
    <style>
        .clr-icn {
            color: #595656;
        }
    </style>
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><strong>Tender</strong></h3>

                    <a href="{{ route('add_tender') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus fs-4 me-1"></i> Tender
                    </a>

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
                                        <th>Tender Id</th>
                                        <th>Project Title</th>
                                        <th>Contract</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tender as $td)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $td->tender_no }}</td>
                                            <td>{{ $td->project_name }}</td>
                                            <td>{{ $td->contractor }}</td>
                                            <td>{{ $td->tender_value }}</td>
                                            <td>
                                                {{ $td->status }}
                                                {{-- <span class="badge rounded-pill text-bg-warning px-2">In Progress</span> --}}
                                            </td>
                                            <td class="d-flex gap-2">
                                                <a href="{{ route('edit_tender', $td->id) }}"><img src="{{ asset('assets/images/icons/Edit.png') }}" width="20px"></a>
                                                <a href="{{ route('tender_profile', $td->id) }}"><img src="{{ asset('assets/images/icons/export.png') }}" width="20px"></a>
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
@endpush
