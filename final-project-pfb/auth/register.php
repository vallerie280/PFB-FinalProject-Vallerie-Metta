<?php
include '../includes/connection.php'; 
$pageTitle = 'Register Account';
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit();
}

$message = '';
$error = '';
$username = '';
$email = '';
$dateOfBirth = '';
$gender = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($dateOfBirth) || empty($gender)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt_check = $conn->prepare("SELECT userID FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, dateOfBirth, gender, role) VALUES (?, ?, ?, ?, ?, 'member')";
            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->bind_param("sssss", $username, $email, $hashedPassword, $dateOfBirth, $gender);

            if ($stmt_insert->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'member';
                header("Location: ../member/home.php?msg=" . urlencode("Registration successful! Welcome, " . $username));
                exit();
                
            } else {
                $error = "Registration failed. Please try again. Error: " . $conn->error;
            }

            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/auth.css">

<main>
    <div class="auth-card">
        <h2>Register Account</h2>

        <?php if ($error): ?>
            <div class="message error-red"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="dateOfBirth">Date of Birth</label>
                <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($dateOfBirth); ?>" required>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($gender === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-register">Register</button>
        </form>

        <p class="auth-link-footer">
            Already have an account? <a href="login.php" class="link-login">Login here</a>
        </p>
    </div>
</main>

<?php
include '../includes/footer.php';
?>