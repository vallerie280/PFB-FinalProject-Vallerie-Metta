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
$error = '';

try {
    $conn->begin_transaction();
    $stmt_cart = $conn->prepare("DELETE FROM cart_items WHERE userID = ?");
    $stmt_cart->bind_param("i", $userID);
    $stmt_cart->execute();
    $stmt_cart->close();

    $stmt_orders = $conn->prepare("DELETE FROM orders WHERE userID = ?");
    $stmt_orders->bind_param("i", $userID);
    $stmt_orders->execute();
    $stmt_orders->close();
    $stmt_user = $conn->prepare("DELETE FROM users WHERE userID = ?");
    $stmt_user->bind_param("i", $userID);
    
    if ($stmt_user->execute()) {
        $conn->commit();
        session_unset();
        session_destroy();
        header("Location: ../index.php?msg=" . urlencode("Your account has been successfully deleted."));
        exit();
    } else {
        throw new Exception("Failed to delete user record.");
    }
    
} catch (Exception $e) {
    $conn->rollback();
    $error = "Account deletion failed. Error: " . $e->getMessage();
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=" . urlencode($error));
    exit();
}