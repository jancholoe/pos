<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        include 'db_connect.php'; // Ensure this points to the correct database connection script
                        $qry = $conn->query("SELECT * FROM system_logs ORDER BY log_timestamp DESC");
                        while ($row = $qry->fetch_assoc()) :
                        ?>
                            <tr>
                                <td><?php echo $i++ ?></td>
                                <td><?php echo $row['user_id'] ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo $row['log_timestamp'] ?></td>
                                <td>
                                    <!-- Actions like view more details if needed -->
                                    <button class="btn btn-sm btn-primary view_log" data-id="<?php echo $row['log_id'] ?>">View Details</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>
    $('.print_receipt').click(function() {
        var orderId = $(this).attr('data-id');
        var printWindow = window.open('print_receipt.php?id=' + orderId, 'Print', 'left=200, top=200, width=400, height=600, toolbar=0, resizable=0');

        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
                setTimeout(function() {
                    printWindow.close();
                }, 100);
            }, 500);
        };
    });


    $('.view_order').click(function() {
        uni_modal('Order', 'view_order.php?id=' + $(this).attr('data-id'))
    })
    $('table').dataTable();
</script>