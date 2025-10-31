</div> <!-- /container -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('.data-table').DataTable();

    // Handle the click event for the preview button
    $('.preview-btn').on('click', function() {
        var routerId = $(this).data('router-id');

        // Fetch the commands via AJAX
        $.ajax({
            url: 'preview_commands.php?router_id=' + routerId,
            method: 'GET',
            success: function(data) {
                var commandList = $('#commandList');
                commandList.empty();
                if (data.length > 0) {
                    data.forEach(function(command) {
                        commandList.append('<li><strong>' + command.description + ':</strong><br><code>' + command.command + '</code></li>');
                    });
                } else {
                    commandList.append('<li>No commands to apply.</li>');
                }
                $('#previewModal').modal('show');
            }
        });
    });
});
</script>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Command Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The following commands will be applied to the router:</p>
                <ul id="commandList"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
