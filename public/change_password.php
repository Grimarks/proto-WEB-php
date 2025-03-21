<?php
require_once "../includes/session.php";
require_once "../includes/config.php";

global $pdo;
$message = "";

// Pastikan pengguna sudah login atau memiliki token
if (!isset($_SESSION['username']) && (!isset($_GET['email']) || !isset($_GET['token']))) {
    header("Location: login.php");
    exit;
}

$email = "";
$fromDashboard = false;

// Jika diakses dari dashboard (tanpa token)
if (isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $email = $user['email'];
        $fromDashboard = true;
    }
}
// Jika diakses melalui link reset password (dengan token)
else {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Validasi token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("❌ Token tidak valid atau sudah kadaluarsa.");
    }
}

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "❌ Password baru dan konfirmasi password tidak cocok.";
    } else {
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE email = ?");
        $stmt->execute([$new_password, $email]);

        $message = "✅ Password berhasil diubah! Silakan login.";

        // Jika diakses dari link reset password, alihkan ke login
        if (!$fromDashboard) {
            header("Refresh: 2; URL=login.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password</title>
    <link rel="stylesheet" href="stylesCP.css">
</head>
<body>
<div class="change-password-container">
    <div class="change-password-card">
        <h2>🔑 Ganti Password</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="new_password">Password Baru:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Konfirmasi Password Baru:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Ubah Password</button>
        </form>

        <a href="<?= $fromDashboard ? 'dashboard.php' : 'login.php' ?>" class="back-link">⬅ Kembali</a>
    </div>
</div>
</body>
</html>
