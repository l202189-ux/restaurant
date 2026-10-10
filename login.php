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

    <style>
        /* Login error pop-up */
        .login-error-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(62, 39, 35, 0.55);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .login-error-popup {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 380px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .login-error-popup h2 {
            color: #3E2723;
            margin-top: 10px;
        }

        .login-error-popup p {
            color: #5D4037;
            margin: 15px 0 20px;
        }

        .login-error-icon {
            background: #D7CCC8;
            color: #3E2723;
            width: 45px;
            height: 45px;
            line-height: 45px;
            border-radius: 50%;
            margin: 0 auto;
            font-size: 25px;
            font-weight: bold;
        }

        .login-error-popup button {
            margin-top: 5px;
        }
    </style>
</head> 
<body> 

<div class="profile-box" style="max-width:400px; margin:50px auto;">
    <h1>Login</h1>



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


<?php if(isset($error_msg)): ?>
    <div class="login-error-overlay" id="login-error-popup">
        <div class="login-error-popup" role="alertdialog" aria-modal="true" aria-labelledby="login-error-title">
            <div class="login-error-icon">!</div>
            <h2 id="login-error-title">Login Failed</h2>
            <p><?= htmlspecialchars($error_msg) ?></p>
            <button type="button" onclick="document.getElementById('login-error-popup').style.display='none';">Try Again</button>
        </div>
    </div>
<?php endif; ?>

</body> 
</html>
