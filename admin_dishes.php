<?php
// --- START OF DOWNLOAD LOGIC ---
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dishes_template.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['dish_name', 'price', 'description', 'image_path', 'availability']);
    fputcsv($output, ['Chicken Shawarma', '12.50', 'Marinated chicken with garlic sauce', 'shawarma.jpg', 'available']);
    fclose($output);
    exit();
}
// --- END OF DOWNLOAD LOGIC ---

include 'db.php';
session_start();

// Only allow admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("location: login.php");
    exit();
}

$error_msg = "";
$csv_error_msg = "";

// Handles CSV upload matching DB columns
if(isset($_POST['upload_csv'])){
    if(isset($_FILES['csv_file']) && $_FILES['csv_file']['size'] > 0){
        $fileName = $_FILES['csv_file']['tmp_name'];

// Opens a temporary file stream in read-only mode
        $file = fopen($fileName, "r");
        $rowCount = 0;
        
// Uses a while loop to parse each row of the CSV into an array 
        while(($column = fgetcsv($file, 1000, ",")) !== FALSE){
            $rowCount++;
// A logical gate used to skip the first row to prevent DB errors 
            if($rowCount == 1) continue; // skip header

// Sanitizes string inputs to prevent character breakages in the SQL string
            $dish_name   = mysqli_real_escape_string($conn, $column[0]);
            $price       = mysqli_real_escape_string($conn, $column[1]);
            $description = mysqli_real_escape_string($conn, $column[2]);
            $image_path  = mysqli_real_escape_string($conn, $column[3]);
            $availability= mysqli_real_escape_string($conn, $column[4]);

// Separates the SQL template from the data to prevent SQL Injection
            $stmt = $conn->prepare("INSERT INTO Dishes 
            (dish_name, price, description, image_path, availability) VALUES (?, ?, ?, ?, ?)");
//Binds parameters: "s" for string, "d" for double/decimal
            $stmt->bind_param("sdsss", $dish_name, $price, 
            $description, $image_path, $availability);
//Executes statement
            $stmt->execute();
        }
        fclose($file);
        
// Storing success feedback in a session variable to survive the page redirect
        $_SESSION['success_flash'] = "CSV Imported Successfully! Total rows: ".
        ($rowCount-1);
        header("Location: admin_dishes.php");
        exit();
    } else {
        $csv_error_msg = "Please select a CSV file.";
    }
}

// ----------------------
// Add a new dish (Manual)
// ----------------------
if(isset($_POST['add_dish'])){
    $dish_name = trim($_POST['dish_name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $availability = $_POST['availability'];

    if(empty($dish_name) || empty($description) || !is_numeric($price) || $price <= 0){
        $error_msg = "Please fill in all fields with valid data.";
    } else {
        $image = null;
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
            $target_dir = "images/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image = basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
        }

        $stmt = $conn->prepare("INSERT INTO Dishes (dish_name, description, price, availability, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $dish_name, $description, $price, $availability, $image);
        if($stmt->execute()){
            // REDIRECT AFTER MANUAL ADD
            $_SESSION['success_flash'] = "Dish added successfully.";
            header("Location: admin_dishes.php");
            exit();
        } else {
            $error_msg = "Failed to add dish.";
        }
    }
}

// ----------------------
// Update a dish
// ----------------------
if(isset($_POST['edit_dish'])){
    $id = intval($_POST['dish_id']);
    $dish_name = trim($_POST['dish_name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("UPDATE Dishes SET dish_name=?, description=?, price=?, availability=? WHERE dish_id=?");
    $stmt->bind_param("ssdsi", $dish_name, $description, $price, $availability, $id);
    if($stmt->execute()){
        $_SESSION['success_flash'] = "Dish updated successfully.";
        header("Location: admin_dishes.php");
        exit();
    }
}

// ----------------------
// Apply/update discount
// ----------------------
if(isset($_POST['apply_discount'])){
    $dish_id = intval($_POST['dish_id']);
    $discount_type = $_POST['discount_type'];
    $discount_value = $_POST['discount_value'];

    $res = $conn->query("SELECT * FROM Discounts WHERE dish_id=$dish_id");
    if($res->num_rows > 0){
        $stmt = $conn->prepare("UPDATE Discounts SET discount_type=?, discount_value=? WHERE dish_id=?");
        $stmt->bind_param("sdi", $discount_type, $discount_value, $dish_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO Discounts (dish_id, discount_type, discount_value) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $dish_id, $discount_type, $discount_value);
    }
    if($stmt->execute()){
        $_SESSION['success_flash'] = "Discount applied successfully.";
        header("Location: admin_dishes.php");
        exit();
    }
}

$dishes = $conn->query("SELECT d.*, dis.discount_type, dis.discount_value FROM Dishes d LEFT JOIN Discounts dis ON d.dish_id = dis.dish_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dishes Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .forms-wrapper { display: flex; justify-content: space-between; gap: 20px; max-width: 1100px; margin: 20px auto; align-items: flex-start; }
        .add-dish-box { flex: 2; background: #D7CCC8; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .csv-upload-box { flex: 1; background: #5D4037; color: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn-primary { background-color: #1A365D; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 15px; font-weight: bold; }
        .table-container { max-width: 1100px; margin: 40px auto; }
        .custom-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .custom-table th { background-color: #5D4037; color: white; padding: 15px; text-transform: uppercase; font-size: 0.85rem; }
        .custom-table td { padding: 15px; border-bottom: 1px solid #f2f2f2; }
        .update-btn { background: #5D4037; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        textarea { resize: none; }
    </style>
</head>
<body>

<h1 style="text-align:center; color: #5D4037;">Admin Dishes Management</h1>

<nav style="text-align:center; margin:20px;">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_dishes.php">Manage Dishes</a>
    <a href="rider_accounts.php">Rider Accounts</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<div style="text-align: center;">
    <?php 
    // Show session message then clear it
    if(isset($_SESSION['success_flash'])){
        echo "<p style='color:green; font-weight:bold;'>".$_SESSION['success_flash']."</p>";
        unset($_SESSION['success_flash']);
    }
    if($csv_error_msg) echo "<p style='color:red;'>$csv_error_msg</p>";
    if($error_msg) echo "<p style='color:red;'>$error_msg</p>";
    ?>
</div>

<div class="forms-wrapper">
    <div class="add-dish-box">
        <h2 style="margin-top:0;">Add New Dish</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display:flex; gap: 10px;">
                <div style="flex:2;"><label>Name:</label><input type="text" name="dish_name" required></div>
                <div style="flex:1;"><label>Price:</label><input type="number" step="0.01" name="price" required></div>
            </div>
            <label>Description:</label><textarea name="description" rows="3" required></textarea>
            <div style="display:flex; gap: 10px;">
                <div style="flex:1;"><label>Availability:</label>
                    <select name="availability"><option value="available">Available</option><option value="unavailable">Unavailable</option></select>
                </div>
                <div style="flex:1;"><label>Image:</label><input type="file" name="image"></div>
            </div>
            <button type="submit" name="add_dish" class="btn-primary">Add New Dish</button>
        </form>
    </div>

    <div class="csv-upload-box">
        <h2 style="margin-top:0; color: #D7CCC8;">CSV Bulk Upload</h2>
        <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px dashed rgba(215, 204, 200, 0.3);">
            <a href="?download_template=1" style="color: #fff; font-weight: bold; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <span style="background: #8D6E63; padding: 2px 6px; border-radius: 4px;"></span> Download CSV Template
            </a>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required style="color: white; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; width: 100%;">
            <button type="submit" name="upload_csv" class="btn-primary" style="background: #D7CCC8; color: #5D4037;">Upload CSV</button>
        </form>
    </div>
</div>

<div class="table-container">
    <h2 style="text-align:center;">Existing Dishes</h2>
    <table class="custom-table">
        <thead>
            <tr>
                <th>Dish</th>
                <th>Details</th>
                <th>Pricing & Discount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while($dish = $dishes->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <input type="hidden" name="dish_id" value="<?= $dish['dish_id'] ?>">
                    <td style="width: 150px;">
                        <?php if($dish['image_path']): ?>
                            <img src="images/<?= $dish['image_path'] ?>" style="width:100px; height:70px; object-fit:cover; border-radius:5px; display:block; margin-bottom:5px;">
                        <?php endif; ?>
                        <input type="text" name="dish_name" value="<?= htmlspecialchars($dish['dish_name']) ?>">
                    </td>
                    <td>
                        <textarea name="description" style="height: 60px;"><?= htmlspecialchars($dish['description']) ?></textarea>
                        <select name="availability">
                            <option value="available" <?= $dish['availability']=='available'?'selected':'' ?>>Available</option>
                            <option value="unavailable" <?= $dish['availability']=='unavailable'?'selected':'' ?>>Unavailable</option>
                        </select>
                    </td>
                    <td style="width: 200px;">
                        <label>Price:</label><input type="number" step="0.01" name="price" value="<?= $dish['price'] ?>">
                        <div style="margin-top:10px; background: #f9f9f9; padding: 5px; border-radius: 5px;">
                            <small><b>Discount:</b> <?= $dish['discount_type'] ? $dish['discount_value'].' ('.$dish['discount_type'].')' : "None" ?></small>
                            <div style="display:flex; gap:5px; margin-top:5px;">
                                <select name="discount_type" style="padding:2px; font-size:0.8rem;"><option value="percentage">%</option><option value="flat">$</option></select>
                                <input type="number" step="0.01" name="discount_value" placeholder="Val" style="padding:2px; font-size:0.8rem;">
                                <button type="submit" name="apply_discount" class="update-btn" style="padding: 2px 5px;">Apply</button>
                            </div>
                        </div>
                    </td>
                    <td><button type="submit" name="edit_dish" class="btn-primary" style="padding: 5px 10px; width: auto; font-size: 0.8rem;">Update</button></td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>