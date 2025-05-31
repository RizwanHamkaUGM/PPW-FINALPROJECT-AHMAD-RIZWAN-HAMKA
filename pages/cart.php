<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: ?page=login");
    exit();
}

include 'koneksi.php'; // Ensure this file correctly sets $host, $database, $user, $password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Handle cart actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $cart_id = (int)$_POST['cart_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
                    $updateStmt->execute([$quantity, $cart_id, $user_id]);
                    $_SESSION['success_message'] = "Quantity updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Quantity must be greater than 0.";
                }
                break;
                
            case 'delete_item':
                $cart_id = (int)$_POST['cart_id'];
                
                $deleteStmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
                $deleteStmt->execute([$cart_id, $user_id]);
                $_SESSION['success_message'] = "Item removed from cart!";
                break;
                
            case 'update_profile': // This case seems out of place for cart.php, but kept as is from original
                $name = $_POST['name'];
                $email = $_POST['email'];
                $phone_number = $_POST['phone_number'];
                
                $updateUserStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ?");
                $updateUserStmt->execute([$name, $email, $phone_number, $user_id]);
                $_SESSION['success_message'] = "Profile updated successfully!";
                break;
        }
        
        // Redirect to prevent form resubmission
        header("Location: ?page=cart");
        exit();
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - <?= htmlspecialchars($user['name'] ?? 'User') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
    <style>
        /* Basic Reset and Sticky Footer Styles */
        html {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Minimum height of the viewport */
            margin: 0; /* Remove default body margin */
            font-family: 'Poppins', sans-serif; /* Set global font */
        }
        .page-content-wrapper { /* New wrapper for main content */
            flex-grow: 1; /* Allows this element to take up available space */
        }
        /* End Sticky Footer Styles */

        /* Additional styles for cart actions (some might be superseded by modal) */
        .item-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
        
        .delete-btn, .edit-qty-btn { /* Combined style for similar buttons */
            background: transparent;
            color: #000;
            border: 1px solid #000;
            padding: 5px 10px;
            border-radius: 0; /* Match existing style */
            cursor: pointer;
            font-size: 12px;
            font-family: 'Poppins', sans-serif; /* Explicitly set for buttons if needed */
            transition: all 0.3s ease;
        }
        
        .delete-btn:hover, .edit-qty-btn:hover {
            background: #000;
            color: white;
        }

        .checkout-btn {
            display: inline-block; 
            background: #000;
            color: white    ;
            border: 1px solid #000;
            padding: 5px 10px;
            border-radius: 0;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Poppins', sans-serif; /* Explicitly set for buttons if needed */
            transition: all 0.3s ease;
            text-decoration: none;
            margin-top: 15px; 
        }
        .checkout-btn:hover {
            background: rgb(80, 80, 80);
            border: 1px solid rgb(80, 80, 80);
            color: white;
        }

        .checkout-button-container {
            text-align: right; 
            margin-top: 10px; 
        }

        .alert {
            padding: 15px;
            margin: 20px auto; 
            border-radius: 0; 
            font-weight: 400;
            /* font-family: 'Poppins', sans-serif; /* Inherits from body */
            width: 90%; 
            box-sizing: border-box;
        }
        
        .alert-success {
            background-color: #f8f9fa; 
            color: #000;
            border: 1px solid #000;
        }
        
        .alert-error {
            background-color: #f8f9fa; 
            color: #000; 
            border: 1px solid #000; 
        }
    </style>
</head>
<body>
    <?php
    if (isset($_SESSION['user_id'])) {
        include 'components/header_login.php'; // Assumed to output <header> or similar block
    } else {
        include 'components/header.php'; // Assumed to output <header> or similar block
    }
    ?>

    <div class="page-content-wrapper">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
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
                                    <div class="order-date"><?= date('d M Y, H:i') ?> (Cart View)</div>
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
                                            <div class="item-image" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; font-size:10px; text-align:center;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="item-category"><?= htmlspecialchars($item['category_name']) ?></div>
                                            <div class="item-quantity">Current Qty: <?= $item['quantity'] ?></div>
                                            
                                            <div class="item-actions">
                                                <button type="button" class="edit-qty-btn" onclick="openUpdateModal(<?= $item['cart_id'] ?>, <?= $item['quantity'] ?>, '<?= htmlspecialchars(addslashes($item['product_name'])) ?>')">Edit Qty</button>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this item from your cart?');">
                                                    <input type="hidden" name="action" value="delete_item">
                                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                    <button type="submit" class="delete-btn">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="item-price">
                                            <div>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="checkout-button-container">
                                    <a class="checkout-btn" href="?page=checkout-orders">Checkout Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> <div id="updateQuantityModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('updateQuantityModal')">&times;</span>
            <h3 id="updateModalTitle" class="modal-title">Update Quantity</h3>
            <form method="POST" id="updateQuantityForm">
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" name="cart_id" id="modal_cart_id">
                
                <div class="form-group">
                    <label for="modal_quantity">Quantity:</label>
                    <input type="number" name="quantity" id="modal_quantity" class="form-control" min="1" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('updateQuantityModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Quantity</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; // Assumed to output <footer> or similar block ?>

    <script>
        const updateQuantityModal = document.getElementById('updateQuantityModal');
        const modalCartIdInput = document.getElementById('modal_cart_id');
        const modalQuantityInput = document.getElementById('modal_quantity');
        const updateModalTitle = document.getElementById('updateModalTitle');

        function openUpdateModal(cartId, currentQuantity, productName) {
            modalCartIdInput.value = cartId;
            modalQuantityInput.value = currentQuantity;
            updateModalTitle.textContent = 'Update Quantity for: ' + productName;
            updateQuantityModal.style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == updateQuantityModal) {
                updateQuantityModal.style.display = 'none';
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