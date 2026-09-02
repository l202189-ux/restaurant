<?php
include 'db.php';
session_start();

// Admin security check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: admin_orders.php");
    exit();
}

$order_id = (int)$_GET['order_id'];

// 1. Fetch General Order & Customer Info
$order_query = $conn->query("
    SELECT o.*, u.full_name AS customer_name, u.email, ca.address_text, ca.city 
    FROM Orders o 
    JOIN Users u ON o.customer_id = u.user_id 
    LEFT JOIN customeraddresses ca ON o.address_id = ca.address_id
    WHERE o.order_id = $order_id
");
$order = $order_query->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// 2. Fetch specific items in this order
$items_query = $conn->query("
    SELECT oi.*, d.dish_name 
    FROM OrderItems oi 
    JOIN Dishes d ON oi.dish_id = d.dish_id 
    WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details #<?= $order_id ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .details-container { max-width: 800px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table th { text-align: left; background: #f4f4f4; padding: 10px; }
        .item-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .grand-total { text-align: right; font-size: 1.5rem; margin-top: 20px; color: #3f58b4; }
    </style>
</head>
<body>

<div class="details-container">
    <a href="admin_orders.php">← Back to Orders</a>
    <h1>Order Details #<?= $order_id ?></h1>

    <div class="info-grid">
        <div>
            <h3>Customer Info</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Status:</strong> <?= ucfirst($order['order_status']) ?></p>
        </div>
        <div>
            <h3>Delivery Address</h3>
            <p><?= htmlspecialchars($order['address_text'] ?? 'N/A') ?><br>
               <?= htmlspecialchars($order['city'] ?? '') ?></p>
            <p><strong>Ordered At:</strong> <?= $order['order_timestamp'] ?></p>
        </div>
    </div>

    <h3>Purchased Items</h3>
    <table class="item-table">
        <thead>
            <tr>
                <th>Dish Name</th>
                <th>Qty</th>
                <th>Base Price</th>
                <th>Discount</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $calculated_total = 0;
            while($item = $items_query->fetch_assoc()): 
                $calculated_total += $item['line_total'];
            ?>
            <tr>
                <td><?= htmlspecialchars($item['dish_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                <td style="color: red;">-$<?= number_format($item['discount_applied'], 2) ?></td>
                <td><strong>$<?= number_format($item['line_total'], 2) ?></strong></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="grand-total">
        Grand Total: $<?= number_format($calculated_total, 2) ?>
    </div>
</div>

</body>
</html>