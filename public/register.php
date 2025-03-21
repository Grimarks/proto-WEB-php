<?php
global $pdo;
require_once "../includes/functions.php";
require_once "../includes/config.php"; // Pastikan koneksi database dimuat

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $password])) {
        $success_message = "✅ Registrasi berhasil! Silakan <a href='login.php'>Login</a>";
    } else {
        $success_message = "❌ Terjadi kesalahan saat registrasi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="stylesReg.css">
</head>
<body>
<div class="register-container">
    <div class="register-card">
        <h2>📝 Registrasi</h2>

        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?= $success_message ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Register</button>
        </form>

        <p class="login-link">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</div>
</body>
</html>
