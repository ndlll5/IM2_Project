<?php
include '../db_connect.php';

// Ensure shop_order_id is provided and valid
if (!isset($_GET['shop_order_id']) || !is_numeric($_GET['shop_order_id'])) {
    header("Location: orders.php");
    exit();
}

$orderId = $_GET['shop_order_id'];

// Fetch order details including user information
$sql = "SELECT so.*, u.firstname, u.lastname, u.username 
        FROM shop_order so 
        JOIN user u ON so.user_id = u.user_id 
        WHERE so.shop_order_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
} else {
    echo "Order not found";
    exit();
}

// Fetch order items
$sqlItems = "SELECT ol.*, pi.name AS product_name 
             FROM orderline ol 
             JOIN product_item pi ON ol.product_item_id = pi.item_id 
             WHERE ol.order_id = ?";
$stmtItems = $conn->prepare($sqlItems);

if (!$stmtItems) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmtItems->bind_param("i", $orderId);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();

if ($resultItems->num_rows > 0) {
    $orderItems = $resultItems->fetch_all(MYSQLI_ASSOC);
} else {
    $orderItems = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Order Details</h1>
        <h3>Order ID: <?php echo $order['shop_order_id']; ?></h3>
        <p><strong>User Name:</strong> <?php echo $order['firstname'] . " " . $order['lastname'] . " (" . $order['username'] . ")"; ?></p>
        <p><strong>Order Date:</strong> <?php echo $order['order_date']; ?></p>
        <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['order_total'], 2); ?></p>
        <p><strong>Status:</strong> <?php echo $order['order_status']; ?></p>

        <h3>Order Items</h3>
        <table class="table table-striped text-white">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td><?php echo $item['product_item_id']; ?></td>
                    <td><?php echo $item['product_name']; ?></td>
                    <td><?php echo $item['qty']; ?></td>
                    <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="orders.php" class="btn btn-primary mb-3">Back to Orders</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
