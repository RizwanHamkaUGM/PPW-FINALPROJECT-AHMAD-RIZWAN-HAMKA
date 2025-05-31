<?php
// Placeholder untuk session management dan database connection
session_start();
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
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// dd($user["name"]);


$stats_stmt = $pdo->prepare("SELECT
                                (SELECT COUNT(*) FROM products) AS total_products,
                                (SELECT COUNT(*) FROM orders) AS total_orders,
                                (SELECT COUNT(*) FROM users) AS total_users,
                                (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS pending_orders;");
$stats_stmt->execute();
$stats = ($stats_stmt->fetchAll(PDO::FETCH_ASSOC))[0];
// dd($stats);

$product_stmt = $pdo->prepare("
    SELECT
        p.id,
        p.name,
        p.price,
        p.stock,
        p.image_url,
        c.name AS category
    FROM products p
    JOIN product_categories c ON p.category_id = c.id
");
$product_stmt->execute();
$raw_products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

$products = [];

foreach ($raw_products as $item) {
    $products[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'price' => (int) $item['price'],
        'category' => $item['category'],
        'stock' => $item['stock'],
        'image_url' => $item['image_url']
    ];
}

// dd($products);

$recent_orders = [
    [
        'id' => 1,
        'user_name' => 'John Doe',
        'total' => 350000,
        'status' => 'pending',
        'date' => '2024-12-01 10:30:00'
    ],
    [
        'id' => 2,
        'user_name' => 'Jane Smith',
        'total' => 450000,
        'status' => 'completed',
        'date' => '2024-12-01 09:15:00'
    ]
];
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
</head>
<body>
    <?php
    if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] == 'admin')) {
        include 'components/header_admin.php';
    } else {
        header("Location: ?page=home");
    }
    ?>

    <!-- Dashboard Banner -->
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

    <!-- Main Dashboard Content -->
    <div class="orders-section">
        <div class="orders-container">
            <!-- Statistics Cards -->
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

            <!-- Action Buttons -->
            <!-- <div class="admin-actions">
                <button class="action-btn" onclick="openAddProductModal()">Add New Product</button>
                <a href="#" class="action-btn secondary">Manage Categories</a>
                <a href="#" class="action-btn secondary">View All Orders</a>
                <a href="#" class="action-btn secondary">User Management</a>
                <a href="#" class="action-btn secondary">Reports</a>
                <a href="#" class="action-btn secondary">Settings</a>
            </div> -->

            <!-- Two Column Layout -->
            <div class="two-column">
                <!-- Products Management -->
                <div>
                    <h2 class="section-title">Product Management</h2>
                    <button class="action-btn" onclick="openAddProductModal()" style="width: 100%; margin-bottom: 10px;">Add New Product</button>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                            alt="<?= htmlspecialchars($product['name']) ?>" 
                                            class="product-image">
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                <td><?= $product['stock'] ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-small btn-edit" onclick="editProduct(<?= $product['id'] ?>)">Edit</button>
                                        <button class="btn-small btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Recent Orders -->
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
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                                <div style="font-size: 12px; margin-top: 4px;">
                                    Rp <?= number_format($order['total'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-stats" style="margin-top: 20px;">
                        <h3 class="section-title">Quick Actions</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="action-btn" onclick="viewPendingOrders()">View Pending Orders</button>
                            <button class="action-btn secondary" onclick="exportData()">Export Data</button>
                            <button class="action-btn secondary" onclick="viewReports()">View Reports</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('addProductModal')">&times;</span>
            <h2 class="modal-title">Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description</label>
                    <textarea class="form-control" id="product_description" name="product_description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="product_price">Price (Rp)</label>
                    <input type="number" class="form-control" id="product_price" name="product_price" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="product_category">Category</label>
                    <select class="form-control" id="product_category" name="product_category" required>
                        <option value="">Select Category</option>
                        <option value="clothing">Clothing</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_stock">Stock</label>
                    <input type="number" class="form-control" id="product_stock" name="product_stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <input type="text" class="form-control" id="product_image" name="product_image" accept="image/*">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('addProductModal')">Cancel</button>
                    <button type="submit" class="btn-save">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal" id="editProductModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('editProductModal')">&times;</span>
            <h2 class="modal-title">Edit Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="form-group">
                    <label for="edit_product_name">Product Name</label>
                    <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_description">Description</label>
                    <textarea class="form-control" id="edit_product_description" name="product_description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_price">Price (Rp)</label>
                    <input type="number" class="form-control" id="edit_product_price" name="product_price" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_category">Category</label>
                    <select class="form-control" id="edit_product_category" name="product_category" required>
                        <option value="">Select Category</option>
                        <option value="clothing">Clothing</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_stock">Stock</label>
                    <input type="number" class="form-control" id="edit_product_stock" name="product_stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_image">Product Image (leave empty to keep current image)</label>
                    <input type="text" class="form-control" id="edit_product_image" name="product_image" accept="image/*">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('editProductModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div>
            <p>&copy; 2025 zwnzs Dashboard. All rights reserved.</p>
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

        function editProduct(productId) {
            // Logic untuk mengisi form edit dengan data produk
            document.getElementById('edit_product_id').value = productId;
            // Fetch product data dan isi form fields
            document.getElementById('editProductModal').style.display = 'block';
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                // Logic untuk delete product
                console.log('Deleting product:', productId);
            }
        }

        function viewPendingOrders() {
            // Logic untuk view pending orders
            console.log('Viewing pending orders');
        }

        function exportData() {
            // Logic untuk export data
            console.log('Exporting data');
        }

        function viewReports() {
            // Logic untuk view reports
            console.log('Viewing reports');
        }

        // Close modal when clicking outside
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