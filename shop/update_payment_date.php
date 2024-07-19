<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderId = $_POST['shop_order_id'];
    $paymentStatus = $_POST['payment_status'];
    $paymentDate = $_POST['payment_date'];

    // Debugging: Log received data
    error_log("Order ID: $orderId");
    error_log("Payment Status: $paymentStatus");
    error_log("Payment Date: $paymentDate");

    // If the payment status is 'Paid', set the payment date
    if ($paymentStatus == 'Paid') {
        $stmt = $conn->prepare("UPDATE shop_order SET payment_status = ?, payment_date = ? WHERE shop_order_id = ?");
        $stmt->bind_param("ssi", $paymentStatus, $paymentDate, $orderId);
    } else {
        $stmt = $conn->prepare("UPDATE shop_order SET payment_status = ? WHERE shop_order_id = ?");
        $stmt->bind_param("si", $paymentStatus, $orderId);
    }

    if ($stmt->execute()) {
        echo "Payment status updated successfully.";
    } else {
        echo "Error updating payment status: " . $stmt->error;
    }

    $stmt->close();
}
?>
