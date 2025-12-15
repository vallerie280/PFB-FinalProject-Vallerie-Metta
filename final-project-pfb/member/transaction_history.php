<?php
include '../includes/connection.php'; 
$pageTitle = 'Transaction History';
$transactions = [];
$error = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$userID = $_SESSION['user_id'];
function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

try {
    $stmt = $conn->prepare("
        SELECT 
            t.transactionID, 
            t.transactionDate, 
            t.totalAmount, 
            t.status
        FROM 
            transactions t
        WHERE 
            t.userID = ?
        ORDER BY 
            t.transactionDate DESC
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($transactions as $key => $transaction) {
        $detail_stmt = $conn->prepare("
            SELECT 
                td.quantity, 
                td.subtotal,
                p.productName,
                p.image
            FROM 
                transaction_details td
            JOIN 
                products p ON td.productID = p.productID
            WHERE 
                td.transactionID = ?
        ");
        $detail_stmt->bind_param("i", $transaction['transactionID']);
        $detail_stmt->execute();
        $detail_result = $detail_stmt->get_result();
        $transactions[$key]['details'] = $detail_result->fetch_all(MYSQLI_ASSOC);
        $detail_stmt->close();
    }

} catch (Exception $e) {
    $error = "An error occurred while fetching your history: " . $e->getMessage();
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/history.css">

<main>
    <h1 class="history-title">Your Order History</h1>

    <?php if ($error): ?>
        <div class="message error-red" style="max-width: 800px; margin: 20px auto;"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (empty($transactions)): ?>
        <div class="empty-history">
            <p>You have not placed any orders yet.</p>
            <a href="/pfb-final/member/product_catalog.php" class="btn btn-login">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="history-container">
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-card">
                    <div class="card-header">
                        <span class="order-id">Order ID: #<?php echo htmlspecialchars($transaction['transactionID']); ?></span>
                        <span class="order-date">Date: <?php echo date('M d, Y', strtotime($transaction['transactionDate'])); ?></span>
                        <span class="order-status status-<?php echo strtolower($transaction['status']); ?>"><?php echo htmlspecialchars($transaction['status']); ?></span>
                    </div>

                    <div class="card-body">
                        <?php foreach ($transaction['details'] as $detail): ?>
                            <div class="item-detail">
                                <img src="../assets/images/<?php echo htmlspecialchars($detail['image']); ?>" alt="<?php echo htmlspecialchars($detail['productName']); ?>">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($detail['productName']); ?></span>
                                    <span class="item-qty">Qty: <?php echo htmlspecialchars($detail['quantity']); ?></span>
                                </div>
                                <span class="item-subtotal"><?php echo formatRupiah($detail['subtotal']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card-footer">
                        <span class="total-label">Total Paid:</span>
                        <span class="total-amount"><?php echo formatRupiah($transaction['totalAmount']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php
include '../includes/footer.php';
?>