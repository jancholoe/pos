<div class="container-fluid">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>

							<th>#</th>
							<th>Name</th>
							<th>Address</th>
							<th>Email</th>
							<th>Mobile</th>
							<th>Status</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						include 'db_connect.php';
						$qry = $conn->query("SELECT * FROM orders");
						while ($row = $qry->fetch_assoc()) :
						?>
							<tr>
								<td><?php echo $i++ ?></td>
								<td><?php echo $row['name'] ?></td>
								<td><?php echo $row['address'] ?></td>
								<td><?php echo $row['email'] ?></td>
								<td><?php echo $row['mobile'] ?></td>
								<?php if ($row['status'] == 1) : ?>
									<td class="text-center"><span class="badge badge-success">Confirmed</span></td>
									<td>
										<button class="btn btn-sm btn-primary view_order" data-id="<?php echo $row['id'] ?>">View Order</button>
										<button class="btn btn-sm btn-info print_receipt" data-id="<?php echo $row['id'] ?>">Print Receipt</button>
									</td>
								<?php else : ?>
									<td class="text-center"><span class="badge badge-secondary">For Verification</span></td>
									<td>
										<button class="btn btn-sm btn-primary view_order" data-id="<?php echo $row['id'] ?>">View Order</button>
									</td>
								<?php endif; ?>
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