<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['qty'])) {
    header("Location: customers_menu.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$address_id = (int)($_POST['address_id'] ?? 0);
$check = $conn->query("SELECT address_id FROM customeraddresses WHERE address_id=$address_id AND customer_id=$customer_id");
if (!$check || $check->num_rows == 0) die("Please choose your saved delivery address.");

$items = [];
foreach ($_POST['qty'] as $dish_id => $qty) {
    if (!ctype_digit((string)$qty)) die("Invalid quantity.");
    $qty = (int)$qty;
    if ($qty == 0) continue;
    if ($qty > 100) die("Please choose a smaller quantity.");
    $dish_id = (int)$dish_id;
    $res = $conn->query("SELECT d.price, dis.discount_type, dis.discount_value FROM Dishes d LEFT JOIN Discounts dis ON d.dish_id=dis.dish_id WHERE d.dish_id=$dish_id AND d.availability='available'");
    $dish = $res ? $res->fetch_assoc() : null;
    if (!$dish) die("Dish is unavailable. Please return to the menu.");

    $price = (float)$dish['price'];
    $discount = 0;
    if ($dish['discount_type'] == 'percentage') $discount = $price * $dish['discount_value'] / 100;
    if ($dish['discount_type'] == 'flat') $discount = (float)$dish['discount_value'];
    $discount = max(0, min($discount, $price));
    $total = round(($price - $discount) * $qty, 2);
    $items[] = [$dish_id, $qty, $price, $discount, $total];
}
if (count($items) == 0) die("Please select at least one dish.");

$conn->begin_transaction();
$stmt = $conn->prepare("INSERT INTO Orders (customer_id, order_status, address_id, order_timestamp) VALUES (?, 'Pending', ?, NOW())");
$stmt->bind_param("ii", $customer_id, $address_id);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt_item = $conn->prepare("INSERT INTO OrderItems (order_id, dish_id, quantity, unit_price, discount_applied, line_total) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($items as $item) {
    $dish_id = $item[0]; $qty = $item[1]; $price = $item[2]; $discount = $item[3]; $total = $item[4];
    $stmt_item->bind_param("iiiddd", $order_id, $dish_id, $qty, $price, $discount, $total);
    $stmt_item->execute();
}
$conn->commit();
header("Location: order_confirmation.php?order_id=$order_id");
exit();
?>