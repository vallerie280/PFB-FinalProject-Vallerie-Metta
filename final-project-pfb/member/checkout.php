<?php
include '../includes/connection.php'; 
$pageTitle = 'Final Checkout';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'member/checkout.php'; 
    header("Location: ../auth/login.php");
    exit();
}

$userID = (int)$_SESSION['user_id']; 

if ($userID <= 0) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?error=" . urlencode("User session corrupted. Please log in again."));
    exit();
}
$userID = (int)$_SESSION['user_id']; 

if ($userID <= 0) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?error=" . urlencode("User session corrupted. Please log in again."));
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$totalAmount = 0;
$checkout_error = '';

if (empty($cart)) {
    header("Location: cart.php?msg=" . urlencode("Your cart is empty. Please add products first."));
    exit();
}
foreach ($cart as $productID => $item) { 
    if (isset($item['price']) && isset($item['quantity'])) {
        $totalAmount += $item['price'] * $item['quantity']; 
    } else {
        $checkout_error = "Inconsistent data found in cart item ID: " . htmlspecialchars($productID);
        break; 
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    
    $paymentMethod = $_POST['payment_method'] ?? 'Bank Transfer'; 

    if ($checkout_error) {
         $checkout_error = "Cannot place order due to inconsistent cart data.";
    } else {

        $conn->begin_transaction();
        
        try {
            $stmt_trans = $conn->prepare("INSERT INTO transactions (userID, transactionDate, totalAmount, paymentMethod, status) VALUES (?, NOW(), ?, ?, 'Pending')");
            
            if ($stmt_trans === false) {
                 throw new Exception("SQL Prepare Error: " . $conn->error);
            }
            $stmt_trans->bind_param("ids", $userID, $totalAmount, $paymentMethod);
            $stmt_trans->execute();
            $transactionID = $conn->insert_id;
            $stmt_trans->close();
            $stmt_detail = $conn->prepare("INSERT INTO transaction_details (transactionID, productID, quantity, subtotal) VALUES (?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE productID = ? AND stock >= ?");

            foreach ($cart as $productID => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt_stock->bind_param("iii", $item['quantity'], $productID, $item['quantity']);
                $stmt_stock->execute();
                
                if ($conn->affected_rows === 0) {
                     throw new Exception("Stock not available for product: " . htmlspecialchars($item['name']));
                }
                $stmt_detail->bind_param("iiid", $transactionID, $productID, $item['quantity'], $subtotal);
                $stmt_detail->execute();
            }

            $stmt_detail->close();
            $stmt_stock->close();

            $conn->commit();
            $_SESSION['cart'] = [];
            
            header("Location: confirmation.php?id=$transactionID");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $checkout_error = "Order placement failed: " . $e->getMessage();
        }
    }
}
function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/checkout.css">

<main>
    <div class="checkout-container">
        <h1>Finalizing Your Order</h1>
        
        <?php if ($checkout_error): ?>
            <div class="message error-red"><?php echo htmlspecialchars($checkout_error); ?></div>
        <?php endif; ?>
        
        <form action="checkout.php" method="POST" class="checkout-form">
            <input type="hidden" name="action" value="place_order">

            <div class="checkout-details-grid">
                
                <div class="payment-shipping card-box">
                    <h2>Payment Details</h2>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="Bank Transfer">Bank Transfer (BCA/Mandiri)</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Gopay">Gopay / E-Wallet</option>
                            <option value="COD">Cash on Delivery (COD)</option>
                        </select>
                    </div>
                </div>

                <div class="order-summary card-box">
                    <h2>Order Summary</h2>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $productID => $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td><?php echo formatRupiah($subtotal); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="total-row">
                        <strong>Grand Total:</strong>
                        <span class="total-amount"><?php echo formatRupiah($totalAmount); ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-place-order">
                        Complete Purchase (<?php echo formatRupiah($totalAmount); ?>)
                    </button>
                    
                    <a href="cart.php" class="btn btn-return-cart">
                        Return to Cart to Modify Order
                    </a>
                </div>

            </div>
            
        </form>
    </div>
</main>

<?php
include '../includes/footer.php';
?>