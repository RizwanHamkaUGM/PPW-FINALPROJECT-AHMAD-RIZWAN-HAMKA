<?php
include 'koneksi.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Your Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
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
    
    <div class="Banner">
        <div class="text-area">
            <div class="banner-title">
                <h1>NOT JUST MERCH. <br> IT'S IDENTITY</h1>
                <a href="?page=shop">explore now</a>
            </div>
        </div>
        <div class="image-area">
            <img src="assets/images/shirt.png" alt="">
        </div>
    </div>
    
    <div class="Product-section">
        <div class="product-container">
            <div class="product-navigation">
                <div class="category">Apparel</div>
                <div class="pagination-container">
                    <a href="?page=shop">Show All</a>
                </div>
            </div>

            <div class="card-container" id="product-cards">
                <?php
                // Debug: Cek apakah variabel ada
                if (isset($clothing_only_4)) {
                    echo "<!-- Debug: clothing_only_4 tersedia, jumlah: " . count($clothing_only_4) . " -->";
                    
                    if (!empty($clothing_only_4)) {
                        foreach ($clothing_only_4 as $row) {
                            echo '<div class="card">';
                            echo '  <div class="card-image">';
                            echo '      <img src="' . htmlspecialchars($row["image_url"] ?? 'assets/images/default.jpg') . '" alt="' . htmlspecialchars($row["name"] ?? 'Product') . '">';
                            echo '  </div>';
                            echo '  <div class="card-title">'; 
                            echo '      <h3 class="carde-title">' . htmlspecialchars(implode(' ', array_slice(explode(' ', $row["name"] ?? 'Unnamed Product'), 0, 3))) . '</h3>';  
                            echo '      <p>Rp ' . number_format($row["price"] ?? 0, 0, ',', '.') . '</p>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Tidak ada produk clothing ditemukan.</p>';
                    }
                } else {
                    echo '<p>Error: Data clothing tidak tersedia. Periksa koneksi database.</p>';
                    echo '<!-- Debug: $clothing_only_4 tidak tersedia -->';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="Banner" style="background-color: #84a1ff; align-items: end;">
        <img class="Banner-Image" src="assets/images/3RD.png" alt="">
    </div>

    <div class="Product-section">
        <div class="product-container">
            <div class="product-navigation">
                <div class="category">Accessories</div>
                <a href="?page=shop">Show All</a>
            </div>
            <div class="card-container" id="product-cards">
                <?php
                // Debug: Cek apakah variabel ada
                if (isset($accessory_only_4)) {
                    echo "<!-- Debug: accessory_only_4 tersedia, jumlah: " . count($accessory_only_4) . " -->";
                    
                    if (!empty($accessory_only_4)) {
                        foreach ($accessory_only_4 as $row) {
                            echo '<div class="card">';
                            echo '  <div class="card-image">';
                            echo '      <img src="' . htmlspecialchars($row["image_url"] ?? 'assets/images/default.jpg') . '" alt="' . htmlspecialchars($row["name"] ?? 'Product') . '">';
                            echo '  </div>';
                            echo '  <div class="card-title">'; 
                            echo '      <h3 class="carde-title">' . htmlspecialchars(implode(' ', array_slice(explode(' ', $row["name"] ?? 'Unnamed Product'), 0, 3))) . '</h3>';  
                            echo '      <p>Rp ' . number_format($row["price"] ?? 0, 0, ',', '.') . '</p>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Tidak ada produk accessories ditemukan.</p>';
                    }
                } else {
                    echo '<p>Error: Data accessories tidak tersedia. Periksa koneksi database.</p>';
                    echo '<!-- Debug: $accessory_only_4 tidak tersedia -->';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="Banner">
        <h1>NOT <br> JUST <br>MERCH.</h1>
    </div>

    <?php
    include 'components/footer.php';
    ?>

</body>
</html>