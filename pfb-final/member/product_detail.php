<?php
include '../includes/connection.php'; 
$pageTitle = 'Product Details';
$productID = (int)$_GET['id'] ?? 0;
$product = null;
$error = '';

if ($productID > 0) {
    $stmt = $conn->prepare("
        SELECT 
            p.productID, p.productName, p.description, p.price, p.stock, p.image,
            v.vendorName
        FROM products p
        JOIN vendors v ON p.vendorID = v.vendorID
        WHERE p.productID = ?
    ");
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $error = "Product not found.";
    }
    $stmt->close();
} else {
    $error = "Invalid product ID.";
}
function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = "member/product_detail.php?id=" . $productID;
        header("Location: ../auth/login.php");
        exit();
    }
    
    $quantity = (int)$_POST['quantity'] ?? 1;

    if ($product && $quantity > 0) {
        if (isset($_SESSION['cart'][$productID])) {
            $_SESSION['cart'][$productID]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productID] = [
                'productID' => $product['productID'], 
                'name'      => $product['productName'],
                'price'     => (float)$product['price'],
                'image'     => $product['image'],
                'quantity'  => $quantity,
            ];
        }

        $message = urlencode(htmlspecialchars($product['productName']) . " added to cart successfully.");
        header("Location: cart.php?msg=" . $message);
        exit();
    } else {
        $error = "Failed to add product to cart or quantity is invalid.";
    }
}


include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/product_detail.css">
<main>
    <div class="detail-container">
        <?php if ($error): ?>
            <div class="message error-red">
                <h2>Error</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="product_catalog.php" class="btn btn-primary">Back to Catalog</a>
            </div>
        <?php elseif ($product): ?>
            <div class="product-display-grid">
                
                <div class="product-image">
                    <img src="../assets/images/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['productName']); ?>">
                </div>

                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['productName']); ?></h1>
                    
                    <div class="price-section">
                        <span class="price-label">Price:</span>
                        <span class="price-value"><?php echo formatRupiah($product['price']); ?></span>
                    </div>

                    <div class="stock-section">
                        <span class="stock-label">Stock:</span>
                        <span class="stock-value <?php echo ($product['stock'] < 10) ? 'low-stock' : ''; ?>">
                            <?php echo ($product['stock'] > 0) ? htmlspecialchars($product['stock']) : 'Out of Stock'; ?>
                        </span>
                    </div>

                    <p class="vendor-info">
                        Sold by: **<?php echo htmlspecialchars($product['vendorName']); ?>**
                    </p>

                    <form action="product_detail.php?id=<?php echo $productID; ?>" method="POST" class="add-to-cart-form">
                        <input type="hidden" name="action" value="add_to_cart">
                        
                        <?php if ($product['stock'] > 0): ?>
                            <div class="form-group">
                                <label for="quantity">Quantity:</label>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required class="qty-input">
                            </div>
                            <button type="submit" class="btn btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sold-out" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="product-description card-box">
                <h2>Product Description</h2>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php
include '../includes/footer.php';
?>