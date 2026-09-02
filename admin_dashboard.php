<?php
include 'db.php';
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit();
}

/* ===========================================================
    DATABASE LOGIC
   =========================================================== */
// Stats fetching
$total_res = $conn->query("SELECT SUM(line_total) as grand_total, COUNT(DISTINCT order_id) as total_orders FROM orderitems");
$stats = $total_res->fetch_assoc();
$grand_total = $stats['grand_total'] ?? 0;
$total_orders = $stats['total_orders'] ?? 0;

// Fetching revenue trends for the last 30 days using SQL aggregate functions 
$graph_query = $conn->query("
    SELECT DATE(o.order_timestamp) as date, SUM(oi.line_total) as daily_total
    FROM Orders o
    JOIN OrderItems oi ON o.order_id = oi.order_id
    WHERE o.order_timestamp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(o.order_timestamp)
    ORDER BY DATE(o.order_timestamp) ASC
");
$dates = []; $totals = [];
while($row = $graph_query->fetch_assoc()) {
//Formatting dates for the x-axis
    $dates[] = date('M d', strtotime($row['date'])); 
//Converting strings to floats
    $totals[] = (float)$row['daily_total'];
}

// Popular Dishes
$popular_query = $conn->query("
    SELECT d.dish_name, SUM(oi.quantity) as total_qty
    FROM orderitems oi
    JOIN Dishes d ON oi.dish_id = d.dish_id
    GROUP BY d.dish_id
    ORDER BY total_qty DESC
    LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Ahmed's Local Spot</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #c4bdb4;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* SIDEBAR */
        .admin-panel {
            width: 320px;
            background-color: #5D4037;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            color: white;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
        }

        .admin-panel h1 {
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 50px;
            color: #D7CCC8;
            text-align: center;
        }

        nav { width: 100%; display: flex; flex-direction: column; gap: 10px; }
        nav a {
            background-color: rgba(255,255,255,0.05);
            color: white; text-decoration: none;
            padding: 16px; border-radius: 12px;
            font-weight: 600; text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
        }
        nav a:hover {
            background-color: #1A365D;
            transform: translateX(10px);
            border-color: transparent;
        }

        /* PERFORMANCE HUB */
        .performance-hub {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .header-section { margin-bottom: 30px; }
        .header-section h2 { color: #3E2723; margin: 0; font-size: 1.8rem; }

        /* MINI STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .mini-card {
            background: #8D6E63;
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .mini-card span { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
        .mini-card strong { display: block; font-size: 1.8rem; margin-top: 8px; }

        /* MAIN CONTENT GRID */
        .main-content-grid {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 25px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .chart-card { height: 400px; position: relative; }
        
        .popular-card { 
            background: #5D4037; 
            color: white; 
            display: flex;
            flex-direction: column;
        }
        .popular-card h3 { margin-top: 0; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }

        .dish-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dish-row:last-child { border-bottom: none; }

        .logout-btn {
            margin-top: auto;
            color: #f3f3f3;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            padding: 10px;
        }
        .logout-btn:hover { color: #ffffff; }
    </style>
</head>
<body>

    <div class="admin-panel">
        <h1>Ahmed's Local Spot<br><small style="font-size: 0.7rem; opacity: 0.6;">Admin Dashboard</small></h1>
        <nav>
            <a href="admin_dishes.php">Manage Dishes</a>
            <a href="rider_accounts.php">Rider Accounts</a>
            <a href="admin_orders.php">View Orders</a>
            <a href="profile.php">My Profile</a>
        <a href="logout.php" class="logout-btn">LOGOUT</a>
    </div>

    <div class="performance-hub">
        <div class="header-section">
            <h2>Restaurant Analytics</h2>
        </div>

        <div class="stats-grid">
            <div class="mini-card">
                <span>Total Revenue</span>
                <strong>$<?= number_format($grand_total, 2) ?></strong>
            </div>
            <div class="mini-card" style="background-color: #795548;">
                <span>Total Orders</span>
                <strong><?= $total_orders ?></strong>
            </div>
            <div class="mini-card" style="background-color: #6D4C41;">
                <span>Avg Order</span>
                <strong>$<?= $total_orders > 0 ? number_format($grand_total / $total_orders, 2) : '0.00' ?></strong>
            </div>
        </div>

        <div class="main-content-grid">
            <div class="card chart-card">
                <h4 style="margin: 0 0 20px 0; color: #5D4037;">Revenue Overview (Last 30 Days)</h4>
                <div style="height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <div class="card popular-card">
                <h3>Top Performing Dishes</h3>
                <?php if($popular_query->num_rows > 0): ?>
                    <?php while($dish = $popular_query->fetch_assoc()): ?>
                        <div class="dish-row">
                            <span><?= htmlspecialchars($dish['dish_name']) ?></span>
                            <span style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">
                                <?= $dish['total_qty'] ?> Sold
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="opacity: 0.5;">No sales recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?= json_encode($totals) ?>,
                    borderColor: '#5D4037',
                    backgroundColor: 'rgba(93, 64, 55, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 4,
                    pointBackgroundColor: '#1A365D'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f0f0', drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>