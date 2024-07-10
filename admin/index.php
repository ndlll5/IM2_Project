<?php 
include '../db_connect.php';

// Query to count pending orders
$sql_pending = "SELECT COUNT(*) as pending_orders FROM shop_order WHERE order_status = 'pending'";
$result_pending = $conn->query($sql_pending);
$row_pending = $result_pending->fetch_assoc();
$pending_orders = $row_pending['pending_orders'];

// Query to count cancelled orders
$sql_cancelled = "SELECT COUNT(*) as cancelled_orders FROM shop_order WHERE order_status = 'cancelled'";
$result_cancelled = $conn->query($sql_cancelled);
$row_cancelled = $result_cancelled->fetch_assoc();
$cancelled_orders = $row_cancelled['cancelled_orders'];

// Query to count completed orders
$sql_completed = "SELECT COUNT(*) as completed_orders FROM shop_order WHERE order_status = 'completed'";
$result_completed = $conn->query($sql_completed);
$row_completed = $result_completed->fetch_assoc();
$completed_orders = $row_completed['completed_orders'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>

        .card-custom .card-body {
            background-color: #FFC107;
            color: #343A40;
            border: none;
        }

    </style>
</head>

<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <!-- <div class="container">
        <h1 class="mt-5">Admin Panel</h1>
        <ul class="list-group mt-3">
            <li class="list-group-item bg-secondary">
                <a href="products.php" class="text-white">Manage Products</a>
            </li>
            <li class="list-group-item bg-secondary">
                <a href="admin/orders.php" class="text-white">Manage Orders</a>
            </li>
            <li class="list-group-item bg-secondary">
                <a href="admin/payments.php" class="text-white">Manage Payments</a>
            </li>
            <li class="list-group-item bg-secondary">
                <a href="admin/users.php" class="text-white">Manage Users</a>
            </li>
        </ul>
    </div> -->

<div class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="mt-2">Dashboard</h1>
            </div>
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-custom border-0">
                        <div class="card-body rounded-lg">
                            <h5 class="card-title">Pending Orders</h5>
                            <h3 class="card-text"><?php echo $pending_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-custom border-0">
                        <div class="card-body rounded-lg">
                            <h5 class="card-title">Cancelled Orders</h5>
                            <h3 class="card-text"><?php echo $cancelled_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card card-custom border-0">
                        <div class="card-body rounded-lg">
                            <h5 class="card-title">Completed Orders</h5>
                            <h3 class="card-text"><?php echo $completed_orders; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

    
    <?php include 'footer.php'; ?>
</body>
</html>