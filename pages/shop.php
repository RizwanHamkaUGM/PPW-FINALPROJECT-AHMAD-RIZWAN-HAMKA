<?php
include 'koneksi.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Our Products</title>
    <link rel="stylesheet" href="assets/css/shop-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php
    session_start();
    if (isset($_SESSION['user_id'])) {
        // dd($_SESSION);
        include 'components/header_login.php';
    } else {
        include 'components/header.php';
    }
    ?>

    <div class="Product-section">
        <div class="product-container">
            <div class="product-navigation">
                <div class="category">All Product</div>
                <?php
                    include 'components/navigation.php'
                ?>
            </div>
            <div class="card-container" id="product-cards">
                <?php
                if (isset($all_products) && !empty($all_products)) {
                    foreach ($all_products as $row) {
                        $category = strtolower($row["category"] ?? 'clothing');
                        echo '<div class="card" data-category="' . htmlspecialchars($category) . '">';
                        echo '  <div class="card-image">';
                        echo '      <img src="' . htmlspecialchars($row["image_url"] ?? 'assets/images/default.jpg') . '" alt="' . htmlspecialchars($row["name"] ?? 'Product') . '" loading="lazy">';
                        echo '  </div>';
                        echo '  <div class="card-title">'; 
                        echo '      <h3 class="carde-title">' . htmlspecialchars(implode(' ', array_slice(explode(' ', $row["name"] ?? 'Unnamed Product'), 0, 3))) . '</h3>';   
                        echo '      <p class="card-price">Rp ' . number_format($row["price"] ?? 0, 0, ',', '.') . '</p>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-products">';
                    echo '  <div class="no-products-content">';
                    echo '      <h3>No Products Available</h3>';
                    echo '      <p>Products are currently being updated. Please check back later.</p>';
                    echo '  </div>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <!-- <?php if (isset($all_products) && !empty($all_products)): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <a href="#" class="arrow disabled" id="prev-page">&laquo; Previous</a>
                    <span class="active">1</span>
                    <a href="#" class="arrow disabled" id="next-page">Next &raquo;</a>
                </div>
            </div>
            <?php endif; ?> -->
        </div>
    </div>

    <?php
    include 'components/footer.php';
    include 'components/modal.php'
    ?>

</body>
</html>