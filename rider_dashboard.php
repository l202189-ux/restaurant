<?php
// Resumes the user's session to access their saved "passport" data
session_start();

// This will only allow riders
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'rider'){
    header("Location: login.php");
    exit();
}

// This prevents browser caching from occuring 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard</title>
    <style>
        /* GLOBAL BACKGROUND (Little Blue) */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #c4bdb4; /* Your light blue background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* LIGHT BROWN CONTAINER (Widened) */
        .dashboard-container {
            padding: 50px;
            border-radius: 12px;
            text-align: center;
            width: 550px; /* Made wider to match admin */
        }

        h1 {
            color: #5D4037;
            font-size: 1.8rem;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* DARK BROWN BOX (Soft Corners) */
        nav {
            background-color: #5D4037; /* The dark brown part */
            padding: 50px;
            border-radius: 20px; /* Softer, rounded corners */
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            width: 50%;        /* Narrower dark box */
            margin: 0 auto;    /* Centered */
        }

        /* BUTTONS (One Size / Uniform) */
        nav a {
            background-color: #8D6E63; /* Muted brown buttons */
            color: #ffffff;
            text-decoration: none;
            padding: 12px 0;
            border-radius: 8px;
            font-weight: bold;
            width: 160px; /* Fixed width for uniformity */
            display: block;
            transition: all 0.3s ease;
        }

        /* HOVER EFFECT (Blue Accent) */
        nav a:hover {
            background-color: #1A365D; /* Dark Blue accent */
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>Rider Dashboard</h1>

    <nav>
        <a href="rider_activeorders.php">Active Orders</a>
        <a href="rider_deliverystatus.php">Delivery History</a>
        <a href="profile.php">My Profile</a>
        <a href="logout.php">Log Out</a>
    </nav>
</div>

</body>
</html>