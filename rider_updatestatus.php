<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'rider') {
    header("Location: login.php");
    exit();
}

$rider_id = (int)$_SESSION['user_id'];

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Error: No order specified.");
}
$order_id = (int)$_GET['order_id'];

/* --- CLAIM ORDER LOGIC --- */
$check_claim = $conn->query("SELECT rider_id FROM Orders WHERE order_id = $order_id");
$claim_data = $check_claim->fetch_assoc();

if ($claim_data['rider_id'] === NULL) {
    $rider_code = rand(1000, 9999);
    $customer_code = rand(1000, 9999);

    $stmt = $conn->prepare("UPDATE Orders SET rider_id = ?, order_status = 'Pending', rider_verify_code = ?, customer_verify_code = ?, rider_code_verified = 0, customer_code_verified = 0 WHERE order_id = ?");
    $stmt->bind_param("iiii", $rider_id, $rider_code, $customer_code, $order_id);
    $stmt->execute();

    header("Location: rider_updatestatus.php?order_id=$order_id");
    exit();
}

/* --- FETCH DATA --- */
$order_res = $conn->query("SELECT * FROM Orders WHERE order_id = $order_id");
$order = $order_res->fetch_assoc();

if ((int)$order['rider_id'] !== $rider_id) {
    die("Error: Assignment mismatch.");
}

/* --- VERIFICATION LOGIC --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_handshake'])) {
    $input_rider = trim($_POST['rider_code_input']);
    $input_customer = trim($_POST['customer_code_input']);

    // Compare as strings to ensure matching
    if ($input_rider === (string)$order['rider_verify_code'] && $input_customer === (string)$order['customer_verify_code']) {
        $conn->query("UPDATE Orders SET rider_code_verified = 1, customer_code_verified = 1, order_status = 'Delivered', delivery_timestamp = NOW() WHERE order_id = $order_id");
        
        $_SESSION['success_msg'] = "Order #$order_id delivered successfully!";
        header("Location: rider_deliverystatus.php");
        exit();
    } else {
        $error = "Verification failed. Please double check the codes with the customer.";
    }
}

$items_res = $conn->query("SELECT oi.quantity, d.dish_name FROM OrderItems oi JOIN Dishes d ON oi.dish_id = d.dish_id WHERE oi.order_id = $order_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Delivery #<?= $order_id ?></title>
    <style>
        body { padding: 20px; font-family: 'Poppins', sans-serif; background-color: #D7CCC8; color: #3E2723; display: flex; flex-direction: column; align-items: center; }
        .card { background: #fff; border-radius: 15px; padding: 30px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); text-align: center; width: 100%; max-width: 450px; border-top: 6px solid #5D4037; }
        .error-msg { color: #d32f2f; font-weight: bold; margin-bottom: 20px; }
        .code-display { background: #FDFBFA; border: 2px dashed #BCAAA4; padding: 20px; border-radius: 12px; margin: 25px 0; }
        .code-num { font-size: 2.5rem; font-weight: bold; letter-spacing: 8px; }
        .input-group { text-align: left; margin-bottom: 20px; }
        input[type="text"] { width: 100%; padding: 12px; border: 2px solid #EFEBE9; border-radius: 8px; font-size: 1.2rem; text-align: center; box-sizing: border-box; }
        .btn-submit { background-color: #5D4037; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: 600; cursor: pointer; text-transform: uppercase; }
        .item-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Order #<?= $order_id ?></h2>
        <p>Status: <strong><?= $order['order_status'] ?></strong></p>

        <?php if (isset($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($order['order_status'] !== 'Delivered'): ?>
            <div class="code-display">
                <div style="font-size: 0.7rem; color: #8D6E63;">YOUR RIDER CODE</div>
                <div class="code-num"><?= $order['rider_verify_code'] ?></div>
            </div>

            <form method="POST">
                <div class="input-group">
                    <label>Confirm Your Rider Code</label>
                    <input type="text" name="rider_code_input" maxlength="4" required>
                </div>
                <div class="input-group">
                    <label>Enter Customer's Code</label>
                    <input type="text" name="customer_code_input" maxlength="4" required>
                </div>
                <button type="submit" name="submit_handshake" class="btn-submit">Complete Delivery</button>
            </form>
        <?php else: ?>
            <p style="color: green; font-weight: bold;">✓ DELIVERED</p>
            <a href="rider_deliverystatus.php" class="btn-submit" style="text-decoration:none; display:block;">Back to History</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Order Items</h3>
        <?php while($item = $items_res->fetch_assoc()): ?>
            <div class="item-row">
                <span><?= htmlspecialchars($item['dish_name']) ?></span>
                <strong>x<?= $item['quantity'] ?></strong>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>