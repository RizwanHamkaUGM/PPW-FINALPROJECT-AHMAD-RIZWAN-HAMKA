<?php
session_start();
include 'koneksi.php'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ?page=home"); 
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header("Location: ?page=logout"); 
    exit();
}

$categories_stmt = $pdo->query("SELECT id, name FROM product_categories ORDER BY name ASC");
$available_categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$stats_stmt = $pdo->prepare("SELECT
                                (SELECT COUNT(*) FROM products) AS total_products,
                                (SELECT COUNT(*) FROM orders) AS total_orders,
                                (SELECT COUNT(*) FROM users) AS total_users,
                                (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS pending_orders;");
$stats_stmt->execute();
$stats = ($stats_stmt->fetchAll(PDO::FETCH_ASSOC))[0];

$product_stmt = $pdo->prepare("
    SELECT
        p.id,
        p.name,
        p.price,
        p.stock,
        p.image_url,
        p.category_id,
        pc.name AS category_name,
        p.type
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    ORDER BY p.name ASC
");
$product_stmt->execute();
$raw_products_data = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

$products_for_display = [];
foreach ($raw_products_data as $item) {
    $products_for_display[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'price' => (int) $item['price'],
        'category_id' => $item['category_id'],
        'category_name' => $item['category_name'] ?? 'N/A', 
        'type' => $item['type'],
        'stock' => $item['stock'],
        'image_url' => $item['image_url']
    ];
}

$recent_orders_stmt = $pdo->prepare("
    SELECT
        o.id,
        o.total_price,
        o.status,
        o.order_date,
        u.name as user_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$recent_orders_stmt->execute();
$raw_recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

$recent_orders = [];
foreach ($raw_recent_orders as $item) {
    $recent_orders[] = [
        'id' => $item['id'],
        'user_name' => $item['user_name'],
        'total' => $item['total_price'],
        'status' => $item['status'],
        'date' => $item["order_date"]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_product':
                    if (empty($_POST['product_category_id']) || empty($_POST['product_type'])) {
                        throw new Exception("Category and Type are required.");
                    }
                    if (!in_array($_POST['product_type'], ['clothing', 'accessory'])) {
                        throw new Exception("Invalid product type.");
                    }

                    $insert_product = $pdo->prepare("INSERT INTO products (name, price, image_url, stock, category_id, type) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_product->execute([
                        $_POST['product_name'],
                        $_POST['product_price'],
                        $_POST['product_image'],
                        $_POST['product_stock'],
                        $_POST['product_category_id'], 
                        $_POST['product_type']         
                    ]);
                    
                    $_SESSION['message'] = "Product added successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: ?page=dashboard"); 
                    exit();

                case 'edit_product':
                    if (empty($_POST['product_category_id']) || empty($_POST['product_type'])) {
                        throw new Exception("Category and Type are required.");
                    }
                     if (!in_array($_POST['product_type'], ['clothing', 'accessory'])) {
                        throw new Exception("Invalid product type.");
                    }

                    if (!empty($_POST['product_image'])) {
                        $update_product = $pdo->prepare("UPDATE products SET name = ?, price = ?, image_url = ?, stock = ?, category_id = ?, type = ? WHERE id = ?");
                        $update_product->execute([
                            $_POST['product_name'],
                            $_POST['product_price'],
                            $_POST['product_image'],
                            $_POST['product_stock'],
                            $_POST['product_category_id'],
                            $_POST['product_type'], 
                            $_POST['product_id']
                        ]);
                    } else {
                        $update_product = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category_id = ?, type = ? WHERE id = ?");
                        $update_product->execute([
                            $_POST['product_name'],
                            $_POST['product_price'],
                            $_POST['product_stock'],
                            $_POST['product_category_id'], 
                            $_POST['product_type'],       
                            $_POST['product_id']
                        ]);
                    }
                    
                    $_SESSION['message'] = "Product updated successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: ?page=dashboard");
                    exit();

                case 'delete_product':
                    $delete_product = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $delete_product->execute([$_POST['product_id']]);
                    
                    $_SESSION['message'] = "Product deleted successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: ?page=dashboard");
                    exit();
            }
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: ?page=dashboard");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
    <link rel="stylesheet" href="assets/css/style-admin.css">
    <style>
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 1rem;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .products-table th, .products-table td {
            min-width: 80px; /* Adjust as needed */
        }
    </style>
</head>
<body>
    <?php
    if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] == 'admin')) {
        include 'components/header_admin.php'; 
    } else {
        header("Location: ?page=home"); 
        exit();
    }
    ?>

    <div class="dashboard-banner">
        <div class="banner-title">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($admin['name']) ?>!</p>
            <p><?= htmlspecialchars($admin['email']) ?></p>
        </div>
        <div class="dashboard-actions">
            <a href="#" class="dashboard-btn">Settings</a>
            <a href="?page=logout" class="dashboard-btn">Logout</a>
        </div>
    </div>

    <div class="orders-section">
        <div class="orders-container">
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_products'] ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_orders'] ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['pending_orders'] ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>
                <?php if (isset($_SESSION['message'])): ?>
                <div class="message <?= htmlspecialchars($_SESSION['message_type']) ?>">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>
                <div>
                    <h2 class="section-title">Product Management</h2>
                    <button class="action-btn" onclick="openAddProductModal()" style="width: 100%; margin-bottom: 10px;">Add New Product</button>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products_for_display as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="product-image">
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($product['category_name'])) ?></td>
                                <td><?= htmlspecialchars(ucfirst($product['type'])) ?></td>
                                <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                <td><?= $product['stock'] ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-small btn-edit" onclick="editProduct(<?= htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                                        <button class="btn-small btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div>
                    <div class="recent-orders">
                        <h3 class="section-title">Recent Orders</h3>
                        <?php foreach ($recent_orders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h4>Order #<?= $order['id'] ?></h4>
                                <p><?= htmlspecialchars($order['user_name']) ?></p>
                                <p><?= date('d M Y, H:i', strtotime($order['date'])) ?></p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                                <div style="font-size: 12px; margin-top: 4px;">
                                    Rp <?= number_format($order['total'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

            <!-- <div class="two-column">
                <div>
                    <h2 class="section-title">Product Management</h2>
                    <button class="action-btn" onclick="openAddProductModal()" style="width: 100%; margin-bottom: 10px;">Add New Product</button>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products_for_display as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="product-image">
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($product['category_name'])) ?></td>
                                <td><?= htmlspecialchars(ucfirst($product['type'])) ?></td>
                                <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                <td><?= $product['stock'] ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-small btn-edit" onclick="editProduct(<?= htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                                        <button class="btn-small btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div>
                    <div class="recent-orders">
                        <h3 class="section-title">Recent Orders</h3>
                        <?php foreach ($recent_orders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h4>Order #<?= $order['id'] ?></h4>
                                <p><?= htmlspecialchars($order['user_name']) ?></p>
                                <p><?= date('d M Y, H:i', strtotime($order['date'])) ?></p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                                <div style="font-size: 12px; margin-top: 4px;">
                                    Rp <?= number_format($order['total'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="quick-stats" style="margin-top: 20px;">
                        <h3 class="section-title">Quick Actions</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="action-btn" onclick="viewPendingOrders()">View Pending Orders</button>
                            <button class="action-btn secondary" onclick="exportData()">Export Data</button>
                            <button class="action-btn secondary" onclick="viewReports()">View Reports</button>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('addProductModal')">&times;</span>
            <h2 class="modal-title">Add New Product</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="product_price">Price (Rp)</label>
                    <input type="number" class="form-control" id="product_price" name="product_price" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="product_category_id">Category</label>
                    <select class="form-control" id="product_category_id" name="product_category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($available_categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars(ucfirst($category['name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="product_type">Product Type</label>
                    <select class="form-control" id="product_type" name="product_type" required>
                        <option value="">Select Type</option>
                        <option value="clothing">Clothing</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_stock">Stock</label>
                    <input type="number" class="form-control" id="product_stock" name="product_stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image URL</label>
                    <input type="url" class="form-control" id="product_image" name="product_image" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('addProductModal')">Cancel</button>
                    <button type="submit" class="btn-save">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="editProductModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('editProductModal')">&times;</span>
            <h2 class="modal-title">Edit Product</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="form-group">
                    <label for="edit_product_name">Product Name</label>
                    <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_price">Price (Rp)</label>
                    <input type="number" class="form-control" id="edit_product_price" name="product_price" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_category_id">Category</label>
                    <select class="form-control" id="edit_product_category_id" name="product_category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($available_categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars(ucfirst($category['name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_product_type">Product Type</label>
                    <select class="form-control" id="edit_product_type" name="product_type" required>
                        <option value="">Select Type</option>
                        <option value="clothing">Clothing</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_stock">Stock</label>
                    <input type="number" class="form-control" id="edit_product_stock" name="product_stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_image">Product Image URL (leave empty to keep current)</label>
                    <input type="text" class="form-control" id="edit_product_image" name="product_image">
                </div>

                <div class="form-group" style="display:none;"> <label for="edit_product_description">Description</label> 
                    <textarea class="form-control" id="edit_product_description" name="product_description_dummy" rows="4"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('editProductModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div>
            <p>&copy; 2025 zwnzs. All rights reserved.</p>
        </div>
        <div>
            <p>Made with ❤️</p>
        </div>
    </footer>

    <script>
        function openAddProductModal() {
            document.getElementById('addProductModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editProduct(product) { 
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_product_name').value = product.name;
            document.getElementById('edit_product_price').value = product.price;
            document.getElementById('edit_product_category_id').value = product.category_id; 
            document.getElementById('edit_product_type').value = product.type; 
            document.getElementById('edit_product_stock').value = product.stock;
            document.getElementById('edit_product_image').value = ''; 
            document.getElementById('edit_product_image').placeholder = product.image_url; 


            document.getElementById('editProductModal').style.display = 'block';
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_product';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'product_id';
                idInput.value = productId;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }



        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>