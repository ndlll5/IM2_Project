<?php
include '../db_connect.php';
include 'navbar.php';

// Fetch item details from the database
function getItemDetails($conn, $itemId) {
    $query = $conn->prepare("SELECT * FROM product_item WHERE item_id = ?");
    if ($query === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $query->bind_param("i", $itemId);
    $query->execute();
    $result = $query->get_result();
    return $result->fetch_assoc();
}

// Calculate total amount in the cart
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalAmount = 0;
foreach ($cartItems as $item) {
    $itemDetails = getItemDetails($conn, $item['item_id']);
    $totalAmount += $itemDetails['price'] * $item['quantity'];
}

// Handle checkout process
if (isset($_POST['checkout'])) {
    $userId = $_SESSION['user_id']; // Replace with actual session variable for user ID

    // Insert order into shop_order table
    $paymentMethodId = $_POST['payment_method'];
    $fulfillmentMethodId = $_POST['fulfillment_method'];
    $orderStatus = 'Pending'; // Example initial status
    $phoneNumber = $_POST['phone_number'];
    $shippingAddress = $fulfillmentMethodId == 2 ? $_POST['shipping_address'] : ''; // Assuming 2 is for delivery

    $insertOrderQuery = $conn->prepare("INSERT INTO shop_order (user_id, order_date, payment_method_id, fulfillment_method_id, order_status, order_total, shipping_address, phone_number)
                                       VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)");
    if ($insertOrderQuery === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $insertOrderQuery->bind_param("iiisiss", $userId, $paymentMethodId, $fulfillmentMethodId, $orderStatus, $totalAmount, $shippingAddress, $phoneNumber);
    $success = $insertOrderQuery->execute();

    if ($success) {
        $orderId = $insertOrderQuery->insert_id;

        // Insert order line items into orderline table
        foreach ($cartItems as $item) {
            $itemDetails = getItemDetails($conn, $item['item_id']);
            $insertOrderlineQuery = $conn->prepare("INSERT INTO orderline (product_item_id, order_id, qty, subtotal) VALUES (?, ?, ?, ?)");
            if ($insertOrderlineQuery === false) {
                die("Error in SQL query: " . $conn->error);
            }
            $subtotal = $itemDetails['price'] * $item['quantity'];
            $insertOrderlineQuery->bind_param("iiid", $item['item_id'], $orderId, $item['quantity'], $subtotal);
            $insertOrderlineSuccess = $insertOrderlineQuery->execute();
            if (!$insertOrderlineSuccess) {
                echo "Error inserting order line: " . $insertOrderlineQuery->error;
            }
        }

        // Clear the cart after successful checkout
        $_SESSION['cart'] = [];
        header("Location: success.php");
        exit();
    } else {
        echo "Error: " . $insertOrderQuery->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motoracer Cart</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Shopping Cart</h2>
        <table class="table table-dark table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($cartItems as $item) {
                    $itemDetails = getItemDetails($conn, $item['item_id']);
                    $itemTotal = $itemDetails['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($itemDetails['name']); ?></td>
                        <td>₱<?php echo htmlspecialchars(number_format($itemDetails['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>₱<?php echo htmlspecialchars(number_format($itemTotal, 2)); ?></td>
                        <td><button class="btn btn-danger btn-sm remove-item-btn" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">Remove</button></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <div class="float-right">
            <h3>Total: ₱<?php echo htmlspecialchars(number_format($totalAmount, 2)); ?></h3>
            <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#checkoutModal">Checkout</button>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Checkout Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fulfillment_method">Fulfillment Method:</label>
                            <select class="form-control" id="fulfillment_method" name="fulfillment_method" required>
                                <option value="1">Pickup</option>
                                <option value="2">Delivery</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number:</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                        <div class="form-group" id="shippingAddressField" style="display:none;">
                            <label for="shipping_address">Shipping Address:</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method:</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="1">Credit Card</option>
                                <option value="2">PayPal</option>
                                <option value="3">Cash on Delivery</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="checkout" class="btn btn-success">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#fulfillment_method').change(function() {
                if ($(this).val() == '2') {
                    $('#shippingAddressField').show();
                    $('#shipping_address').prop('required', true);
                } else {
                    $('#shippingAddressField').hide();
                    $('#shipping_address').prop('required', false);
                }
            });

            $('.remove-item-btn').click(function() {
                var itemId = $(this).data('item-id');
                $.post('remove_from_cart.php', { item_id: itemId }, function(response) {
                    location.reload();
                });
            });
        });
    </script>
</body>
</html>
