<?php
session_start();
include 'db.php';

// Check login & role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Cache control
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Check if customer has an address
$address_check = $conn->query("
    SELECT address_id
    FROM customeraddresses
    WHERE customer_id = {$_SESSION['user_id']}
    LIMIT 1
");

$has_address = ($address_check && $address_check->num_rows > 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS to center and style the address alert */
        .address-alert-container {
            max-width: 800px;
            margin: 20px auto 0; /* Centers horizontally, adds space from top */
            padding: 0 20px;
        }

        .address-alert {
            color: #c70e0e;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .address-alert a {
            background-color: #584d4d;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            transition: opacity 0.3s;
        }

        .address-alert a:hover {
            opacity: 0.8;
        }

        /* Ensuring Hero section doesn't push too far up */
        .hero {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<nav>
    <a href="customers_menu.php">Menu</a>
    <a href="customer_orders.php">My Orders</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<?php if (!$has_address): ?>
    <div class="address-alert-container">
        <div class="address-alert">
            <span><strong>Missing Delivery Address:</strong> Delivery address is required to place orders.</span>
            <a href="profile.php">Add Address Now</a>
        </div>
    </div>
<?php endif; ?>

<section class="hero">
    <h1>Ahmed's Local Spot</h1>
    <h2>Our Objective:</h2>
    <p>
        We aim to provide traditional, high-quality meals infused with classic Pakistani spices.
        Our restaurant focuses on customer satisfaction with a quick and welcoming dining experience for everyone.
    </p>
</section>

<section class="info-cards">
    <div class="card">
        <h3>Fresh Ingredients</h3>
        <p>All dishes are prepared daily using fresh and locally sourced ingredients.</p>
    </div>

    <div class="card">
        <h3>Fast Delivery</h3>
        <p>Our trained riders ensure timely and safe delivery of every order.</p>
    </div>

    <div class="card">
        <h3>Customer Satisfaction</h3>
        <p>We prioritize hygiene, quality, and customer feedback.</p>
    </div>
</section>

</body>
</html>