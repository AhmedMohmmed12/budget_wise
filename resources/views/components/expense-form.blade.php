<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Expense</h5>
    </div>
    <div class="card-body">
        <form id="expenseForm">
            @csrf
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                </div>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select category</option>
                    <option value="Restaurants">Restaurants</option>
                    <option value="Housing">Housing</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Shopping">Shopping</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Others">Others</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label for="note" class="form-label">Note (Optional)</label>
                <textarea class="form-control" id="note" name="note" rows="2" placeholder="Add a note..."></textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Add Expense
                </button>
            </div>
        </form>

        <div class="alert alert-success mt-3 d-none" id="successMessage">
            <i class="fas fa-check-circle me-2"></i>Expense added successfully!
        </div>

        <div class="alert alert-danger mt-3 d-none" id="errorMessage">
            <i class="fas fa-exclamation-circle me-2"></i><span id="errorText"></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Wait for jQuery to be available
function waitForJQuery(callback) {
    if (typeof $ !== 'undefined') {
        callback();
    } else {
        setTimeout(() => waitForJQuery(callback), 100);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    waitForJQuery(function() {
        $('#expenseForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#successMessage, #errorMessage').addClass('d-none');
        
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');
        
        $.ajax({
            url: '{{ route("expenses.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#successMessage').removeClass('d-none');
                    $('#expenseForm')[0].reset();
                    $('#date').val('{{ date("Y-m-d") }}');
                    
                    // Reload page after 1 second to refresh budget summary
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        const input = $(`#${key}`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[key][0]);
                    });
                } else {
                    $('#errorText').text('Failed to add expense. Please try again.');
                    $('#errorMessage').removeClass('d-none');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Add Expense');
            }
        });
        });
    });
});
</script>
@endpush

