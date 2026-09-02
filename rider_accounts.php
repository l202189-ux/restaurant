<?php
include 'db.php';
session_start();

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Add new rider
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_rider'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = 'rider';

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

// Prepare a parameterized SQL statement to securely insert user data
    $stmt = $conn->prepare("INSERT INTO Users (full_name, email, password_hash, role) 
    VALUES (?, ?, ?, ?)");

// Bind user input values to the prepared statement
    $stmt->bind_param("ssss", $full_name, $email, $password_hash, $role);

// Execute the prepared statement
    if ($stmt->execute()) {
        $success = "Rider added successfully!";
    } else {
// Error message displayed if execution fails
        $error = "Error: " . $stmt->error;
    }
}

if (isset($_GET['delete'])) {
    // ... deletion logic ...
    header("Location: manage_riders.php"); // Must match your filename
    exit();
}

// Fetch all riders
$riders = $conn->query("SELECT user_id, full_name, email FROM Users WHERE role='rider'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Riders</title>
    <style>
        body { 
            background-color: #D7CCC8; 
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: #3E2723;
        }

        /* Nav Bar Style to match your image */
        nav.admin-nav {
            background-color: #3E2723;
            padding: 15px 0;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        nav.admin-nav a {
            color: #ffffff;
            background-color: #5D4037;
            text-decoration: none;
            font-weight: 700;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2 { text-align: center; text-transform: uppercase; letter-spacing: 1px; }

        /* Compact Form Box */
        .form-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 0 auto 40px auto;
        }

        .form-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-box button {
            width: 100%;
            background: #3E2723;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Compact Table Styling */
        .table-container {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            background-color: #F5F5F5;
            color: #5D4037;
            padding: 12px;
            border-bottom: 2px solid #D7CCC8;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #F0F0F0;
            text-align: center;
        }

        /* Small Delete Button */
        .btn-delete {
            background-color: #d9534f;
            color: white;
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .btn-delete:hover { background-color: #c9302c; }

        .msg { color: green; text-align: center; font-weight: bold; }
        .error { color: red; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="admin-nav">
        <a href="admin_dashboard.php">Dashboard</a>
        </nav>

    <div class="container">
        <h1>Rider Accounts</h1>

        <?php if(isset($success)) echo "<p class='msg'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

        <div class="form-box">
            <h2>Add Rider</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_rider" value="1">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Add Rider</button>
            </form>
        </div>

        <h2>Existing Riders</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($rider = $riders->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rider['user_id'] ?></td>
                        <td><?= htmlspecialchars($rider['full_name']) ?></td>
                        <td><?= htmlspecialchars($rider['email']) ?></td>
                        <td>
                            <a href="manage_riders.php?delete=<?= $rider['user_id'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Delete this rider?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>