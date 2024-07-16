<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['shop_order_id'], $_POST['order_status'])) {
        $orderId = $_POST['shop_order_id'];
        $orderStatus = $_POST['order_status'];

        $sql = "UPDATE shop_order SET order_status = ? WHERE shop_order_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            exit();
        }

        $stmt->bind_param("si", $orderStatus, $orderId);
        if ($stmt->execute()) {
            header("Location: order_details.php?shop_order_id=" . $orderId);
        } else {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
}
?>
