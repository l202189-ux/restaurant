<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ahmed's Local Spot</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

<nav>
    <a href="customers_menu.php">Menu</a>
    <a href="reviews.php">Reviews</a>
    <a href="login.php">Login</a>
    <a href="signup.php">Sign Up</a>
</nav>

<!-- HERO SECTION -->
<section class="hero">
    <h1>Ahmed's Local Spot</h1>
    <h2>Our Objective:</h2>
    <p>
        We aim to provide traditional, high-quality meals infused with classic Pakistani spices.
        Our restaurant focuses on customer satisfaction with a quick and welcoming dining experience for everyone.
    </p>
</section>

<!-- INFO CARDS SECTION -->
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