<?php
include '../includes/connection.php'; 
$pageTitle = 'Explore Our Full Catalog';

$selectedVendor = intval($_GET['vendor'] ?? 0);
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 0);
$sortOrder = $_GET['sort'] ?? 'name_asc';

$products = [];
$vendors = [];
$error = '';

try {
    $vendorResult = $conn->query("SELECT vendorID, vendorName FROM vendors ORDER BY vendorName ASC");
    if ($vendorResult) {
        $vendors = $vendorResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = "Error fetching vendors: " . $e->getMessage();
}
$whereClauses = [];
$orderBy = '';

if ($selectedVendor > 0) {
    $stmt = $conn->prepare("SELECT vendorName FROM vendors WHERE vendorID = ?");
    $stmt->bind_param("i", $selectedVendor);
    $stmt->execute();
    $stmt->close();
    $whereClauses[] = "p.vendorID = $selectedVendor"; 
}

if ($minPrice > 0 || $maxPrice > 0) {
    if ($minPrice > 0) {
        $whereClauses[] = "p.price >= " . $conn->real_escape_string($minPrice);
    }
    if ($maxPrice > 0) {
        $whereClauses[] = "p.price <= " . $conn->real_escape_string($maxPrice);
    }
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

switch ($sortOrder) {
    case 'name_desc':
        $orderBy = "ORDER BY productName DESC";
        break;
    case 'price_asc':
        $orderBy = "ORDER BY price ASC";
        break;
    case 'price_desc':
        $orderBy = "ORDER BY price DESC";
        break;
    case 'name_asc':
    default:
        $orderBy = "ORDER BY productName ASC";
        break;
}
$sql = "SELECT p.*, v.vendorName 
        FROM products p
        JOIN vendors v ON p.vendorID = v.vendorID
        $whereSql
        $orderBy";

try {
    $result = $conn->query($sql);
    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "Error fetching products: " . $conn->error;
    }
} catch (Exception $e) {
    $error = "An error occurred while fetching product data: " . $e->getMessage();
}
function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/catalog.css">

<main>
    <h1 class="catalog-title"><?php echo $pageTitle; ?></h1>
    
    <?php if ($error): ?>
        <div class="message error-red"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="catalog-layout">
        
        <div class="sidebar">
            <h2>Filters</h2>
            
            <form action="product_catalog.php" method="GET" id="filter-form">
                
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortOrder); ?>">

                <div class="filter-group">
                    <h3>Vendor</h3>
                    <select name="vendor" onchange="document.getElementById('filter-form').submit()">
                        <option value="0">All Vendors</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?php echo $vendor['vendorID']; ?>"
                                <?php echo ($selectedVendor == $vendor['vendorID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vendor['vendorName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Price Range (Rp)</h3>
                    <label for="min_price">Min Price:</label>
                    <input type="number" name="min_price" id="min_price" value="<?php echo htmlspecialchars($minPrice); ?>" min="0">
                    
                    <label for="max_price">Max Price:</label>
                    <input type="number" name="max_price" id="max_price" value="<?php echo htmlspecialchars($maxPrice); ?>" min="0">
                    
                    <button type="submit" class="btn btn-filter">Apply Price Filter</button>
                    
                    <?php if ($selectedVendor > 0 || $minPrice > 0 || $maxPrice > 0): ?>
                        <a href="product_catalog.php" class="btn btn-reset">Reset Filters</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
        
        <div class="product-area">
            
            <div class="sort-header">
                <form action="product_catalog.php" method="GET" class="sort-form">
                    
                    <input type="hidden" name="vendor" value="<?php echo htmlspecialchars($selectedVendor); ?>">
                    <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($minPrice); ?>">
                    <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($maxPrice); ?>">

                    <label for="sort">Sort By:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="name_asc" <?php echo ($sortOrder == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo ($sortOrder == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo ($sortOrder == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo ($sortOrder == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </form>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="no-results">No products found matching your criteria.</div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <a href="product_detail.php?id=<?php echo $product['productID']; ?>">
                                <img src="../assets/images/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                            </a>
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($product['productName']); ?></h3>
                                <p class="vendor-name">by <?php echo htmlspecialchars($product['vendorName']); ?></p>
                                <p class="price"><?php echo formatRupiah($product['price']); ?></p>
                                
                                <a href="/pfb-final/member/product_detail.php?id=<?php echo $product['productID']; ?>" class="btn btn-view-detail">
                                    View Detail
                                </a>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</main>

<?php
include '../includes/footer.php';
?>