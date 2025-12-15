<?php
include '../includes/connection.php'; 
$pageTitle = 'Order Confirmation';
$transactionID = intval($_GET['id'] ?? 0);
$transactionData = null;
$details = [];
$error = '';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($transactionID === 0) {
    $error = "Invalid transaction ID provided.";
} else {
    try {
        $sql_header = "
            SELECT 
                t.transactionID, t.transactionDate, t.totalAmount, t.paymentMethod, t.status, 
                u.username, u.email
            FROM transactions t
            JOIN users u ON t.userID = u.userID
            WHERE t.transactionID = ? AND t.userID = ?";
            
        $stmt_header = $conn->prepare($sql_header);
        $stmt_header->bind_param("ii", $transactionID, $_SESSION['user_id']); 
        $stmt_header->execute();
        $result_header = $stmt_header->get_result();
        
        if ($result_header->num_rows > 0) {
            $transactionData = $result_header->fetch_assoc();
            $stmt_header->close();
            $sql_details = "
                SELECT 
                    td.quantity, td.subtotal, 
                    p.productName, p.price, p.image
                FROM transaction_details td
                JOIN products p ON td.productID = p.productID
                WHERE td.transactionID = ?";
                
            $stmt_details = $conn->prepare($sql_details);
            $stmt_details->bind_param("i", $transactionID);
            $stmt_details->execute();
            $result_details = $stmt_details->get_result();
            $details = $result_details->fetch_all(MYSQLI_ASSOC);
            $stmt_details->close();

        } else {
            $error = "Transaction not found or you do not have permission to view it.";
        }
    } catch (Exception $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/confirmation.css">

<main>
    <div class="confirmation-container">
        <?php if ($error): ?>
            <div class="message error-red">
                <h2>Error</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="../index.php" class="btn btn-primary">Go to Home</a>
            </div>
        <?php elseif ($transactionData): ?>
            <div class="confirmation-header">
                <i class="fas fa-check-circle success-icon"></i>
                <h1>Order Confirmed!</h1>
                <p class="transaction-id">Transaction ID: **#<?php echo htmlspecialchars($transactionData['transactionID']); ?>**</p>
            </div>
            
            <div class="confirmation-details-grid">
                
                <div class="summary-box">
                    <h2>Order Overview</h2>
                    <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($transactionData['transactionDate'])); ?></p>
                    <p><strong>Total Amount:</strong> <span class="total-amount"><?php echo formatRupiah($transactionData['totalAmount']); ?></span></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($transactionData['paymentMethod']); ?></p>
                    <p><strong>Status:</strong> <span class="status-tag status-<?php echo strtolower($transactionData['status']); ?>"><?php echo htmlspecialchars($transactionData['status']); ?></span></p>
                </div>

                <div class="customer-box">
                    <h2>Customer Information</h2>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($transactionData['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($transactionData['email']); ?></p>
                </div>
            </div>

            <div class="product-details-box">
                <h2>Items Purchased</h2>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['productName']); ?></td>
                                <td><?php echo formatRupiah($item['price']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo formatRupiah($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="confirmation-footer">
                <a href="product_catalog.php" class="btn btn-primary">Continue Shopping</a>
                <a href="transaction_history.php" class="btn btn-secondary">View Order History</a>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php
include '../includes/footer.php';
?>