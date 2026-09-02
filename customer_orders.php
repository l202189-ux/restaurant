<?php
session_start();
include 'db.php';

// Ensure customer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];

// SQL: Grouping by every column to ensure 'customer_verify_code' is visible to PHP
$sql = "
SELECT 
    o.order_id, 
    o.order_status, 
    o.order_timestamp, 
    o.customer_verify_code, 
    SUM(oi.line_total) AS total_amount
FROM Orders o
JOIN OrderItems oi ON o.order_id = oi.order_id
WHERE o.customer_id = $customer_id
GROUP BY o.order_id, o.order_status, o.order_timestamp, o.customer_verify_code
ORDER BY o.order_timestamp DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Ahmed's Local Spot</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #D7CCC8; font-family: 'Poppins', sans-serif; }
        .profile-box { max-width: 1150px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .orders-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .orders-table th { background: #5D4037; color: white; padding: 15px; text-align: left; font-size: 0.85rem; text-transform: uppercase; }
        .orders-table td { padding: 20px 15px; border-bottom: 1px solid #EFEBE9; vertical-align: middle; }

        /* Security Code Badge - UPDATED STYLE */
        .delivery-code-badge {
            background-color: #FFF3E0;
            color: #E65100;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 800;
            display: inline-block;
            margin-top: 8px;
            border: 2px dashed #FFB74D;
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .status-container { display: flex; flex-direction: column; align-items: flex-start; }
        .status-text { 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 12px; 
            padding: 4px 10px; 
            background: #EFEBE9; 
            border-radius: 4px; 
            color: #5D4037;
        }

        .rate-link { color: #5D4037; text-decoration: none; font-weight: bold; }
        .rate-link:hover { text-decoration: underline; }
        .reviewed { color: #388E3C; font-weight: bold; }
        .receipt-link { color: #8D6E63; text-decoration: none; font-size: 0.9rem; }
        
        nav { background: #3E2723; padding: 15px; text-align: center; border-radius: 0 0 10px 10px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>

<nav>
    <a href="customer_dashboard.php">Dashboard</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="profile-box">
    <h1>My Orders</h1>
    
    <?php if ($result && $result->num_rows > 0): ?>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Status & Security</th> 
                <th>Total</th>
                <th>Details</th>
                <th>Reviews</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = $result->fetch_assoc()): ?>
            <?php
            $review_check = $conn->query("SELECT review_id FROM Reviews WHERE order_id = {$order['order_id']} AND customer_id = $customer_id");
            $has_review = ($review_check && $review_check->num_rows > 0);
            ?>
            <tr>
                <td><strong>#<?= $order['order_id'] ?></strong></td>
                <td><?= date('d M Y', strtotime($order['order_timestamp'])) ?><br><small><?= date('H:i', strtotime($order['order_timestamp'])) ?></small></td>
                <td>
                    <div class="status-container">
                        <span class="status-text"><?= htmlspecialchars($order['order_status']) ?></span>
                        
                        <?php 
                        /* UPDATED LOGIC: 
                           Checks if status is 'Pending' OR 'In Progress' 
                           to ensure code is visible immediately.
                        */
                        if (($order['order_status'] === 'Pending' || $order['order_status'] === 'In Progress') && (int)$order['customer_verify_code'] > 0): 
                        ?>
                            <div class="delivery-code-badge">
                                <?= $order['customer_verify_code'] ?>
                            </div>
                            <small style="margin-top:4px; font-size: 10px; color: #8D6E63; font-weight: 600;">GIVE TO RIDER</small>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="font-weight:bold; color: #388E3C;">$<?= number_format($order['total_amount'], 2) ?></td>
                <td><a href="customer_orderdetails.php?order_id=<?= $order['order_id'] ?>" class="rate-link">View Details</a></td>
                <td>
                    <?php if ($order['order_status'] === 'Delivered'): ?>
                        <?php if ($has_review): ?>
                            <span class="reviewed">✓ Reviewed</span>
                        <?php else: ?>
                            <a href="customer_reviews.php?order_id=<?= $order['order_id'] ?>" class="rate-link" style="color:#E65100;">Rate Now</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#999; font-size: 0.8rem;">Available after delivery</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="generate_receipt.php?order_id=<?= $order['order_id'] ?>" class="receipt-link">📄 PDF Receipt</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="text-align:center; padding:40px; color:#666;">You haven't placed any orders yet.</p>
    <?php endif; ?>
</div>

</body>
</html>