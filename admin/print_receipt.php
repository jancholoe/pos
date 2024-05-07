<?php
include 'db_connect.php';

if(!isset($_GET['id']) || trim($_GET['id']) == '') {
    die('ID is required');
}
$order_id = $_GET['id'];

$qry = $conn->query("SELECT * FROM orders WHERE id = $order_id");

if(!$qry) {
    die('Failed to execute query: ' . $conn->error);
}

if($row = $qry->fetch_assoc()):
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Receipt</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; margin: 0; padding: 20px; font-size: 14px; }
        .receipt { width: 600px; padding: 20px; margin: auto; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .header img { max-width: 100px; }
        .footer { font-size: 12px; color: #777; }
        .content { margin-top: 20px; }
        .content p { margin: 5px 0; }
        .title { font-size: 22px; text-align: center; margin-top: 0; }
    </style>
</head>
<body onload="window.print();">
    <div class="receipt">
        <div class="header">
            <img src="logo.png" alt="Company Logo">
            <h1>Noemi Pizza</h1>
            <p>123 Business Address, City, State, 12345</p>
            <p>Phone: (123) 456-7890 | Email: info@company.com</p>
        </div>
        <h2 class="title">Order Receipt</h2>
        <div class="content">
            <p><strong>Order ID:</strong> <?php echo $order_id ?></p>
            <p><strong>Name:</strong> <?php echo $row['name'] ?></p>
            <p><strong>Address:</strong> <?php echo $row['address'] ?></p>
            <p><strong>Email:</strong> <?php echo $row['email'] ?></p>
            <p><strong>Mobile:</strong> <?php echo $row['mobile'] ?></p>
        </div>
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For more information, visit our website: <a href="http://www.companywebsite.com">www.companywebsite.com</a></p>
        </div>
    </div>
</body>
</html>
<?php 
else:
    echo "No data found for ID $order_id";
endif;
?>

