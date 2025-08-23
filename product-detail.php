<?php
// product-detail.php
include 'db_connection.php';
session_start();

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// Fetch related products
$related_products = [];
$stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND stock_quantity > 0 ORDER BY RAND() LIMIT 4");
$stmt->bind_param("si", $product['category'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();

if ($related_result->num_rows > 0) {
    while($row = $related_result->fetch_assoc()) {
        $related_products[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
        }
        .product-image {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-price {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: bold;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .quantity-input {
            width: 70px;
            text-align: center;
        }
        .related-product {
            transition: transform 0.3s;
        }
        .related-product:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'navbar.php'; ?>

    <!-- Page Content -->
    <div class="container mt-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6">
                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/500x600'; ?>" class="product-image" alt="<?php echo $product['name']; ?>">
            </div>
            
            <!-- Product Details -->
            <div class="col-md-6">
                <h1><?php echo $product['name']; ?></h1>
                <div class="product-price mb-3">Rs <?php echo number_format($product['price'], 2); ?></div>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star-half-alt text-warning"></i>
                        <span class="ms-1">(24 reviews)</span>
                    </div>
                </div>
                
                <p class="mb-4"><?php echo $product['description']; ?></p>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Size</label>
                        <select class="form-select">
                            <option selected>Select Size</option>
                            <option>XS</option>
                            <option>S</option>
                            <option>M</option>
                            <option>L</option>
                            <option>XL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control quantity-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <a href="cart-action.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-lg me-md-2">
                            <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg me-md-2" disabled>
                            <i class="fas fa-times-circle me-2"></i> Out of Stock
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-heart me-2"></i> Add to Wishlist
                    </button>
                </div>
                
                <hr class="my-4">
                
                <div class="product-details">
                    <h5>Product Details</h5>
                    <ul>
                        <li>Category: <?php echo $product['category']; ?></li>
                        <li>Material: 100% Cotton</li>
                        <li>Care: Machine wash cold</li>
                        <li>SKU: GC<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Product Reviews -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Customer Reviews</h3>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Product Reviews</h5>
                            <button class="btn btn-primary">Write a Review</button>
                        </div>
                        
                        <div class="review-item mt-4">
                            <div class="d-flex justify-content-between">
                                <h6>Sarah Johnson</h6>
                                <div>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                            </div>
                            <p class="text-muted">Posted on January 15, 2023</p>
                            <p>My daughter loves this dress! The quality is excellent and it fits perfectly. Will definitely buy again.</p>
                        </div>
                        
                        <div class="review-item mt-4">
                            <div class="d-flex justify-content-between">
                                <h6>Emily Davis</h6>
                                <div>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                </div>
                            </div>
                            <p class="text-muted">Posted on December 5, 2022</p>
                            <p>Beautiful dress, but the sizing runs a bit small. I would recommend ordering one size up.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (count($related_products) > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>Related Products</h3>
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card related-product">
                            <img src="<?php echo $related['image_url'] ?: 'https://via.placeholder.com/300x300'; ?>" class="card-img-top" alt="<?php echo $related['name']; ?>" style="height: 250px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $related['name']; ?></h5>
                                <p class="card-text product-price">$<?php echo number_format($related['price'], 2); ?></p>
                                <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>