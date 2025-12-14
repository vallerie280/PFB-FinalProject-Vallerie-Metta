<?php
include '../includes/connection.php'; 
$pageTitle = 'Shopping Cart';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$cart_message = $_GET['msg'] ?? '';

function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $productID = (int)$_POST['product_id'];
    
    if ($_POST['action'] === 'update_qty' && isset($_POST['quantity'])) {
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0 && isset($cart[$productID])) {
            $cart[$productID]['quantity'] = $quantity;
            $cart_message = 'Cart quantity updated.';
        } elseif ($quantity <= 0 && isset($cart[$productID])) {
            unset($cart[$productID]); 
            $cart_message = 'Item removed from cart.';
        }
    } elseif ($_POST['action'] === 'remove_item' && isset($cart[$productID])) {
        unset($cart[$productID]);
        $cart_message = 'Item successfully removed from cart.';
    }
    
    $_SESSION['cart'] = $cart;
    header("Location: cart.php?msg=" . urlencode($cart_message));
    exit();
}

$grandTotal = 0;
foreach ($cart as $productID => $item) {
    if (isset($item['price']) && isset($item['quantity'])) {
        $grandTotal += $item['price'] * $item['quantity'];
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/cart.css">

<main>
    <div class="cart-container">
        <h1>Your Shopping Cart</h1>
        
        <?php if (!empty($cart_message)): ?>
            <div class="message success-green"><?php echo htmlspecialchars($cart_message); ?></div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="message info-blue">
                <p>Your cart is empty.</p>
                <a href="product_catalog.php">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $productID => $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                        <tr>
                            <td class="product-info">
                                <img src="../assets/images/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                            </td>
                            <td><?php echo formatRupiah($item['price']); ?></td>
                            <td>
                                <form action="cart.php" method="POST" class="qty-form">
                                    <input type="hidden" name="action" value="update_qty">
                                    <input type="hidden" name="product_id" value="<?php echo $productID; ?>">
                                    <input 
                                        type="number" 
                                        name="quantity" 
                                        value="<?php echo htmlspecialchars($item['quantity']); ?>" 
                                        min="1" 
                                        onchange="this.form.submit()" 
                                        class="qty-input"
                                    >
                                </form>
                            </td>
                            <td><?php echo formatRupiah($subtotal); ?></td>
                            <td>
                                <form action="cart.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="product_id" value="<?php echo $productID; ?>">
                                    <button type="submit" class="btn btn-remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <div class="total-row">
                    <strong>Grand Total:</strong>
                    <span class="total-amount"><?php echo formatRupiah($grandTotal); ?></span>
                </div>
                <div class="cart-actions">
                    <a href="product_catalog.php" class="btn btn-secondary">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
include '../includes/footer.php';
?>