<?php
session_start();
include 'db.php';

$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer';
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$dashboard = $is_logged_in ? 'customer_dashboard.php' : 'index.php';

$has_address = false;
if ($is_logged_in) {
    $address_check = $conn->query("SELECT address_id FROM customeraddresses WHERE customer_id = $user_id LIMIT 1");
    $has_address = ($address_check && $address_check->num_rows > 0);
}

$result = $conn->query("SELECT d.*, dis.discount_type, dis.discount_value FROM Dishes d LEFT JOIN Discounts dis ON d.dish_id = dis.dish_id WHERE d.availability='available'");
$dishes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu | Ahmed's Local Spot</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #fdfdfb; margin: 0; color: #333; }

        /* --- NAVIGATION BAR (FIXED SPACING) --- */
        nav { 
            background: #5D4037; 
            padding: 0 10%; /* Creates space on the sides */
            height: 70px;
            display: flex; 
            justify-content: space-between; /* Pushes brand left, links right */
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .nav-brand { font-size: 1.5rem; color: #f5f5f2; font-weight: bold; text-decoration: none; }

        .nav-links a { 
            color: #e5e6ea; 
            text-decoration: none; 
            margin-left: 25px; 
            font-weight: 500; 
            transition: color 0.3s; 
        }
        .nav-links a:hover { color: #A7C7E7; }

        /* --- CONTENT LAYOUT --- */
        .menu-container { max-width: 1000px; margin: 50px auto; padding: 0 20px; }
        
        .page-header { text-align: center; margin-bottom: 50px; }
        .page-header h1 { font-size: 2.8rem; color: #5D4037; margin: 0; }
        .page-header p { color: #888; font-style: italic; }

        /* --- TABLE STYLING --- */
        .menu-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .menu-row { border-bottom: 1px solid #f0f0f0; transition: background 0.2s; }
        .menu-row:hover { background: #fffcf9; }

        .img-cell { width: 160px; padding: 25px; }
        .menu-img { width: 140px; height: 100px; object-fit: cover; border-radius: 8px; display: block; }
        .no-img { width: 140px; height: 100px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 0.8rem; }

        .details-cell { padding: 25px; vertical-align: top; }
        .dish-name { font-size: 1.4rem; font-weight: bold; color: #1a1a1a; margin-bottom: 5px; display: block; }
        .dish-desc { color: #666; font-size: 0.95rem; line-height: 1.5; margin: 0; }

        .price-cell { width: 130px; padding: 25px; text-align: right; vertical-align: top; }
        .current-price { font-size: 1.3rem; font-weight: bold; color: #5D4037; display: block; }
        .old-price { font-size: 0.9rem; text-decoration: line-through; color: #bcaaa4; }

        .qty-cell { width: 100px; padding: 25px; text-align: center; }
        .qty-input { width: 55px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; text-align: center; font-weight: bold; }

        /* --- CHECKOUT BUTTON --- */
        .action-footer { margin-top: 40px; text-align: right; }
        .btn-checkout { background: #1A365D; color: white; padding: 15px 45px; border: none; border-radius: 4px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-checkout:hover { background: #5D4037; transform: translateY(-2px); }

        /* --- STATUS MESSAGES --- */
        .alert-bar { background: #fff3e0; border: 1px solid #ffe0b2; padding: 15px; text-align: center; border-radius: 8px; margin-bottom: 30px; color: #e65100; }
    </style>
</head>
<body>

<nav>
    <a href="<?= $dashboard ?>" class="nav-brand">Ahmed's Local Spot</a>
    <div class="nav-links">
        <a href="customers_menu.php">Menu</a>
        <a href="reviews.php">Reviews</a>
        <?php if (!$is_logged_in): ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php else: ?>
            <a href="logout.php">Logout</a>
        <?php endif; ?>
    </div>
</nav>

<div class="menu-container">
    <div class="page-header">
        <h1>Our Menu</h1>
        <p>Hand-crafted dishes delivered to your doorstep</p>
    </div>

    <?php if (!$is_logged_in): ?>
        <div class="alert-bar">Please <a href="login.php">log in</a> to place an order.</div>
    <?php elseif (!$has_address): ?>
        <div class="alert-bar">Please <a href="profile.php">add a delivery address</a> to unlock ordering.</div>
    <?php endif; ?>

    <form method="POST" action="customer_checkout.php">
        <table class="menu-table">
            <?php foreach ($dishes as $dish): 
                $final_p = $dish['price'];
                if (!empty($dish['discount_type'])) {
                    $final_p = ($dish['discount_type'] === 'percentage') 
                               ? $dish['price'] * (1 - $dish['discount_value']/100) 
                               : $dish['price'] - $dish['discount_value'];
                }
            ?>
            <tr class="menu-row">
                <td class="img-cell">
                    <?php if (!empty($dish['image_path'])): ?>
                        <img src="images/<?= htmlspecialchars($dish['image_path']) ?>" class="menu-img">
                    <?php else: ?>
                        <div class="no-img">No Photo</div>
                    <?php endif; ?>
                </td>
                
                <td class="details-cell">
                    <span class="dish-name"><?= htmlspecialchars($dish['dish_name']) ?></span>
                    <p class="dish-desc"><?= htmlspecialchars($dish['description']) ?></p>
                </td>

                <td class="price-cell">
                    <span class="current-price">$<?= number_format($final_p, 2) ?></span>
                    <?php if($final_p < $dish['price']): ?>
                        <span class="old-price">$<?= number_format($dish['price'], 2) ?></span>
                    <?php endif; ?>
                </td>

                <td class="qty-cell">
                    <?php if ($is_logged_in && $has_address): ?>
                        <input type="number" name="quantity[<?= $dish['dish_id'] ?>]" class="qty-input" min="0" value="0">
                    <?php else: ?>
                        <span style="color: #ccc;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if ($is_logged_in && $has_address): ?>
            <div class="action-footer">
                <button type="submit" class="btn-checkout">Review & Checkout</button>
            </div>
        <?php endif; ?>
    </form>
</div>

</body>
</html>