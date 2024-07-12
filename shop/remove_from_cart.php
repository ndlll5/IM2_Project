<?php
session_start();
include '../db_connect.php';

if (isset($_POST['item_id'])) {
    $itemId = filter_var($_POST['item_id'], FILTER_SANITIZE_NUMBER_INT);

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['item_id'] == $itemId) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                echo "Item removed from cart.";
                exit;
            }
        }
    }
}

echo "Item not found in cart.";
?>
