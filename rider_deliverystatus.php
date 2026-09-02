<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'rider') {
    header("Location: login.php");
    exit();
}

$rider_id = (int)$_SESSION['user_id'];

/* --- CHECK FOR CURRENT ACTIVE ORDER --- */
// Logic: An order is active if it's 'In Progress'
$active_res = $conn->query("
    SELECT order_id FROM Orders 
    WHERE rider_id = $rider_id 
    AND order_status = 'Pending' 
    LIMIT 1
");
$has_active = ($active_res && $active_res->num_rows > 0);
$active_order_id = $has_active ? $active_res->fetch_assoc()['order_id'] : null;

/* --- FETCH DELIVERY HISTORY --- */
$history_res = $conn->query("
    SELECT o.order_id, o.order_status, o.order_timestamp, u.full_name, 
           CONCAT(a.address_text, ', ', a.city) as address
    FROM Orders o
    JOIN Users u ON o.customer_id = u.user_id
    JOIN CustomerAddresses a ON o.address_id = a.address_id
    WHERE o.rider_id = $rider_id
    ORDER BY o.order_timestamp DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delivery History</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .active-banner { background: #FFF3CD; color: #856404; padding: 20px; border-radius: 10px; border: 1px solid #FFEEBA; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: left; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .In-Progress { background: #e3f2fd; color: #1976d2; }
        .Delivered { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
<div style="max-width: 1000px; margin: 40px auto; padding: 20px;">
    <h1>My Deliveries</h1>
    
    <nav style="margin-bottom: 20px;">
        <a href="rider_dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a>
    </nav>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 20px;"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
    <?php endif; ?>

    <?php if ($has_active): ?>
        <div class="active-banner">
            <strong>🚀 ACTIVE DELIVERY FOUND</strong><br>
            You are currently delivering Order #<?= $active_order_id ?>.<br>
            <a href="rider_updatestatus.php?order_id=<?= $active_order_id ?>" style="color: #856404; font-weight: bold;">CLICK HERE TO COMPLETE DELIVERY</a>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($o = $history_res->fetch_assoc()): ?>
            <tr>
                <td>#<?= $o['order_id'] ?></td>
                <td><?= htmlspecialchars($o['full_name']) ?></td>
                <td><?= htmlspecialchars($o['address']) ?></td>
                <td><span class="status-badge <?= str_replace(' ', '-', $o['order_status']) ?>"><?= $o['order_status'] ?></span></td>
                <td>
                    <a href="rider_updatestatus.php?order_id=<?= $o['order_id'] ?>">Details</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>