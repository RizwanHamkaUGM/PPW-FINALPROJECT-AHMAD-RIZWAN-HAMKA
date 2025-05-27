<?php
$host = "localhost";
$user = "root";
$password = "Megamode#12";
$database = "FINPRO";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$registerError = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $passwordInput = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validasi password cocok
    if ($passwordInput !== $confirmPassword) {
        $registerError = "Password tidak cocok!";
    } else {
        // Hash password
        $hashedPassword = password_hash($passwordInput, PASSWORD_DEFAULT);

        // Insert data ke database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone_number);

        if ($stmt->execute()) {
            $successMessage = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
        } else {
            $registerError = "Gagal mendaftar: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - PHP Native</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 100px auto; max-width: 400px; }
        form { padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; margin: 10px 0;
        }
        input[type="submit"] {
            padding: 10px 20px; background-color: #27ae60; color: white; border: none;
            cursor: pointer;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h2>Register</h2>

<?php if ($registerError): ?>
    <p class="error"><?php echo $registerError; ?></p>
<?php endif; ?>

<?php if ($successMessage): ?>
    <p class="success"><?php echo $successMessage; ?></p>
<?php endif; ?>

<form method="POST" action="register.php">
    <label>Nama:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>No. HP:</label>
    <input type="text" name="phone_number" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>Konfirmasi Password:</label>
    <input type="password" name="confirm_password" required>

    <input type="submit" value="Daftar">
</form>

<p>Sudah punya akun? <a href="login.php">Login</a></p>

</body>
</html>
