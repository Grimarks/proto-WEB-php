<?php
global $pdo;
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {  // Tidak pakai password_verify()
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $redirect_url = ($user['role'] === 'admin') ? "dashboard.php?admin" : "dashboard.php?user";

        echo "<script>
                alert('✅ Login berhasil! Selamat datang, " . addslashes($user['username']) . "!');
                window.location.href = '$redirect_url';
              </script>";
        exit;
    } else {
        $error = "❌ Login gagal. Email atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
        <p>Belum punya akun? <a href="register.php">Daftar</a></p>
        <p><a href="forgot_password.php">Lupa Password?</a></p>
    </form>
</div>
</body>
</html>
