<?php
session_start();
include 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])){
    $customer_id = $_SESSION['user_id'];
    $address_id = (int)$_POST['address_id'];
    $quantities = $_POST['qty'];

    // 1. Create Order
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_status, address_id, order_timestamp) VALUES (?, 'Pending', ?, NOW())");
    $stmt->bind_param("ii", $customer_id, $address_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // 2. Loop through and save items
    foreach($quantities as $dish_id => $qty){
        if($qty > 0){
            $dish_id = (int)$dish_id;
            
            // Recalculate based on DB values for security
            $dish_res = $conn->query("
                SELECT d.price, dis.discount_type, dis.discount_value 
                FROM Dishes d 
                LEFT JOIN Discounts dis ON d.dish_id = dis.dish_id 
                WHERE d.dish_id=$dish_id
            ");
            $dish = $dish_res->fetch_assoc();
            
            $unit_price = $dish['price'];
            $discount_applied = 0;
            if(!empty($dish['discount_type'])){
                if ($dish['discount_type'] === 'percentage') {
                    $discount_applied = $unit_price * ($dish['discount_value']/100);
                } else {
                    $discount_applied = $dish['discount_value'];
                }
            }

            $line_total = ($unit_price - $discount_applied) * $qty;

            $stmt_item = $conn->prepare("INSERT INTO orderitems (order_id, dish_id, quantity, unit_price, discount_applied, line_total) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_item->bind_param("iiiddd", $order_id, $dish_id, $qty, $unit_price, $discount_applied, $line_total);
            $stmt_item->execute();
        }
    }

    // Success! Redirect to confirmation
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
} else {
    header("Location: customers_menu.php");
    exit();
}
?>