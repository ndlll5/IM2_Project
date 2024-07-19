<?php
include '../db_connect.php';

// Pagination and search settings
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch payments for display with search functionality
$payment_query = "SELECT shop_order.*, CONCAT(user.firstname, ' ', user.lastname) AS full_name, username 
                  FROM shop_order 
                  JOIN user ON shop_order.user_id = user.user_id 
                  WHERE user.username LIKE '%$search%' 
                  OR CONCAT(user.firstname, ' ', user.lastname) LIKE '%$search%' 
                  OR shop_order.payment_status LIKE '%$search%'
                  LIMIT $limit OFFSET $offset";
$payment_result = mysqli_query($conn, $payment_query);
if ($payment_result) {
    $payments = mysqli_fetch_all($payment_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching payments: " . mysqli_error($conn);
    $payments = [];
}

// Fetch total number of payments for pagination
$total_query = "SELECT COUNT(*) as total 
                FROM shop_order 
                JOIN user ON shop_order.user_id = user.user_id 
                WHERE user.username LIKE '%$search%' 
                OR CONCAT(user.firstname, ' ', user.lastname) LIKE '%$search%' 
                OR shop_order.payment_status LIKE '%$search%'";
$total_result = mysqli_query($conn, $total_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .modal-content {
            background-color: #343a40; /* Dark background color */
            color: white; /* White text color */
        }

        .bg-pagi{
            background-color: #333333;
        }
    </style>
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Payments</h1>

        <div class="row">
            <div class="col mb-3">
                <!-- Search form -->
                <form class="form-inline" method="GET" action="payment.php">
                    <input class="form-control mr-2" type="search" name="search" placeholder="Search by name" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-light btn-sm" type="submit">Search</button>
                </form>
            </div>
        </div>

        <!-- Table to display existing payments -->
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Payment Date</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo $payment['shop_order_id']; ?></td>
                    <td><?php echo $payment['username']; ?></td>
                    <td><?php echo $payment['full_name']; ?></td>
                    <td><?php echo $payment['payment_date']; ?></td>
                    <td>₱<?php echo number_format($payment['order_total'], 2); ?></td>
                    <td>
                        <form method="POST" action="update_payment_status.php" class="d-inline">
                            <input type="hidden" name="shop_order_id" value="<?php echo $payment['shop_order_id']; ?>">
                            <select name="payment_status" class="custom-select custom-select-sm bg-secondary text-white" onchange="this.form.submit()">
                                <option value="Paid" <?php echo $payment['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="Pending" <?php echo $payment['payment_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Failed" <?php echo $payment['payment_status'] == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="Cancelled" <?php echo $payment['payment_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Refunded" <?php echo $payment['payment_status'] == 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </form>
                    </td>
                    <td><a href="payment_details.php?shop_order_id=<?php echo $payment['shop_order_id']; ?>" class="btn btn-primary btn-sm">View Details</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination controls -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="payment.php?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="payment.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="payment.php?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
