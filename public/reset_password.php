<?php
require_once "../includes/functions.php";
require_once "../includes/config.php";
require '../vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $pdo;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Cek apakah email terdaftar
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Buat token unik
        $token = bin2hex(random_bytes(32));

        // Simpan token ke database
        $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $stmt->execute([$token, $email]);

        // Buat link reset password
        $reset_link = "http://localhost:63342/WebPHP/auth_system/public/change_password.php?email=" . urlencode($email) . "&token=" . $token;

        // Kirim email menggunakan PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Konfigurasi SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'darrell.satriano@gmail.com';
            $mail->Password = 'ubfh krhh xdks ocwu';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Pengaturan email
            $mail->setFrom('darrell.satriano@gmail.com', 'Admin');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Anda';
            $mail->Body = "Klik link berikut untuk mereset password Anda: <a href='$reset_link'>$reset_link</a>";

            // Kirim email
            $mail->send();
            $message = "✅ Email reset password telah dikirim. Cek email Anda.";
        } catch (Exception $e) {
            $message = "❌ Gagal mengirim email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "❌ Email tidak ditemukan dalam sistem.";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="stylesReset.css">
</head>
<body>
<div class="form-container">
    <div class="form-card">
        <h2>🔒 Reset Password</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="email">Masukkan Email Anda:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Kirim Link Reset</button>
        </form>

        <p class="back-link"><a href="login.php">Kembali ke Login</a></p>
    </div>
</div>
</body>
</html>
