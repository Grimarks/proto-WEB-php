<?php
require_once "../includes/session.php";
require_once "../includes/config.php";

global $pdo;

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$stmt = $pdo->prepare("SELECT id, username, email, role, profile_picture FROM users");
$stmt->execute();
$usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete']) && $role === 'admin') {
    $userId = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $userId = $_POST['user_id'];
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];
    $newRole = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$newUsername, $newEmail, $newRole, $userId]);

    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $userId = $_POST['user_id'];
    $file = $_FILES['profile_picture'];

    if ($file['error'] === 0) {
        $fileName = $userId . "_" . basename($file['name']);
        $targetPath = "uploads/" . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$fileName, $userId]);
        }
    }

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="stylesDashboard.css">
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-card">
        <h1>Selamat Datang, <?= ucfirst($role) ?>!</h1>
        <p class="username"><?= htmlspecialchars($username) ?></p>

        <?php if ($role === 'admin'): ?>
            <h2>Daftar Pengguna</h2>
            <table border="1">
                <thead>
                <tr>
                    <th>Foto</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($usersData as $user): ?>
                    <tr>
                        <td>
                            <?php if ($user['profile_picture']): ?>
                                <img src="uploads/<?= htmlspecialchars($user['profile_picture']) ?>" width="50" height="50">
                                <br>
                                <a href="download.php?file=<?= htmlspecialchars($user['profile_picture']) ?>">⬇ Download</a>
                            <?php else: ?>
                                ❌ Tidak ada foto
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                <select name="role">
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                </select>
                                <button type="submit" name="update_user">✏️ Update</button>
                            </form>
                            <a href="dashboard.php?delete=<?= $user['id'] ?>" onclick="return confirm('Hapus pengguna ini?')">❌ Hapus</a>
                            <br>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="file" name="profile_picture" required>
                                <button type="submit">📤 Upload Foto</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="user-text">Anda login sebagai <strong>User</strong>. Nikmati fitur yang tersedia.</p>
        <?php endif; ?>

        <div class="button-group">
            <a href="change_password.php" class="btn">🔑 Ganti Password</a>
            <a href="reset_password.php" class="btn">🔄 Reset Password</a>
            <a href="logout.php" class="btn logout-btn">🚪 Logout</a>
        </div>
    </div>
</div>
</body>
</html>
