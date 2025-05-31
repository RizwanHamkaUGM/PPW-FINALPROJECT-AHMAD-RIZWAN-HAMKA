<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: ?page=login");
    exit();
}

include 'koneksi.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data keranjang
$cartStmt = $pdo->prepare("
    SELECT 
        ci.id as cart_id,
        ci.quantity,
        ci.added_at,
        p.id as product_id,
        p.name as product_name,
        p.image_url,
        p.price,
        pc.name as category_name
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE ci.user_id = ?
    ORDER BY ci.added_at DESC
");
$cartStmt->execute([$user_id]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);


$orderStmt = $pdo->prepare("
    SELECT 
        o.id as order_id,
        o.order_date,
        o.total_price,
        o.status,
        p.name as product_name,
        p.image_url,
        p.price,
        oi.quantity,
        oi.item_price,
        pc.name as category_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$orderStmt->execute([$user_id]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

$groupedOrders = [];
foreach ($orders as $order) {
    $groupedOrders[$order['order_id']]['info'] = [
        'order_date' => $order['order_date'],
        'total_price' => $order['total_price'],
        'status' => $order['status']
    ];
    $groupedOrders[$order['order_id']]['items'][] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
</head>
<body>
    <?php
    if (isset($_SESSION['user_id'])) {
        // dd($_SESSION);
        include 'components/header_login.php';
    } else {
        include 'components/header.php';
    }
    ?>
    <!-- Cart Section -->
<div class="orders-section">
    <div class="orders-container">
        <div class="orders-header">
            <div class="orders-title">Your Cart</div>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="no-orders">
                <h3>Cart is Empty</h3>
                <p>You haven't added any items to your cart.</p>
            </div>
        <?php else: ?>
            <div class="order-card-container">
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h4>Pending Checkout</h4>
                            <div class="order-date"><?= date('d M Y, H:i') ?> (Latest Update)</div>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-cart">Cart</span>
                            <div class="order-total">
                                Rp <?= number_format(array_sum(array_map(function($item) {
                                    return $item['price'] * $item['quantity'];
                                }, $cartItems)), 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                         class="item-image">
                                <?php else: ?>
                                    <div class="item-image" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                <div class="item-details">
                                    <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="item-category"><?= htmlspecialchars($item['category_name']) ?></div>
                                    <div class="item-quantity">Qty: <?= $item['quantity'] ?></div>
                                </div>
                                <div class="item-price">
                                    <div>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title">Edit Profile</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                            value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                            value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                            value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
        function openEditModal() {
            document.getElementById('editProfileModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>