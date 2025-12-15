<?php
include '../includes/connection.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: ../admin/dashboard.php?error=" . urlencode("Admin passwords must be changed through phpMyAdmin Manually."));
    exit();
}

if ($_SESSION['role'] !== 'member') {
    header("Location: ../auth/login.php");
    exit();
}
$userID = $_SESSION['user_id'];
$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "All password fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation password do not match.";
    } elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($currentPassword, $user['password'])) {
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE userID = ?");
            $stmt_update->bind_param("si", $newHashedPassword, $userID);
            
            if ($stmt_update->execute()) {
                $message = "Password updated successfully!";
                $stmt_update->close();
            } else {
                $error = "Database update failed: " . $stmt_update->error;
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

$pageTitle = 'Change Password';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/auth.css"> 
<link rel="stylesheet" href="../assets/css/profile.css"> <main>
    <div class="profile-container">
        <h1>Change Password</h1>

        <?php if ($error): ?>
            <div class="message error-red"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($message): ?>
            <div class="message success-green"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="change_password.php" method="POST" class="card-box">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <hr> <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-update">Change Password</button>
        </form>

        <p class="auth-link-footer">
            <a href="edit_profile.php" class="link-register">← Back to Edit Profile</a>
        </p>
    </div>
</main>

<?php
include '../includes/footer.php';
?>