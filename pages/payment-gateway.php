<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    $_SESSION['error_message'] = "Invalid Order ID specified.";
    header("Location: ?page=profile");
    exit();
}

include 'koneksi.php'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT o.id, o.total_price, o.status, p.payment_method, p.payment_status
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error_message'] = "Order not found or you do not have permission to access it.";
    header("Location: ?page=profile");
    exit();
}

if ($order['status'] !== 'pending') {
    $_SESSION['error_message'] = "This order has already been processed and cannot be paid again.";
    header("Location: ?page=profile");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    try {
        $pdo->beginTransaction();

        $updateOrderStmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
        $updateOrderStmt->execute([$order_id]);

        $updatePaymentStmt = $pdo->prepare("UPDATE payments SET payment_status = 'confirmed' WHERE order_id = ?");
        $updatePaymentStmt->execute([$order_id]);

        $pdo->commit();

        $_SESSION['success_message'] = "Payment for Order #{$order_id} was successful!";
        header("Location: ?page=profile");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Payment simulation failed. Please try again. Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - Order #<?= htmlspecialchars($order['id']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
</head>
<body>
    <?php
    if (isset($_SESSION['user_id'])) {
        include 'components/header_login.php';
    } else {
        include 'components/header.php';
    }
    ?>

    <div class="page-content-wrapper">
        <div class="payment-container">
            <div class="payment-header">
                <h1 class="payment-title">Payment Simulation</h1>
                <p class="payment-subtitle">Complete your purchase for Order #<?= htmlspecialchars($order['id']) ?></p>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="payment-details">
                <div class="detail-item">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Current Status:</span>
                    <span class="detail-value status-badge status-<?= htmlspecialchars($order['status']) ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount to Pay:</span>
                    <span class="detail-value total">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
                </div>
            </div>

            <form class="payment-form" method="POST">
                <p class="dummy-info">This is a simulated payment environment. No real money will be charged.<br>Click the button below to mark this order as 'Paid'.</p>
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                <button type="submit" name="confirm_payment" class="confirm-payment-btn">Confirm Payment</button>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html>