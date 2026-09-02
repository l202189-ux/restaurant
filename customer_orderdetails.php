<?php
session_start();
include 'db.php';

/* ============================= AUTH: CUSTOMER ONLY ============================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

/* ============================= VALIDATE ORDER ID ============================= */
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: customer_orders.php");
    exit();
}

$order_id = (int) $_GET['order_id'];

/* ============================= FETCH ORDER ============================= */
$sql_order = "
SELECT o.*, ca.address_text, ca.city, u.full_name AS rider_name
FROM Orders o
JOIN CustomerAddresses ca ON o.address_id = ca.address_id
LEFT JOIN Users u ON o.rider_id = u.user_id
WHERE o.order_id = $order_id AND o.customer_id = $customer_id
";

$result_order = $conn->query($sql_order);
if (!$result_order || $result_order->num_rows === 0) {
    die('Order not found or access denied.');
}

$order = $result_order->fetch_assoc();

/* ============================= CUSTOMER CONFIRM DELIVERY ============================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_code_input'])) {
    if ((int)$order['rider_code_verified'] !== 1) {
        $error = "Rider has not confirmed delivery yet.";
    } elseif ($_POST['customer_code_input'] == $order['customer_code']) {
        $conn->query("UPDATE Orders SET customer_code_verified = 1 WHERE order_id = $order_id");
        $success = "Delivery confirmed successfully.";
        // Refresh order
        $result_order = $conn->query($sql_order);
        $order = $result_order->fetch_assoc();
    } else {
        $error = "Incorrect delivery code.";
    }
}

/* ============================= AUTO MARK AS DELIVERED ============================= */
if (
    (int)$order['rider_code_verified'] === 1 &&
    (int)$order['customer_code_verified'] === 1 &&
    $order['order_status'] !== 'Delivered'
) {
    $conn->query("UPDATE Orders SET order_status = 'Delivered', delivery_timestamp = NOW() WHERE order_id = $order_id");
    $order['order_status'] = 'Delivered';
}

/* ============================= FETCH ORDER ITEMS ============================= */
$sql_items = "
SELECT oi.quantity, oi.unit_price, oi.discount_applied, oi.line_total, d.dish_name
FROM OrderItems oi
JOIN Dishes d ON oi.dish_id = d.dish_id
WHERE oi.order_id = $order_id
";
$result_items = $conn->query($sql_items);

$items = [];
$total_amount = 0;
while ($item = $result_items->fetch_assoc()) {
    $items[] = $item;
    $total_amount += $item['line_total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order #<?= $order_id ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
    <a href="customer_dashboard.php">Dashboard</a>
    <a href="customer_orders.php">My Orders</a>
    <a href="logout.php">Logout</a>
</nav>
<div class="profile-box">
    <h1>Order #<?= $order_id ?></h1>
    <?php if (isset($success)): ?>
        <div class="success-msg"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>
    <p><strong>Status:</strong> <?= $order['order_status'] ?></p>
    <p><strong>Order Date:</strong> <?= date('d M Y, H:i', strtotime($order['order_timestamp'])) ?></p>
    <?php if (!empty($order['rider_name'])): ?>
        <p><strong>Rider:</strong> <?= htmlspecialchars($order['rider_name']) ?></p>
    <?php endif; ?>
    <?php if (!empty($order['delivery_timestamp'])): ?>
        <p><strong>Delivered At:</strong> <?= date('d M Y, H:i', strtotime($order['delivery_timestamp'])) ?></p>
    <?php endif; ?>
    <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order['address_text'] . ', ' . $order['city']) ?></p>

    <!-- ============================= DELIVERY CODE + CONFIRMATION ============================= -->
    <?php if (!empty($order['customer_code'])): ?>
        <div style="text-align:center; margin:25px 0; padding:20px; border:2px dashed #000;">
            <p><strong>Your Delivery Code</strong></p>
            <p style="font-size:36px; font-weight:bold; letter-spacing:6px;">
                <?= htmlspecialchars($order['customer_code']) ?>
            </p>
            <?php if ((int)$order['customer_code_verified'] === 1): ?>
                <p style="color:green; font-weight:bold;">Delivery confirmed successfully</p>
            <?php elseif ((int)$order['rider_code_verified'] === 1): ?>
                <p style="font-size:13px; color:gray;">After receiving your order, enter the code below to confirm delivery.</p>
                <form method="POST" style="margin-top:15px;">
                    <input type="text" name="customer_code_input" placeholder="Enter delivery code" required style="padding:8px; text-align:center;">
                    <br><br>
                    <button type="submit">Confirm Delivery</button>
                </form>
            <?php else: ?>
                <p style="font-size:13px; color:gray;">Waiting for rider to confirm delivery.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h2>Items Ordered</h2>
    <table class="orders-table">
        <tr>
            <th>Dish</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Discount</th>
            <th>Total</th>
        </tr>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['dish_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['unit_price'], 2) ?></td>
                <td><?= number_format($item['discount_applied'], 2) ?></td>
                <td><?= number_format($item['line_total'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="4"><strong>Grand Total</strong></td>
            <td><strong><?= number_format($total_amount, 2) ?></strong></td>
        </tr>
    </table>
</div>
</body>
</html>
