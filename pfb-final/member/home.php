<?php
include '../includes/connection.php'; 
$pageTitle = 'Member Home';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'member') {
    header("Location: ../auth/login.php"); 
    exit();
}

$loggedInUsername = $_SESSION['username'] ?? 'Member User';

$products = [
    ['productName' => 'Felix Accent Armchair', 'price' => '1,950,000', 'vendorName' => 'Nordico', 'image' => 'Felix Accent Armchair.png', 'productID' => 1],
    ['productName' => 'Kyra Dining Set (4 Chairs)', 'price' => '3,790,000', 'vendorName' => 'Rustika', 'image' => 'Kyra Dining Set (4 Chairs).png', 'productID' => 2],
    ['productName' => 'Zenno Floating Wall Shelf', 'price' => '499,000', 'vendorName' => 'Rustika', 'image' => 'Zenno Floating Wall Shelf.png', 'productID' => 3],
];

function formatRupiah($price) {
    return 'Rp ' . number_format((int)str_replace(['.', ','], '', $price), 0, ',', '.');
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css"> <div class="member-home">
    <h1 class="welcome-message">Hello, <?php echo htmlspecialchars($loggedInUsername); ?>!</h1>
    
    <section class="quick-links">
        <h2>Quick Actions</h2>
        <div class="link-grid">
            <a href="catalog.php" class="link-card"><h3>Shop All Furniture</h3></a>
            <a href="history.php" class="link-card"><h3>View Order History</h3></a>
            <a href="cart.php" class="link-card"><h3>Go to Cart</h3></a>
        </div>
    </section>

    <h2 class="section-title">Recommended for You</h2>
    <section class="recommended-products">
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <div class="product-info">
                        <a href="product_detail.php?id=<?php echo urlencode($product['productID']); ?>">
                            <?php echo htmlspecialchars($product['productName']); ?>
                        </a>
                        <span class="product-vendor">by <?php echo htmlspecialchars($product['vendorName']); ?></span>
                        <span class="product-price"><?php echo formatRupiah($product['price']); ?></span>
                        <a href="product_detail.php?id=<?php echo urlencode($product['productID']); ?>" class="btn-detail">View Detail</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">We currently don't have any product recommendations for you.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
include '../includes/footer.php';
?>