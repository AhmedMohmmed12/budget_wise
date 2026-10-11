@once
@push('scripts')
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('.delete-entry');
    if (!button || !confirm('Delete this entry? You can bring it back from Recently deleted.')) return;

    button.disabled = true;
    try {
        const response = await fetch(button.dataset.url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        if (!response.ok) throw new Error();
        location.reload();
    } catch {
        button.disabled = false;
        alert("Couldn't delete that entry. Try again.");
    }
});
</script>
@endpush
@endonce
