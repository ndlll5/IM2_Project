<?php
include '../db_connect.php';

// Fetch all orders
$sql = "SELECT * FROM shop_order";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        .modal-content {
        background-color: #343a40; /* Dark background color */
        color: white; /* White text color */
        }
    </style>
</head>
<body class = "bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Manage Orders</h1>
        <table class="table table-striped text-white">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['shop_order_id']; ?></td>
                    <td><?php echo $order['user_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td>₱<?php echo number_format($order['order_total'], 2); ?></td>
                    <td><?php echo $order['order_status']; ?></td>
                    <td><a href="order_details.php?shop_order_id=<?php echo $order['shop_order_id']; ?>" class="btn btn-primary btn-sm">View Details</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
