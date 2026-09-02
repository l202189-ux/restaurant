<?php
include 'db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Reviews - Ahmed's Local Spot</title>
    <style>
        /* Unified Light Brown/Tan Background for the page */
        body { 
            background-color: #D7CCC8; 
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: #3E2723;
        }

        /* --- MATCHED NAVIGATION BAR STYLE --- */
        nav.review-nav {
            background-color: #3E2723; /* Dark brown bar from your image */
            padding: 15px 0;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        /* --- MATCHED BUTTON STYLE --- */
        nav.review-nav a {
            color: #ffffff;
            background-color: #5D4037; /* Lighter brown buttons */
            text-decoration: none;
            font-weight: 700;
            padding: 10px 25px;
            border-radius: 8px; /* Slightly rounded corners as seen in image */
            font-size: 0.9rem;
            transition: opacity 0.3s ease;
        }

        nav.review-nav a:hover {
            opacity: 0.8;
        }

        /* --- LAYOUT & GRID --- */
        .container {
            max-width: 950px; 
            margin: 40px auto;
            padding: 0 20px;
        }

        .main-title {
            text-align: center;
            margin-bottom: 35px;
            color: #3E2723;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1.5rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* Compact White Cards */
        .review-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            border-bottom: 1px solid #F5F5F5;
            padding-bottom: 8px;
        }

        .customer-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #5D4037;
        }

        .date-stamp {
            font-size: 0.75rem;
            color: #A1887F;
        }

        .order-badge {
            font-size: 0.7rem;
            background: #F5F5F5;
            padding: 3px 8px;
            border-radius: 4px;
            color: #8D6E63;
            font-weight: bold;
        }

        .review-entry {
            padding: 10px 0;
            border-bottom: 1px solid #FAFAFA;
        }

        .review-entry:last-child {
            border-bottom: none;
        }

        .entry-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .tag {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .dish-tag { background: #EFEBE9; color: #5D4037; }
        .general-tag { background: #FFF3E0; color: #E65100; }

        .stars {
            font-size: 0.8rem;
            color: #FFB300;
        }

        .review-text {
            margin: 0;
            line-height: 1.4;
            font-size: 0.85rem;
            color: #4E342E;
            font-style: italic;
        }

        @media (max-width: 600px) {
            .reviews-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="reviews-page">

    <nav class="review-nav">
        <a href="index.php">Home</a>
        <a href="customers_menu.php">Order Now</a>
    </nav>

    <div class="container">
        <h1 class="main-title">Community Reviews</h1>

        <div class="reviews-grid">
            <?php
            $reviews_res = $conn->query("
                SELECT r.order_id, r.rating, r.review_text, r.review_date, r.dish_id,
                        u.full_name AS customer_name, 
                        d.dish_name
                FROM Reviews r
                JOIN Users u ON r.customer_id = u.user_id
                LEFT JOIN Dishes d ON r.dish_id = d.dish_id
                ORDER BY r.review_date DESC, r.order_id DESC
            ");

            if ($reviews_res && $reviews_res->num_rows > 0):
                $reviews = [];
                while ($row = $reviews_res->fetch_assoc()) {
                    $reviews[$row['order_id']]['customer'] = $row['customer_name'];
                    $reviews[$row['order_id']]['date'] = $row['review_date'];
                    $reviews[$row['order_id']]['items'][] = [
                        'name' => $row['dish_name'] ?? "Overall Experience",
                        'rating' => $row['rating'],
                        'text' => $row['review_text']
                    ];
                }

                foreach ($reviews as $order_id => $group):
            ?>
                <div class="review-card">
                    <div class="card-header">
                        <div class="user-info">
                            <span class="customer-name"><?= htmlspecialchars($group['customer']) ?></span><br>
                            <span class="date-stamp"><?= date('M d, Y', strtotime($group['date'])) ?></span>
                        </div>
                        <span class="order-badge">#<?= $order_id ?></span>
                    </div>

                    <div class="card-body">
                        <?php foreach ($group['items'] as $item): 
                            $tagClass = ($item['name'] == "Overall Experience") ? "general-tag" : "dish-tag";
                        ?>
                            <div class="review-entry">
                                <div class="entry-top">
                                    <span class="tag <?= $tagClass ?>"><?= htmlspecialchars($item['name']) ?></span>
                                    <div class="stars"><?= str_repeat('★', $item['rating']) ?></div>
                                </div>
                                <p class="review-text">"<?= htmlspecialchars($item['text']) ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                endforeach;
            else:
                echo "<div class='no-reviews'><p>No reviews yet.</p></div>";
            endif;
            ?>
        </div>
    </div>
</body>
</html>