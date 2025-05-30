<?php

$page = $_GET['page'] ?? 'home'; // default ke halaman 'home'

// Daftar halaman yang diperbolehkan
$allowed_pages = [
    'home' => 'pages/home.php',
    'contact' => 'pages/contact.php',
    'dashboard' => 'pages/dashboard.php',
    'profile' => 'pages/profile.php',
    'keranjang' => 'pages/keranjang.php',
    
    'shop' => 'pages/shop.php',
    'shop-clothing' => 'pages/shop-clothing.php',
    'shop-accesory' => 'pages/shop-accesory.php',

    'login' => 'pages/login.php',
    'register' => 'pages/register.php',
    'logout' => 'pages/logout.php',

    'submit-order' => 'pages/submit-order.php',
];


if (array_key_exists($page, $allowed_pages)) {
    if (file_exists($allowed_pages[$page])) {
        include $allowed_pages[$page];
    } else {
        include 'pages/404.php';
    }
} else {
    include 'pages/404.php';
}
?>