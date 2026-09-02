<?php 
include 'db.php'; 
session_start(); 

// Logic will only run if the user submits the form through 'POST'
if($_SERVER['REQUEST_METHOD'] == 'POST'){ 
    $email = $_POST['email']; 
    $password = $_POST['password'];

// Fetches the user record safely
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash,
    role FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 

//Checks if one user is found with the email
    if($result->num_rows == 1){ 
        $user = $result->fetch_assoc(); 

//Decrypts and compares the password against the hashed version the database
        if(password_verify($password, $user['password_hash'])){ 
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['full_name'] = $user['full_name']; 
            $_SESSION['role'] = $user['role']; 

//Takes the user to the correct dashboard based on their access level
            if($user['role'] == 'admin'){
                header("Location: admin_dashboard.php"); 
            } elseif($user['role'] == 'customer'){ 
                header("Location: customer_dashboard.php"); 
            } elseif($user['role'] == 'rider'){ 
                header("Location: rider_dashboard.php"); 
            } 
            exit(); 
//Error occurs if the email exists but the password verification fails
        } else { 
            $error_msg = "Incorrect password."; 
        }
// Error occurs if the email lookup returns zero results
    } else { 
        $error_msg = "Email not found."; 
    } 
} 
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <title>Login</title> 
    <link rel="stylesheet" href="style.css">
</head> 
<body> 

<div class="profile-box" style="max-width:400px; margin:50px auto;">
    <h1>Login</h1>

    <?php if(isset($error_msg)) echo "<div class='error'>$error_msg</div>"; ?>

    <form action="#" method="POST"> 
        <label>Email</label> 
        <input type="email" name="email" required> 

        <label>Password</label> 
        <input type="password" name="password" required> 

        <button type="submit">Login</button> 
    </form>

    <p style="text-align:center; margin-top:15px;">
        Don't have an account? <a href="signup.php">Sign up here</a>
    </p>
</div>

</body> 
</html>
