<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];

// Fetch Addresses for selection
$addr_res = $conn->query("SELECT * FROM customeraddresses WHERE customer_id = $customer_id");
if ($addr_res->num_rows == 0) {
    die("❌ Error: Add a delivery address first. <a href='profile.php'>Profile</a>");
}

// Logic to build the summary from POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $quantities = $_POST['quantity'];
    $summary_items = [];
    $grand_total = 0;

    foreach ($quantities as $dish_id => $qty) {
        if ($qty > 0) {
            $dish_id = (int)$dish_id;
            $res = $conn->query("
                SELECT d.dish_name, d.price, dis.discount_type, dis.discount_value 
                FROM Dishes d 
                LEFT JOIN Discounts dis ON d.dish_id = dis.dish_id 
                WHERE d.dish_id = $dish_id
            ");
            
            if ($row = $res->fetch_assoc()) {
                $unit_price = $row['price'];
                $discount_per_unit = 0;

                if (!empty($row['discount_type'])) {
                    if ($row['discount_type'] === 'percentage') {
                        $discount_per_unit = $unit_price * ($row['discount_value'] / 100);
                    } elseif ($row['discount_type'] === 'flat') {
                        $discount_per_unit = $row['discount_value'];
                    }
                }

                $final_price = $unit_price - $discount_per_unit;
                $line_total = $final_price * $qty;
                $grand_total += $line_total;

                $summary_items[] = [
                    'dish_id' => $dish_id,
                    'name' => $row['dish_name'],
                    'qty' => $qty,
                    'price' => $unit_price,
                    'discount' => $discount_per_unit,
                    'total' => $line_total
                ];
            }
        }
    }

    if (empty($summary_items)) {
        header("Location: customers_menu.php?error=empty");
        exit();
    }
} else {
    header("Location: customers_menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Summary - Ahmed's Local Spot</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #D7CCC8; font-family: 'Poppins', sans-serif; color: #3E2723; margin: 0; }
        
        .checkout-container { 
            max-width: 600px; 
            margin: 60px auto; 
            padding: 30px; 
            background: #fff; 
            border-radius: 15px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
            border-top: 8px solid #5D4037;
        }

        h2 { text-transform: uppercase; letter-spacing: 1px; color: #3E2723; text-align: center; margin-bottom: 25px; }

        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .summary-table td { padding: 15px 10px; border-bottom: 1px solid #EFEBE9; }
        
        .item-info strong { color: #5D4037; font-size: 1.05rem; }
        .discount-text { color: #A1887F; font-weight: bold; font-size: 0.85rem; }

        .total-row { 
            font-size: 24px; 
            font-weight: bold; 
            color: #3E2723; 
            text-align: right; 
            padding: 25px 0; 
            border-top: 2px solid #5D4037;
        }

        .address-section {
            background: #FDFBFA;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #EFEBE9;
            margin-bottom: 25px;
        }

        .address-section label {
            display: block;
            font-weight: bold;
            margin-bottom: 10px;
            color: #5D4037;
            font-size: 0.9rem;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #EFEBE9;
            border-radius: 8px;
            font-family: inherit;
            color: #3E2723;
            outline: none;
        }

        /* UPDATED: SMALLER BUTTON STYLING */
        .btn-container { text-align: center; margin-top: 10px; }

        .btn-confirm { 
            background: #5D4037; 
            color: white; 
            border: none; 
            padding: 12px 30px; /* Reduced padding */
            width: auto;         /* Changed from 100% to auto */
            min-width: 200px;    /* Gives it a nice consistent size */
            cursor: pointer; 
            border-radius: 25px; /* Rounded corners for a modern feel */
            font-size: 1rem; 
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .btn-confirm:hover { 
            background: #3E2723; 
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #8D6E63;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.8rem;
        }

        textarea { resize: none; }
    </style>
</head>
<body>

<div class="checkout-container">
    <h2>Review Your Order</h2>
    
    <table class="summary-table">
        <?php foreach ($summary_items as $item): ?>
        <tr>
            <td class="item-info">
                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                <small style="color: #8D6E63;">$<?= number_format($item['price'], 2) ?> x <?= $item['qty'] ?></small>
                <?php if($item['discount'] > 0): ?>
                    <br><span class="discount-text">Promo: -$<?= number_format($item['discount'] * $item['qty'], 2) ?></span>
                <?php endif; ?>
            </td>
            <td style="text-align:right; font-weight: bold; color: #5D4037;">$<?= number_format($item['total'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="total-row">Total: $<?= number_format($grand_total, 2) ?></div>

    <form action="place_order.php" method="POST">
        <div class="address-section">
            <label>DELIVER TO:</label>
            <select name="address_id" required>
                <?php 
                $addr_res->data_seek(0);
                while($addr = $addr_res->fetch_assoc()): ?>
                    <option value="<?= $addr['address_id'] ?>">
                        <?= htmlspecialchars($addr['address_text']) ?>, <?= htmlspecialchars($addr['city']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <?php foreach($summary_items as $item): ?>
            <input type="hidden" name="qty[<?= $item['dish_id'] ?>]" value="<?= $item['qty'] ?>">
        <?php endforeach; ?>

        <div class="btn-container">
            <button type="submit" class="btn-confirm">Place Order</button>
        </div>
        
        <a href="customers_menu.php" class="cancel-link">← Change my order</a>
    </form>
</div>

</body>
</html>