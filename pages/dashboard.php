<?php
session_start();

// Jika session tidak ada, cek cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['user_name'] = $_COOKIE['user_name'];
    $_SESSION['user_role'] = $_COOKIE['user_role'];
}

// Kalau tetap belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil dari session
$name = $_SESSION['user_name'];
$role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial; padding: 50px; }
        .card {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
            text-align: center;
        }
        .logout {
            display: inline-block;
            margin-top: 20px;
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Selamat datang, <?php echo htmlspecialchars($name); ?>!</h2>
    <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong></p>
    <a href="logout.php" class="logout">Logout</a>
</div>

</body>
</html>
