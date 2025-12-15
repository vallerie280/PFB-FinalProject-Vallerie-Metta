<?php
include '../includes/connection.php'; 
$pageTitle = 'Login to Furniland';

$redirectUrl = $_SESSION['redirect_url'] ?? '../index.php';

if (isset($_SESSION['user_id'])) {
    unset($_SESSION['redirect_url']);
    header("Location: " . $redirectUrl); 
    exit();
}

$error = $_GET['error'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT userID, username, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $sessionRole = $user['role'];
                if (strpos($email, '@gmail.com') !== false) {
                    $sessionRole = 'member';
                } 
                $_SESSION['role'] = $sessionRole; 
                
                unset($_SESSION['redirect_url']);
                
                if ($_SESSION['role'] === 'admin') {
                     header("Location: ../admin/dashboard.php");
                } else {
                     header("Location: " . $redirectUrl);
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/auth.css">

<main>
    <div class="auth-card">
        <h2>Login to Account</h2>

        <?php if ($error): ?>
            <div class="message error-red"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-login">Login</button>
        </form>

        <p class="auth-link-footer">
            Don't have an account? <a href="register.php" class="link-register">Register here</a>
        </p>
    </div>
</main>

<?php
include '../includes/footer.php';
?>