<?php
session_start();

$host = "localhost";
$user = "root";
$password = "Megamode#12";
$database = "FINPRO";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $passwordInput = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($passwordInput, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Jika Remember Me dicentang, buat cookie
            if (isset($_POST['remember'])) {
                setcookie("user_id", $user['id'], time() + (86400 * 30), "/");
                setcookie("user_name", $user['name'], time() + (86400 * 30), "/");
                setcookie("user_role", $user['role'], time() + (86400 * 30), "/");
            }

            header("Location: ?page=dashboard");
            exit();
        } else {
            $loginError = "Password salah!";
        }
    } else {
        $loginError = "Email tidak ditemukan!";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial; margin: 100px auto; max-width: 400px; }
        form { padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; margin: 10px 0;
        }
        input[type="submit"] {
            padding: 10px 20px; background-color: #2980b9; color: white; border: none;
        }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Login</h2>

<?php if ($loginError): ?>
    <p class="error"><?php echo $loginError; ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label><input type="checkbox" name="remember"> Remember Me</label><br><br>

    <input type="submit" value="Login">
</form>

<p>Belum punya akun? <a href="?page=register">Daftar</a></p>

</body>
</html>
