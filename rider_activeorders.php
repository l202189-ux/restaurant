<?php
session_start();
include 'db.php';

// Only allow riders
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'rider') {
    header("Location: login.php");
    exit();
}

$rider_id = (int)$_SESSION['user_id'];

// Fetch orders: Pending or assigned to this rider
$orders_res = $conn->query("
    SELECT 
        o.order_id, 
        o.order_status, 
        o.order_timestamp, 
        u.full_name AS customer_name, 
        CONCAT(a.address_text, ', ', a.city) AS address
    FROM orders o
    JOIN Users u ON o.customer_id = u.user_id
    JOIN customeraddresses a ON o.address_id = a.address_id
    WHERE o.order_status='Pending' OR (o.rider_id=$rider_id AND o.order_status='In Progress')
    ORDER BY o.order_timestamp DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Orders</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- SHADES OF BROWN THEME --- */
        body {
            background-color: #D7CCC8; /* Light stone brown */
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            color: #3E2723;
        }

        .page-title {
            text-align: center;
            color: #3E2723;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 30px 0 20px 0;
            font-size: 1.8rem;
        }

        /* Consistent Navigation Bar */
        nav {
            background-color: #3E2723; /* Espresso */
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }

        nav a {
            color: #F5F5F5;
            text-decoration: none;
            background-color: #5D4037; /* Mocha */
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        nav a:hover {
            background-color: #8D6E63;
            transform: translateY(-1px);
        }

        /* The Wrapper */
        .table-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #5D4037; /* Mocha */
            color: white;
            text-align: left;
            padding: 15px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #EFEBE9;
            color: #4E342E;
            font-size: 0.95rem;
        }

        tr:hover {
            background-color: #FDFBFA;
        }

        /* Status Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }

        .status-pending { 
            background: #EFEBE9; 
            color: #5D4037; 
            border: 1px solid #BCAAA4;
        }
        
        .status-progress { 
            background: #D7CCC8; 
            color: #3E2723; 
            border: 1px solid #8D6E63;
        }

        /* Action Buttons */
        .view-link {
            display: inline-block;
            background-color: #5D4037;
            color: white;
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
            text-align: center;
            min-width: 100px;
        }

        .view-link:hover {
            background-color: #3E2723;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .error-msg {
            text-align: center;
            color: #8D6E63;
            padding: 40px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<nav>
    <a href="rider_dashboard.php">Dashboard</a>
    <a href="rider_deliverystatus.php">Delivery History</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<h1 class="page-title">Active Orders</h1>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Status</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($orders_res && $orders_res->num_rows > 0): ?>
            <?php while($order = $orders_res->fetch_assoc()): 
                $statusClass = ($order['order_status'] == 'Pending') ? 'status-pending' : 'status-progress';
            ?>
            <tr>
                <td><strong>#<?= $order['order_id'] ?></strong></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= htmlspecialchars($order['address']) ?></td>
                <td>
                    <span class="badge <?= $statusClass ?>">
                        <?= $order['order_status'] ?>
                    </span>
                </td>
                <td style="font-size: 0.85rem; color: #8D6E63;">
                    <?= date('M d, H:i', strtotime($order['order_timestamp'])) ?>
                </td>
                <td>
                    <?php if($order['order_status'] == 'Pending'): ?>
                        <a href="rider_updatestatus.php?order_id=<?= $order['order_id'] ?>" class="view-link">Claim Order</a>
                    <?php elseif($order['order_status'] == 'In Progress'): ?>
                        <a href="rider_updatestatus.php?order_id=<?= $order['order_id'] ?>" class="view-link">Update Status</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="error-msg">No active orders available at the moment.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>