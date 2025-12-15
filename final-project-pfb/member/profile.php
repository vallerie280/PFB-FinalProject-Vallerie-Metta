<?php
include '../includes/connection.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../auth/login.php");
    exit();
}
$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, dateOfBirth, gender FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userProfile = $result->fetch_assoc();
$stmt->close();

$pageTitle = 'My Profile';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/profile.css"> 

<main>
    <div class="profile-container">
        <h1>Hello, <?php echo htmlspecialchars($userProfile['username'] ?? 'Member'); ?></h1>
        <p class="role-tag">Role: <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></p>

        <div class="profile-details card-box">
            <h2>Account Details</h2>
            
            <div class="detail-group">
                <label>Email Address:</label>
                <span><?php echo htmlspecialchars($userProfile['email'] ?? 'N/A'); ?></span>
            </div>
            
            <div class="detail-group">
                <label>Date of Birth:</label>
                <span><?php echo htmlspecialchars($userProfile['dateOfBirth'] ?? 'N/A'); ?></span>
            </div>
            
            <div class="detail-group">
                <label>Gender:</label>
                <span><?php echo htmlspecialchars($userProfile['gender'] ?? 'N/A'); ?></span>
            </div>
        </div>

        <div class="action-links">
            <a href="edit_profile.php" class="btn btn-edit">Edit Profile</a>
            <a href="transaction_history.php" class="btn btn-history">View Order History</a>
        </div>

    </div>
</main>

<?php
include '../includes/footer.php';
?>