<?php
global $pdo;
require_once "../includes/session.php";
require_once "../includes/config.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $file = $_FILES["profile_picture"];
    $uploadDir = "../public/uploads/"; // Pastikan folder ini ada dan memiliki izin tulis
    $fileName = $username . "_" . basename($file["name"]); // Hanya tambahkan username, tanpa timestamp
    $targetFilePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validasi tipe file
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($fileType, $allowedTypes)) {
        die("❌ " . htmlspecialchars("Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan."));
    }

    // Validasi ukuran file (maksimal 2MB)
    if ($file["size"] > 2 * 1024 * 1024) {
        die("❌ " . htmlspecialchars("Ukuran file terlalu besar (maks 2MB)."));
    }

    // Simpan file
    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        // Simpan hanya nama file di database, bukan path lengkap
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE username = ?");
        $stmt->execute([$fileName, $username]);

        echo "✅ " . htmlspecialchars("Foto profil berhasil diunggah!");
    } else {
        echo "❌ " . htmlspecialchars("Gagal mengunggah file.");
    }
}
?>
