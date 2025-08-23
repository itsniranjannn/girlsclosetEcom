<?php
// products.php
include 'db_connection.php';
session_start();

// Get filters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$size_filter = isset($_GET['size']) ? $_GET['size'] : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;

// Build SQL query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category = c.id 
        WHERE p.stock_quantity > 0 
        AND p.price BETWEEN ? AND ?";

$params = [$min_price, $max_price];
$types = "dd";

if ($category_filter > 0) {
    $sql .= " AND p.category = ?";
    $types .= "i";
    $params[] = $category_filter;
}

if (!empty($size_filter)) {
    $sql .= " AND p.size = ?";
    $types .= "s";
    $params[] = $size_filter;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get categories
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

// Sizes
$sizes = ['XS','S','M','L','XL'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #ff69b4; }
        .filter-section { background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px; }
        .product-card { border: none; transition: transform 0.3s; margin-bottom: 30px; height: 100%; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-image { height: 250px; object-fit: cover; }
        .product-price { color: var(--primary-color); font-weight: bold; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: #ff1493; border-color: #ff1493; }
        .category-filter li, .size-filter li { margin-bottom: 10px; }
        .category-filter a, .size-filter a { color: #555; text-decoration: none; }
        .category-filter a:hover, .category-filter a.active, 
        .size-filter a:hover, .size-filter a.active { color: var(--primary-color); font-weight: bold; }
        .price-labels { display: flex; justify-content: space-between; margin-bottom: 5px; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row">
        <!-- Filters -->
        <div class="col-md-3">
            <div class="filter-section">
                <h5>Categories</h5>
                <ul class="category-filter list-unstyled">
                    <li><a href="products.php?min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>" class="<?php echo empty($category_filter) ? 'active' : ''; ?>">All Products</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="products.php?category=<?php echo $cat['id']; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&size=<?php echo $size_filter; ?>" class="<?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>

                <h5 class="mt-4">Sizes</h5>
                <ul class="size-filter list-unstyled">
                    <li><a href="products.php?category=<?php echo $category_filter; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>" class="<?php echo empty($size_filter) ? 'active' : ''; ?>">All Sizes</a></li>
                    <?php foreach ($sizes as $size): ?>
                        <li><a href="products.php?category=<?php echo $category_filter; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&size=<?php echo $size; ?>" class="<?php echo $size_filter == $size ? 'active' : ''; ?>">
                            <?php echo $size; ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>

                <h5 class="mt-4">Price Range</h5>
                <form method="get" id="priceFilterForm">
                    <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                    <input type="hidden" name="size" value="<?php echo $size_filter; ?>">

                    <div class="price-labels">
                        <span>Rs <?php echo $min_price; ?></span>
                        <span>Rs <?php echo $max_price; ?></span>
                    </div>
                    <input type="range" name="min_price" class="form-range" min="0" max="10000" step="100" id="minPrice" value="<?php echo $min_price; ?>" oninput="updateMinPrice(this.value)">
                    <input type="range" name="max_price" class="form-range" min="0" max="10000" step="100" id="maxPrice" value="<?php echo $max_price; ?>" oninput="updateMaxPrice(this.value)">
                    <button type="submit" class="btn btn-primary mt-3 w-100">Apply Price Filter</button>
                </form>
            </div>
        </div>

        <!-- Products -->
        <div class="col-md-9">
            <div class="row">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card product-card">
                                <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/300x300'; ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="product-price">Rs<?php echo number_format($product['price'], 2); ?></p>
                                    <div class="d-flex justify-content-between">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">Details</a>
                                        <a href="cart-action.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                        <h4>No products found</h4>
                        <p>Try adjusting your filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateMinPrice(val) {
    document.querySelector('.price-labels span:first-child').innerText = 'Rs ' + val;
}
function updateMaxPrice(val) {
    document.querySelector('.price-labels span:last-child').innerText = 'Rs ' + val;
}
</script>
</body>
</html>
