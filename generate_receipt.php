<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    die('Unauthorized');
}

$order_id = (int)$_GET['order_id'];
$customer_id = (int)$_SESSION['user_id'];

// 1. Fetch Order Info - Using prepared statements is safer, but keeping your query style for now
$order_q = $conn->query("SELECT o.order_id, o.order_timestamp, u.full_name 
                         FROM Orders o JOIN Users u ON o.customer_id = u.user_id 
                         WHERE o.order_id = $order_id AND o.customer_id = $customer_id");
$order = $order_q->fetch_assoc();

if (!$order) {
    die('Order not found or access denied.');
}

// 2. Fetch Items
$items_q = $conn->query("SELECT d.dish_name, oi.quantity, oi.line_total 
                         FROM OrderItems oi JOIN Dishes d ON oi.dish_id = d.dish_id 
                         WHERE oi.order_id = $order_id");

$items = [];
$grand_total = 0;
while($row = $items_q->fetch_assoc()) {
    $items[] = $row;
    $grand_total += $row['line_total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generating Receipt...</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <div style="text-align: center; margin-top: 50px; font-family: sans-serif;">
        <p>Generating your PDF, please wait...</p>
    </div>

    <script>
        // Use htmlspecialchars to prevent quotes from breaking JS strings
        const orderInfo = {
            id: "<?= $order['order_id'] ?>",
            name: "<?= htmlspecialchars($order['full_name'], ENT_QUOTES) ?>",
            date: "<?= date('d M Y', strtotime($order['order_timestamp'])) ?>",
            total: "<?= number_format($grand_total, 2) ?>"
        };

        const orderItems = [
            <?php foreach ($items as $item): ?>
            ["<?= htmlspecialchars($item['dish_name'], ENT_QUOTES) ?>", "<?= $item['quantity'] ?>", "Rs. <?= number_format($item['line_total'], 2) ?>"],
            <?php endforeach; ?>
        ];

        window.onload = function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Define your brand color (Brown)
            const brandColor = [101, 67, 33]; 

            // Receipt Header
            doc.setFontSize(22);
            doc.setTextColor(brandColor[0], brandColor[1], brandColor[2]); 
            doc.text("Ahmed's Local Spot", 105, 20, { align: "center" });

            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0); // Reset to black
            doc.text(`Official Receipt: #${orderInfo.id}`, 20, 40);
            doc.text(`Customer: ${orderInfo.name}`, 20, 48);
            doc.text(`Date: ${orderInfo.date}`, 20, 56);

            // Generate Table
            doc.autoTable({
                startY: 65,
                head: [['Dish Name', 'Quantity', 'Amount']],
                body: orderItems,
                theme: 'striped',
                headStyles: { fillColor: brandColor }
            });

            // Total Section
            const finalY = doc.lastAutoTable.finalY;
            doc.setFont("helvetica", "bold");
            // Align the total to the right side
            doc.text(`Total Amount Paid: Rs. ${orderInfo.total}`, 190, finalY + 15, { align: "right" });

            // Footer
            doc.setFontSize(10);
            doc.setFont("helvetica", "italic");
            doc.setTextColor(100); // Gray
            doc.text("Thank you for your order! This is a computer-generated receipt.", 105, finalY + 30, { align: "center" });

            // Save PDF
            doc.save(`Receipt_Order_${orderInfo.id}.pdf`);
            
            // Redirect after download
            setTimeout(() => {
                window.location.href = 'customer_orders.php';
            }, 2500);
        };
    </script>
</body>
</html>