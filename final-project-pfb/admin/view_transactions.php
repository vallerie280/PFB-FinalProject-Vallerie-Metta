<?php
include '../includes/connection.php'; 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$pageTitle = 'Customer Transactions';
include '../includes/header.php';
$transactionTableName = 'transactions'; 
$sql = "SELECT 
            o.transactionID,   
            u.username AS customerName,
            o.transactionDate,  
            o.totalAmount,
            o.status
        FROM {$transactionTableName} o 
        JOIN users u ON o.userID = u.userID
        ORDER BY o.transactionDate DESC";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error . "<br>Query: " . $sql);
}

$totalOrders = $result->num_rows;
?>

<link rel="stylesheet" href="../assets/css/view_transactions.css">

<main>
    <div class="transactions-view-container">
        <h1>All Customer Transactions</h1>
        
        <div class="total-info">
            Total Transactions: <span class="total-count"><?php echo $totalOrders; ?></span>
        </div>

        <?php if ($totalOrders > 0): ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($row['transactionID']); ?></td>
                            <td><?php echo htmlspecialchars($row['customerName']); ?></td>
                            <td><?php echo date("Y-m-d", strtotime($row['transactionDate'])); ?></td>
                            <td class="amount-cell">Rp <?php echo number_format($row['totalAmount'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                    $statusClass = strtolower($row['status']);
                                    echo "<span class='status-badge status-{$statusClass}'>" . htmlspecialchars($row['status']) . "</span>";
                                ?>
                            </td>
                            <td>
                                <a href="view_transactions.php" class="action-link">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No customer transactions found.</p>
        <?php endif; ?>
    </div>
</main>

<?php
include '../includes/footer.php';
?>