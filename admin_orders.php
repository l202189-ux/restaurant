<?php
include 'db.php';
session_start();

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all orders with the calculated grand total
// REMOVED: o.delivery_code from the SELECT list
$orders = $conn->query("
    SELECT o.order_id, u.full_name AS customer, r.full_name AS rider,
           o.order_status, o.order_timestamp, o.delivery_timestamp,
           (SELECT SUM(line_total) FROM orderitems WHERE order_id = o.order_id) AS grand_total
    FROM Orders o
    LEFT JOIN Users u ON o.customer_id = u.user_id
    LEFT JOIN Users r ON o.rider_id = r.user_id
    ORDER BY o.order_timestamp DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Orders</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 style="text-align:center;">Admin Orders Management</h1>
    <nav style="text-align:center; margin-bottom: 20px;">
        <a href="admin_dashboard.php">Dashboard</a> | 
        <a href="logout.php">Logout</a>
    </nav>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Rider</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Ordered At</th>
            <th>Details</th>
        </tr>
        <?php while($order = $orders->fetch_assoc()): ?>
        <tr>
            <td>#<?= $order['order_id'] ?></td>
            <td><?= htmlspecialchars($order['customer']) ?></td>
            <td><?= htmlspecialchars($order['rider'] ?? 'Unassigned') ?></td>
            <td><strong>$<?= number_format($order['grand_total'] ?? 0, 2) ?></strong></td>
            <td>
                <span class="status-badge <?= strtolower($order['order_status']) ?>">
                    <?= ucfirst($order['order_status']) ?>
                </span>
            </td>
            <td><?= date('M d, Y H:i', strtotime($order['order_timestamp'])) ?></td>
            <td>
                <a href="admin_orderdetails.php?order_id=<?= $order['order_id'] ?>" class="button">View Details</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
