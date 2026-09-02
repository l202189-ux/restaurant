<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

if(!isset($_GET['order_id'])){
    header("Location: customers_menu.php");
    exit();
}

// Sanitize input to prevent basic SQL injection 
$order_id = (int)$_GET['order_id'];
// Multi-Table JOIN to aggregate order details and other details 
$order_res = $conn->query("
    SELECT oi.quantity, oi.unit_price, oi.discount_applied, oi.line_total, d.dish_name
    FROM OrderItems oi
    JOIN Dishes d ON oi.dish_id = d.dish_id
    JOIN Orders o ON oi.order_id = o.order_id
    WHERE oi.order_id = $order_id AND o.customer_id = $customer_id
");
// Error handling occurs 
if(!$order_res || $order_res->num_rows == 0){
    die("Order not found.");
}
// Fetches all matching rows 
$items = $order_res->fetch_all(MYSQLI_ASSOC);
//Data aggregation occurs 
$total_amount = 0;
foreach($items as $item){
    $total_amount += $item['line_total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Confirmation</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a href="customer_dashboard.php">Home</a>
    <a href="customers_menu.php">Menu</a>
    <a href="customer_orders.php">My Orders</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="profile-box">
    <h1>Order Placed Successfully!</h1>
    <p>Order ID: <?= $order_id ?></p>

    <h2>Items Ordered</h2>
    <table class="orders-table">
        <tr>
            <th>Dish</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Discount</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['dish_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['unit_price'],2) ?></td>
                <td>$<?= number_format($item['discount_applied'],2) ?></td>
                <td>$<?= number_format($item['line_total'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="order-total">
            <td colspan="4">Total</td>
            <td>$<?= number_format($total_amount,2) ?></td>
        </tr>
    </table>

    <p><a href="customer_orders.php">View All My Orders</a></p>
</div>

</body>
</html>

