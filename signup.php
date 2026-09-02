<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $role = 'customer';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO Users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $full_name, $email, $password_hash, $role);

    if ($stmt->execute()) {
        $success_msg = "Signup successful! <a href='login.php'>Login here</a>";
    } else {
        $error_msg = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="profile-box" style="max-width:400px; margin:50px auto;">
    <h1>Sign Up</h1>

    <?php if(isset($success_msg)) echo "<div class='msg'>$success_msg</div>"; ?>
    <?php if(isset($error_msg)) echo "<div class='error'>$error_msg</div>"; ?>

    <form action="#" method="post">
        <label>Full Name</label>
        <input type="text" name="fullname" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Sign Up</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

</body>
</html>
