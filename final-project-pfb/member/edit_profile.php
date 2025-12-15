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
    header("Location: ../admin/dashboard.php?error=" . urlencode("Admin profiles cannot be edited through the member portal."));
    exit();
}

if ($_SESSION['role'] !== 'member') {
    header("Location: ../auth/login.php");
    exit();
}
$userID = $_SESSION['user_id'];
$error = $_GET['error'] ?? '';
$message = $_GET['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    
    if (empty($username) || empty($email) || empty($dateOfBirth) || empty($gender)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            $stmt_check = $conn->prepare("SELECT userID FROM users WHERE (username = ? OR email = ?) AND userID != ?");
            $stmt_check->bind_param("ssi", $username, $email, $userID);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $error = "The new username or email is already in use by another account.";
            } else {
                $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ?, dateOfBirth = ?, gender = ? WHERE userID = ?");
                $stmt_update->bind_param("ssssi", $username, $email, $dateOfBirth, $gender, $userID);
                
                if ($stmt_update->execute()) {
                    $_SESSION['username'] = $username;
                    header("Location: edit_profile.php?message=" . urlencode("Profile updated successfully!"));
                    exit();
                } else {
                    $error = "Database update failed: " . $stmt_update->error;
                }
                $stmt_update->close();
            }
            $stmt_check->close();
        } catch (Exception $e) {
            $error = "An unexpected error occurred: " . $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("SELECT username, email, dateOfBirth, gender FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userProfile = $result->fetch_assoc();
$stmt->close();

$pageTitle = 'Edit Profile';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/auth.css"> 
<link rel="stylesheet" href="../assets/css/profile.css">

<main>
    <div class="profile-container">
        <h1>Edit Profile</h1>

        <?php if ($error): ?>
            <div class="message error-red"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($message): ?>
            <div class="message success-green"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="edit_profile.php" method="POST" class="card-box">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userProfile['username'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userProfile['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="dateOfBirth">Date of Birth</label>
                <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($userProfile['dateOfBirth'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo ($userProfile['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($userProfile['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($userProfile['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-update">Save Changes</button>
        </form>

        <p class="auth-link-footer">
            <a href="profile.php" class="link-register">← Back to Profile</a>
            |
            <a href="change_password.php" class="link-register">Change Password</a>
        </p>
        
        <div class="delete-section">
             <button id="delete-btn" class="btn btn-delete">Delete Account</button>
             <p>Warning: This action is irreversible and will delete all associated data (e.g., cart items, orders).</p>
        </div>
        </div>
</main>

<script>
    document.getElementById('delete-btn').addEventListener('click', function() {
        if (confirm('WARNING: Are you sure you want to permanently delete your account? This action cannot be undone.')) {
            window.location.href = 'delete_account.php'; 
        }
    });
</script>

<?php
include '../includes/footer.php';
?>