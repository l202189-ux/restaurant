<?php
include 'db.php';
session_start();

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

/* =========================
   HANDLE PROFILE UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = mysqli_real_escape_string($conn, $_POST['name']);

    // Update name for all users
    $conn->query("
        UPDATE Users
        SET full_name='$name'
        WHERE user_id=$user_id
    ");

    // Customers can save several addresses
    if ($role === 'customer') {
        $label = mysqli_real_escape_string($conn, $_POST['address_label'] ?? '');
        $text = mysqli_real_escape_string($conn, $_POST['address_text'] ?? '');
        $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
        $address_id = (int)($_POST['address_id'] ?? 0);

        if ($text !== '' && $city !== '') {
            if (isset($_POST['add_address']) || $address_id == 0) {
                $conn->query("INSERT INTO customeraddresses (customer_id, address_label, address_text, city) VALUES ($user_id, '$label', '$text', '$city')");
            } else {
                $conn->query("UPDATE customeraddresses SET address_label='$label', address_text='$text', city='$city' WHERE address_id=$address_id AND customer_id=$user_id");
            }
        }
    }

    $_SESSION['name'] = $name;
    $success_msg = "Profile updated successfully!";
}

/* =========================
   FETCH USER + ADDRESS
========================= */
$sql = "
SELECT 
    u.full_name,
    u.email,
    ca.address_label,
    ca.address_text,
    ca.city
FROM Users u
LEFT JOIN customeraddresses ca
    ON u.user_id = ca.customer_id
WHERE u.user_id = $user_id
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['full_name'];
    $email = $user['email'];
    $address_label = $user['address_label'] ?? '';
    $address_text  = $user['address_text'] ?? '';
    $city          = $user['city'] ?? '';
} else {
    $name = $email = $address_label = $address_text = $city = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Keeping your original colors and theme */
        
        .profile-box {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background: white; /* Keeping your background */
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Fixing the layout of where info goes in */
        .profile-box form {
            display: flex;
            flex-direction: column;
        }

        .profile-box label {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            display: block;
        }

        .profile-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box; /* This ensures the input fits inside the box */
            font-size: 1rem;
        }

        /* Making the button smaller and centered */
        .profile-box button {
            width: 150px; 
            margin: 30px auto 0 auto; /* Centers the button */
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            /* Colors will be inherited from your style.css or button tag */
        }

        .profile-box h3 {
            margin-top: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .msg {
            text-align: center;
            margin-bottom: 20px;
            color: green;
        }
    </style>
</head>
<body>

<nav style="text-align:center; padding: 20px;">
    <?php if ($role === 'customer'): ?>
        <a href="customer_dashboard.php">Dashboard</a>
    <?php elseif ($role === 'admin'): ?>
        <a href="admin_dashboard.php">Dashboard</a>
    <?php elseif ($role === 'rider'): ?>
        <a href="rider_dashboard.php">Dashboard</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</nav>

<div class="profile-box">
    <h1 style="text-align:center;">My Profile</h1>

    <?php if (isset($success_msg)) echo "<div class='msg'>$success_msg</div>"; ?>

    <form method="POST">

        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($email) ?>" readonly style="background-color: #f9f9f9; color: #777;">

        <label>Role</label>
        <input type="text" value="<?= ucfirst($role) ?>" readonly style="background-color: #f9f9f9; color: #777;">

        <?php if ($role === 'customer'): ?>
            <h3>Delivery Address</h3>
            <label>Saved Addresses</label>
            <select name="address_id" id="address_id" onchange="chooseAddress()" style="padding:10px; width:100%;">
                <option value="0">New address</option>
                <?php
                $saved = $conn->query("SELECT * FROM customeraddresses WHERE customer_id=$user_id ORDER BY address_id");
                while ($addr = $saved->fetch_assoc()): ?>
                    <option value="<?= $addr['address_id'] ?>"
                        data-label="<?= htmlspecialchars($addr['address_label'], ENT_QUOTES) ?>"
                        data-address="<?= htmlspecialchars($addr['address_text'], ENT_QUOTES) ?>"
                        data-city="<?= htmlspecialchars($addr['city'], ENT_QUOTES) ?>">
                        <?= htmlspecialchars($addr['address_label'] ?: 'Address') ?> - <?= htmlspecialchars($addr['city']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Address Label</label>
            <input type="text" name="address_label"
                   value="<?= htmlspecialchars($address_label) ?>"
                   placeholder="e.g. Home">

            <label>Street Address</label>
            <input type="text" name="address_text"
                   value="<?= htmlspecialchars($address_text) ?>"
                   placeholder="Street, area, house no." id="address_text" required>

            <label>City</label>
            <input type="text" name="city"
                   value="<?= htmlspecialchars($city) ?>" id="city" required>
        <?php endif; ?>

        <?php if ($role === 'rider'): ?>
            <label>Rider Status</label>
            <input type="text" value="Active" readonly style="background-color: #f9f9f9; color: #777;">
        <?php endif; ?>

        <button type="submit">Save Changes</button>
        <?php if ($role === 'customer'): ?>
            <button type="submit" name="add_address" value="1">Save as New Address</button>
        <?php endif; ?>
    </form>
</div>

<script>
function chooseAddress() {
    const selected = document.getElementById('address_id').selectedOptions[0];
    document.getElementById('address_label').value = selected.dataset.label || '';
    document.getElementById('address_text').value = selected.dataset.address || '';
    document.getElementById('city').value = selected.dataset.city || '';
}
<?php if ($role === 'customer'): ?>
document.getElementById('address_id').value = "<?= (int)($conn->query("SELECT address_id FROM customeraddresses WHERE customer_id=$user_id ORDER BY address_id LIMIT 1")->fetch_assoc()['address_id'] ?? 0) ?>";
chooseAddress();
<?php endif; ?>
</script>
</body>
</html>