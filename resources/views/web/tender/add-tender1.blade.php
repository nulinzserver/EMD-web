@extends('web.layout.app')
@section('page_name', 'Add Tender')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
        }
    </style>
@endpush

@section('content')

    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-none d-sm-block col-auto">
                    <h3><strong>Add Tender</strong></h3>
                </div>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('post_tender_one') }}" method="POST">
                            @csrf
                            <input type="hidden" name="mc_id" value={{ auth()->user()->id }} id="">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Tender No/ID <span class="text-danger">*</span></label>
                                    <input type="text" name="tender_id" class="form-control form-control-lg border-1" placeholder="" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Project Name <span class="text-danger">*</span></label>
                                    <input type="text" name="project_name" class="form-control form-control-lg border-1" placeholder="" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="d-flex form-label fw-bold">Contract <span class="text-danger">*</span></label>

                                    <div class="d-flex gap-4">
                                        <div class="d-flex align-item-center">
                                            <input class="form-check-input fs-3 me-2 mt-0" type="radio" name="contract_type" id="Yes" value="Government">
                                            <label class="form-check-label fs-5" for="Yes">Government</label>
                                        </div>
                                        <div class="d-flex align-item-center">
                                            <input class="form-check-input fs-3 me-2 mt-0" type="radio" name="contract_type" id="inlineCheckbox2" value="Private">
                                            <label class="form-check-label fs-5" for="inlineCheckbox2">Private</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label fw-bold">Authority/Client <span class="text-danger">*</span></label>

                                        <a class="badge bg-badge bg-dark fs-13 mb-0" href="{{ route('client_list') }}">+ Client</a>
                                    </div>
                                    <select class="form-select form-control-lg border-1 select2-addable py-3" id="clientSelect" name="client" required>
                                        <option value="" selected disabled>Select</option>
                                        @foreach ($nick_name as $nl)
                                            <option value="{{ $nl->id }}">{{ $nl->nick_name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Scheme <span class="text-danger">*</span></label>
                                    {{-- <select class="form-select form-control-lg border-1" id="clientSelect" name="scheme">
                                        <option value="" selected disabled>Select</option>
                                        @foreach ($scheme as $sc)
                                            <option value="{{ $sc }}">{{ $sc }}</option>
                                        @endforeach
                                    </select> --}}
                                    <select class="form-select form-control-lg border-1 select2-addable" id="schemeSelect" name="scheme">
                                        <option value="" selected disabled>Select</option>
                                        @foreach ($scheme as $sc)
                                            <option value="{{ $sc }}">{{ $sc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 inter-det mb-3">
                                    <label class="form-label fw-bold">Year End Date <span class="text-danger">*</span></label>
                                    @php
                                        $currentYear = date('Y');
                                    @endphp

                                    <select name="year_range" class="form-select">
                                        @for ($y = $currentYear; $y <= $currentYear + 50; $y++)
                                            <option value="{{ $y }}-{{ $y + 1 }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                                {{ $y }} - {{ $y + 1 }}
                                            </option>
                                        @endfor
                                    </select>

                                </div>
                                <script>
                                    function setAcademicYear() {
                                        let y = parseInt(document.getElementById('year').value);
                                        document.getElementById('academic_year').value = `${y}-${y+1}`;
                                    }
                                </script>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg border-1" name="location" placeholder="" required/>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select class="form-select border-1" name="status" id="">
                                        <option value="" selected disabled>Select</option>
                                        <option value="In Bidding">In Bidding</option>
                                        <option value="Dropped">Dropped</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="On Hold">On Hold</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Closed">Closed</option>
                                        <option value="Not Started">Not Started</option>
                                        <option value="Dummy">Dummy</option>
                                        <option value="Active">Active</option>
                                    </select>
                                </div>

                                <div class="col-md-3 inter-det mb-3">
                                    <label class="form-label fw-bold">Reminder Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-lg border-1" name="reminder_date" required>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-2 mt-2">
                                    <button class="btn btn-primary w-100" id="submit_btn">Next</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row inter-set" style="display: none;">
                <div class="card border p-0">
                    <div id="map" style="width: 100%; height: 450px;"></div>
                </div>
            </div>

        </div>
    </main>

    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control mb-3" placeholder="Enter client name">
                    <button type="button" class="btn btn-primary w-100">Save</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // add button
        document.getElementById('clientSelect').addEventListener('change', function() {
            if (this.value === 'add') {
                // Open modal
                var modal = new bootstrap.Modal(document.getElementById('addClientModal'));
                modal.show();

                // Reset dropdown back to default
                this.value = '';
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.select2-addable').select2({
                tags: true, // ⭐ ALLOW ADDING NEW VALUES
                tokenSeparators: [','], // Type comma to create new tag
                placeholder: "Select",
                allowClear: true
            });
        });
    </script>
@endpush
