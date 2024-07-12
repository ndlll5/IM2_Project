<?php
session_start();
include 'header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $contact_number = filter_var($_POST['contact_number'], FILTER_SANITIZE_STRING);
    $fulfillment_method = filter_var($_POST['fulfillment_method'], FILTER_SANITIZE_NUMBER_INT);
    $payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_NUMBER_INT);

    $order_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $order_total += $item['price'] * $item['quantity'];
    }

    $order_date = date('Y-m-d');
    $order_status = 'Pending';

    $sql = "INSERT INTO shop_order (user_id, order_date, payment_method_id, fulfillment_method_id, order_status, order_total, shipping_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiiids", $user_id, $order_date, $payment_method, $fulfillment_method, $order_status, $order_total, $address);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        foreach ($_SESSION['cart'] as $item) {
            $product_item_id = $item['item_id'];
            $quantity = $item['quantity'];
            $subtotal = $item['price'] * $quantity;

            $sql = "INSERT INTO orderline (product_item_id, order_id, qty, subtotal) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $product_item_id, $order_id, $quantity, $subtotal);
            $stmt->execute();
        }

        unset($_SESSION['cart']);
        echo "Order placed successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$sql = "SELECT * FROM fulfillment_method";
$fulfillment_methods = $conn->query($sql);

$sql = "SELECT * FROM payment_type";
$payment_methods = $conn->query($sql);
?>

<div class="container mt-5">
    <h2>Checkout</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <input type="text" class="form-control" id="address" name="address" required>
        </div>
        <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="text" class="form-control" id="contact_number" name="contact_number" required>
        </div>
        <div class="mb-3">
            <label for="fulfillment_method" class="form-label">Fulfillment Method</label>
            <select class="form-control" id="fulfillment_method" name="fulfillment_method" required>
                <?php while ($row = $fulfillment_methods->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select class="form-control" id="payment_method" name="payment_method" required>
                <?php while ($row = $payment_methods->fetch_assoc()): ?>
                    <option value="<?php echo $row['payment_type_id']; ?>"><?php echo $row['value']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Place Order</button>
    </form>
</div>

<?php
include 'footer.php';
?>
