  {{-- Overall Expense --}}
  <div class="card">
      <div class="card-body">
          <div class="row">
              <div class="col-md-6 border-rs">
                  <div class="row justify-content-between align-items-center">
                      <h5 class="col-md-6 card-title text-dark">Expense Chart</h5>

                      <div>
                          <div id="expense_chart" style="height:350px;"></div>
                      </div>
                  </div>
              </div>

              {{-- Overall Expense Category --}}
              <div class="col-md-6">
                  <div class="card-head d-flex justify-content-between align-items-center pb-2">
                      <h5 class="col-md-6 card-title text-dark mb-0">Category</h5>

                      <a data-bs-toggle="modal" data-bs-target="#exampleModalToggleExpense"><img src="{{ asset('assets/images/icons/Edit.png') }}" width="20px"
                              alt=""></a>
                  </div>
                  <ul class="row justify-content-between align-items-center m-0 p-0">
                      @foreach ($exp_values as $cat => $total)
                          <li class="d-flex align-items-center justify-content-between br-2 p-3">
                              <div class="d-flex align-items-center">
                                  <img src="{{ asset('assets/images/icons/emd-base.png') }}" width="35px" alt="">
                                  <p class="fs-5 mb-0 ms-3">{{ $cat }}</p>
                              </div>
                              <h4 class="mb-0">&#8377; <span>{{ $total }}</span></h4>
                          </li>
                      @endforeach
                  </ul>
              </div>
          </div>
      </div>
  </div>

  <!-- expense Modal -->
  <div class="modal fade" id="exampleModalToggleExpense" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
      <div class="modal-dialog modal-xs modal-dialog-centered">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title fw-bold">Add Expense</h4>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <form action="{{ route('add_exp') }}" method="post">
                      @csrf
                      <input type="hidden" name="mc_id" value={{ $tender_prof->mc_id }}>
                      <input type="hidden" name="tender_id" value={{ $tender_prof->id }}>
                      <div class="mb-3">
                          <label class="form-label">Expense Category</label>
                          <select name="exp_cat" class="form-select" id="">
                              <option value="" selected disabled>Select</option>
                              <option value="Labour">Labour</option>
                              <option value="Materials">Materials</option>
                              <option value="Equipments">Equipments</option>
                              <option value="Admin Expenses">Admin Expenses</option>
                              <option value="Others">Others</option>
                          </select>
                      </div>
                      <div class="mb-3">
                          <label class="form-label">Amount</label>
                          <input type="number" name="amount" class="form-control" min="0">
                      </div>
                      <div class="d-flex gap-2">
                          <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                          <button class="btn btn-primary w-50" data-bs-target="#verifyModal" data-bs-toggle="modal">Save</button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </div>
