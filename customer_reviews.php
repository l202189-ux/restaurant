<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];
$submitted = false;

/* --- FETCH ITEMS --- */
$query = "
    SELECT oi.dish_id, d.dish_name 
    FROM OrderItems oi
    JOIN Dishes d ON oi.dish_id = d.dish_id
    WHERE oi.order_id = $order_id
";
$items_res = $conn->query($query);

/* --- HANDLE SUBMISSION --- */
if (isset($_POST['submit_all_reviews'])) {
    foreach ($_POST['reviews'] as $dish_id => $data) {
        $rating = (int)$data['rating'];
        $review_text = trim($conn->real_escape_string($data['text']));

        if (!empty($review_text)) {
            $sql_dish_id = ($dish_id == 0) ? "NULL" : (int)$dish_id;
            $insert_query = "INSERT INTO Reviews (order_id, customer_id, dish_id, rating, review_text, review_date) 
                             VALUES ($order_id, $customer_id, $sql_dish_id, $rating, '$review_text', NOW())";
            $conn->query($insert_query);
        }
    }
    $submitted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Your Order #<?= $order_id ?></title>
    <style>
        body { background-color: #D7CCC8; font-family: 'Poppins', sans-serif; color: #3E2723; margin: 0; }
        nav { background: #3E2723; padding: 15px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; font-weight: 500; }

        .review-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        
        .header-section { text-align: center; margin-bottom: 30px; }
        .header-section h1 { color: #3E2723; text-transform: uppercase; margin-bottom: 5px; font-size: 1.8rem; }

        .review-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 6px solid #5D4037;
        }

        .dish-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .dish-name { font-weight: bold; font-size: 1.1rem; color: #5D4037; }

        select {
            padding: 8px 12px;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: inherit;
            color: #3E2723;
            background-color: #fff;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 12px;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
            resize: none; 
            transition: border-color 0.3s;
        }

        textarea:focus { border-color: #8D6E63; }

        .overall-section {
            background: #EFEBE9;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid #BCAAA4;
        }

        /* SMALLER, CENTERED BUTTON */
        .btn-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-submit {
            background-color: #5D4037;
            color: white;
            border: none;
            padding: 12px 40px;
            width: auto;
            min-width: 180px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover { 
            background-color: #3E2723;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .success-banner {
            background: #ffffff;
            color: #388E3C;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #5D4037;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<nav>
    <a href="customer_dashboard.php">Dashboard</a>
    <a href="customer_orders.php">My Orders</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="review-container">
    
    <?php if ($submitted): ?>
        <div class="success-banner">
            <h2>Review Posted!</h2>
            <p>Thanks for helping the community with your feedback.</p>
            <a href="customer_orders.php" class="back-link">&larr; Return to My Orders</a>
        </div>
    <?php else: ?>

    <div class="header-section">
        <h1>How was your meal?</h1>
        <p>Order <strong>#<?= $order_id ?></strong></p>
    </div>

    <form method="POST">
        <?php 
        $items_res->data_seek(0); 
        while($item = $items_res->fetch_assoc()): 
        ?>
            <div class="review-card">
                <div class="dish-header">
                    <span class="dish-name"><?= htmlspecialchars($item['dish_name']) ?></span>
                    <select name="reviews[<?= $item['dish_id'] ?>][rating]">
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <textarea name="reviews[<?= $item['dish_id'] ?>][text]" placeholder="How was the taste?"></textarea>
            </div>
        <?php endwhile; ?>

        <div class="overall-section">
            <h3 style="margin-top: 0; font-size: 1.1rem;">Overall Experience</h3>
            <textarea name="reviews[0][text]" placeholder="General comments about the delivery or packaging..."></textarea>
            <input type="hidden" name="reviews[0][rating]" value="5"> 
        </div>

        <div class="btn-wrapper">
            <button type="submit" name="submit_all_reviews" class="btn-submit">
                Submit
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

</body>
</html>