<?php
// Routing dengan simple 404 page
if (array_key_exists($page, $allowed_pages)) {
    include $allowed_pages[$page];
} else {
    // Simple 404 Page yang konsisten dengan style home
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Page Not Found</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    </head>

    <body>
        <?php
        include 'components/header.php'
        ?>
        
        <!-- Menggunakan style Banner yang sama seperti di home -->
        <div class="Banner" style="min-height: 60vh; align-items: center; justify-content: center; text-align: center; background-color: white;">
            <div class="text-area" style="max-width: 100%; padding: 2rem; background-color: white;">
                <div class="banner-title">
                    <h1 style="font-size: 6rem; margin-bottom: 1rem; color: #333;">404</h1>
                    <h2 style="font-size: 2rem; margin-bottom: 1rem; font-weight: 700; color: #333;">PAGE NOT FOUND</h2>
                    <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.8 ; color: #333;">
                        The page you're looking for doesn't exist.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="?page=home" style="padding: 12px 30px; background: #333; color: white; text-decoration: none; border-radius: 25px; font-weight: 600;">Go Home</a>
                        <a href="?page=shop" style="padding: 12px 30px; border: 2px solid #333; color: #333; text-decoration: none; border-radius: 25px; font-weight: 600;">Browse Shop</a>
                    </div>
                </div>
            </div>
        </div>

        <?php
            include 'components/footer.php'
        ?>
    </body>
    </html>
    <?php
}
?>