@extends('web.layout.app')
@section('page_name', 'Edit Tender')

@push('style')
@endpush

@section('content')

    <main class="content">
        <div class="container-fluid p-0">
            <div class="row mb-xl-3 mb-2">
                <div class="d-none d-sm-block col-auto">
                    <h3><strong>Edit Tender</strong></h3>
                </div>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('update_tender') }}" method="POST">
                            @csrf

                            <input type="hidden" name="id" value="{{ $tender_details->id }}">
                            <div class="row">
                                {{-- Tender No / ID --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Tender No/ID <span class="text-danger">*</span></label>
                                    <input type="text" name="tender_no" class="form-control form-control-lg border-1" value="{{ old('tender_id', $tender_details->tender_no) }}">
                                </div>

                                {{-- Project Name --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Project Name <span class="text-danger">*</span></label>
                                    <input type="text" name="project_name" class="form-control form-control-lg border-1"
                                        value="{{ old('project_name', $tender_details->project_name) }}">
                                </div>

                                {{-- Contract Type --}}
                                <div class="col-md-3 mb-3">
                                    <label class="d-flex form-label fw-bold">Contract <span class="text-danger">*</span></label>

                                    <div class="d-flex gap-4">
                                        <div class="d-flex align-item-center">
                                            <input class="form-check-input fs-3 me-2 mt-0" type="radio" name="contract_type" value="Government"
                                                {{ old('contractor', $tender_details->contractor) == 'Government' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-5">Government</label>
                                        </div>

                                        <div class="d-flex align-item-center">
                                            <input class="form-check-input fs-3 me-2 mt-0" type="radio" name="contract_type" value="Private"
                                                {{ old('contractor', $tender_details->contractor) == 'Private' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-5">Private</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Authority / Client --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Authority/Client <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-lg border-1" name="client" required>
                                        <option value="" disabled>Select</option>
                                        @foreach ($nick_name as $nl)
                                            <option value="{{ $nl->authority }}" {{ $tender_details->authority == $nl->authority ? 'selected' : '' }}>
                                                {{ $nl->nick_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Scheme --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Scheme <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-lg border-1" name="scheme">
                                        <option value="" disabled>Select</option>

                                        @foreach ($schemes as $sc)
                                            <option value="{{ $sc->scheme }}" {{ $tender_details->scheme == $sc->scheme ? 'selected' : '' }}>
                                                {{ $sc->scheme }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                                {{-- Year End Date --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Year End Date <span class="text-danger">*</span></label>

                                    @php
                                        $currentYear = date('Y');
                                    @endphp

                                    <select name="year_range" class="form-select">
                                        @for ($y = $currentYear; $y <= $currentYear + 50; $y++)
                                            <option value="{{ $y }}-{{ $y + 1 }}"
                                                {{ old('year_range', $tender_details->year_range) == "$y-" . ($y + 1) ? 'selected' : '' }}>
                                                {{ $y }} - {{ $y + 1 }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                {{-- Location --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg border-1" name="location" value="{{ old('location', $tender_details->location) }}">
                                </div>

                                {{-- Status --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select class="form-select border-1" name="status">
                                        <option value="" disabled>Select</option>

                                        <option value="In Bidding" {{ old('status', $tender_details->status) == 'In Bidding' ? 'selected' : '' }}>In Bidding</option>
                                        <option value="Dropped" {{ old('status', $tender_details->status) == 'Dropped' ? 'selected' : '' }}>Dropped</option>
                                        <option value="In Progress" {{ old('status', $tender_details->status) == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="On Hold" {{ old('status', $tender_details->status) == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="Completed" {{ old('status', $tender_details->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Closed" {{ old('status', $tender_details->status) == 'Closed' ? 'selected' : '' }}>Closed</option>
                                        <option value="Not Started" {{ old('status', $tender_details->status) == 'Not Started' ? 'selected' : '' }}>Not Started</option>
                                        <option value="Dummy" {{ old('status', $tender_details->status) == 'Dummy' ? 'selected' : '' }}>Dummy</option>
                                        <option value="Active" {{ old('status', $tender_details->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    </select>
                                </div>

                                {{-- Reminder Date --}}
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Reminder Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-lg border-1" name="reminder_date"
                                        value="{{ old('reminder_date', $tender_details->remainder_date) }}">
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-2 mt-2">
                                    <button class="btn btn-primary w-100" id="submit_btn">Update</button>
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
@endpush
