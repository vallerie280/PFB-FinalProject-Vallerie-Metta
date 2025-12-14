<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "furniland_db"; 


$conn_server = mysqli_connect($host, $user, $password);

if (!$conn_server) {
    die("ERROR TAHAP 1 (Koneksi Server): Pastikan XAMPP/MySQL berjalan. " . mysqli_connect_error());
}

echo "<h2>Setup Database Furniland</h2>";

$sql_drop = "DROP DATABASE IF EXISTS $dbname";
mysqli_query($conn_server, $sql_drop);
echo "Database $dbname berhasil dihapus (jika ada).<br>";

$sql_create = "CREATE DATABASE $dbname";
if (mysqli_query($conn_server, $sql_create)) {
    echo "Database **$dbname** berhasil dibuat!<br>";
} else {
    die("ERROR TAHAP 1 (Create DB): Gagal membuat database. " . mysqli_error($conn_server));
}

mysqli_close($conn_server);
echo "<hr>";
$connection = mysqli_connect($host, $user, $password, $dbname);

if (!$connection) {
    die("ERROR TAHAP 2 (Koneksi DB): Koneksi ke database $dbname gagal. " . mysqli_connect_error());
}

echo "<h3>Membuat Tabel dan Mengisi Data Awal</h3>";
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);


$create_queries = [
    "CREATE TABLE IF NOT EXISTS users ( 
        userID INT AUTO_INCREMENT PRIMARY KEY, 
        username VARCHAR(50) NOT NULL UNIQUE, 
        email VARCHAR(100) NOT NULL UNIQUE, 
        password VARCHAR(255) NOT NULL, 
        dateOfBirth DATE NULL,         
        gender ENUM('Male', 'Female', 'Other') NULL, 
        role ENUM('admin', 'member') NOT NULL DEFAULT 'member'
    );",

    "CREATE TABLE IF NOT EXISTS vendors ( 
        vendorID INT AUTO_INCREMENT PRIMARY KEY, 
        vendorName VARCHAR(100) NOT NULL UNIQUE, 
        location VARCHAR(100) NULL 
    );",
    
    "CREATE TABLE IF NOT EXISTS products ( 
        productID INT AUTO_INCREMENT PRIMARY KEY, 
        productName VARCHAR(100) NOT NULL, 
        description TEXT, 
        price DECIMAL(10, 2) NOT NULL, 
        stock INT NOT NULL DEFAULT 0,  
        image VARCHAR(255) NULL, 
        vendorID INT, 
        FOREIGN KEY (vendorID) REFERENCES vendors (vendorID) ON DELETE SET NULL 
    );",
    
    "CREATE TABLE IF NOT EXISTS transactions ( 
        transactionID INT AUTO_INCREMENT PRIMARY KEY, 
        userID INT, 
        transactionDate DATETIME NOT NULL, 
        totalAmount DECIMAL(10, 2) NOT NULL, 
        status ENUM('Pending', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending', 
        paymentMethod VARCHAR(50) NULL, 
        FOREIGN KEY (userID) REFERENCES users (userID) ON DELETE CASCADE 
    );",
    
    "CREATE TABLE IF NOT EXISTS transaction_details ( 
        detailID INT AUTO_INCREMENT PRIMARY KEY, 
        transactionID INT, 
        productID INT, 
        quantity INT NOT NULL, 
        subtotal DECIMAL(10, 2) NOT NULL, 
        FOREIGN KEY (transactionID) REFERENCES transactions (transactionID) ON DELETE CASCADE, 
        FOREIGN KEY (productID) REFERENCES products (productID) ON DELETE CASCADE 
    );",

    "INSERT INTO users (username, email, password, dateOfBirth, gender, role) 
     VALUES ('Admin Master', 'admin@furni.com', '$admin_password', '1990-01-01', 'Male', 'admin');",
    "INSERT INTO users (username, email, password, dateOfBirth, gender, role) 
     VALUES ('Member User', 'member@furni.com', '$admin_password', '1995-05-05', 'Female', 'member');",
    
    "INSERT INTO vendors (vendorName, location) VALUES 
    ('Boke Furniture', 'Jakarta'), 
    ('Fabelio', 'Semarang'), 
    ('GGS', 'Yogyakarta'), 
    ('Kayuku', 'Tangerang'), 
    ('Indonesia furniture', 'Jakarta'),
    ('IKEA', 'Jakarta'),          
    ('Rustika', 'Jakarta'),       
    ('WoodCraft', 'Jakarta'),     
    ('UrbanNest', 'Jakarta')      
    ON DUPLICATE KEY UPDATE location=VALUES(location), vendorName=VALUES(vendorName);",
];

foreach ($create_queries as $index => $query) {
    if (mysqli_query($connection, $query)) {
        echo "Query " . ($index + 1) . " berhasil dijalankan.<br>";
    } else {
        echo "ERROR Query " . ($index + 1) . ": Gagal menjalankan query: " . mysqli_error($connection) . "<br>";
    }
}

echo "<h3>Inserting All Product Data (Unified)...</h3>";

$all_products = [
    ['name' => 'Faxalen', 'price' => '4995000.00', 'vendor' => 'Boke Furniture', 'image' => 'faxalen.jpeg', 'description' => 'Mirror cabinet with built-in lights, oak effect, 60x15x95 cm'],
    ['name' => 'Pand', 'price' => '1079000.00', 'vendor' => 'Fabelio', 'image' => 'pand.jpeg', 'description' => 'Table multi-fungsional'],
    ['name' => 'Gultarp', 'price' => '145000.00', 'vendor' => 'GGS', 'image' => 'gultarp.jpeg', 'description' => 'Seat, antrasit/remmarn antrasit'],
    ['name' => 'Vihals', 'price' => '749000.00', 'vendor' => 'Kayuku', 'image' => 'vihals.jpeg', 'description' => 'Table, white 125x74 cm'],
    ['name' => 'Soderhamn', 'price' => '9995000.00', 'vendor' => 'Indonesia furniture', 'image' => 'soderhamn.jpeg', 'description' => '3-seat sofa, tonerud grey'],
    ['name' => 'Felix Accent Armchair', 'price' => '1,950,000', 'vendor' => 'IKEA', 'image' => 'Felix Accent Armchair.jpg', 'description' => 'Mid-century armchair with soft velvet fabric and gold metal legs.'],
    ['name' => 'Kyra Dining Set (4 Chairs)', 'price' => '3,790,000', 'vendor' => 'Rustika', 'image' => 'Kyra Dining Set (4 Chairs).jpg', 'description' => 'Stylish wood table with cushioned chairs, perfect for modern homes.'],
    ['name' => 'Zenno Floating Wall Shelf', 'price' => '499,000', 'vendor' => 'Rustika', 'image' => 'Zenno Floating Wall Shelf.jpg', 'description' => 'Wall-mounted shelf made of engineered wood and easy to install.'],
    ['name' => 'Chae\'s Study Table + Drawer', 'price' => '1,675,000', 'vendor' => 'WoodCraft', 'image' => 'Chaes Study Table Drawer.jpg', 'description' => 'Study desk with twice side drawer unit and smooth oak finish.'],
    ['name' => 'Verra Minimalist Coffee Table', 'price' => '1,150,000', 'vendor' => 'UrbanNest', 'image' => 'Verra Minimalist Coffee Table.jpg', 'description' => 'Round table with tempered glass top and matte black steel frame.'],
    ['name' => 'Astra Modular Wardrobe', 'price' => '3,850,000', 'vendor' => 'Rustika', 'image' => 'Astra Modular Wardrobe.jpg', 'description' => 'Customizable wardrobe with sliding doors and built-in LED lights.'],
];

$vendorMap = [];
$vendorResult = mysqli_query($connection, "SELECT vendorID, vendorName FROM vendors");
while ($row = mysqli_fetch_assoc($vendorResult)) {
    $vendorMap[$row['vendorName']] = $row['vendorID'];
}

$stmt = $connection->prepare("INSERT INTO products (productName, description, price, stock, image, vendorID) VALUES (?, ?, ?, ?, ?, ?)");
$insertCount = 0;

foreach ($all_products as $product) {
    $cleanedPrice = (float) str_replace(',', '', $product['price']);
    
    $vid = $vendorMap[$product['vendor']] ?? null;

    $stock = 50; 
    $imageName = $product['image'];
    
    if ($vid) {
        $stmt->bind_param("ssdiss", 
            $product['name'], 
            $product['description'], 
            $cleanedPrice, 
            $stock, 
            $imageName, 
            $vid
        );
        
        if ($stmt->execute()) {
            $insertCount++;
        } else {
            echo "ERROR: Failed to insert product '{$product['name']}': " . $stmt->error . "<br>";
        }
    } else {
        echo "WARNING: Could not find vendor ID for '{$product['vendor']}'. Skipping product '{$product['name']}'.<br>";
    }
}
$stmt->close();

echo "Successfully inserted $insertCount products.<br>";
echo "<hr>";
echo "Setup database selesai. Total products: " . (mysqli_num_rows(mysqli_query($connection, "SELECT productID FROM products"))) . "<br>";
echo "Akun Admin: **admin@furni.com**<br>";
echo "Password: **admin123**<br>";

mysqli_close($connection);
?>