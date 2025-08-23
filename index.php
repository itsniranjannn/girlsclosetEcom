<?php
// index.php for all user
include 'db_connection.php';
session_start();

// Fetch featured products
$featured_products = [];
$sql = "SELECT * FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC LIMIT 8";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Girls Clothing Store - Beautiful Clothes for Girls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
            --secondary-color: #ffefef;
            --dark-color: #333;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #555;
            font-weight: 500;
        }
        .nav-link:hover {
            color: var(--primary-color);
        }
        .hero-section {
            background: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), url('https://images.unsplash.com/photo-1534452203293-494d7ddbf7e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            text-align: center;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .section-title {
            position: relative;
            margin-bottom: 40px;
            text-align: center;
        }
        .section-title:after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            margin: 15px auto;
        }
        .product-card {
            border: none;
            transition: transform 0.3s;
            margin-bottom: 30px;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 250px;
            object-fit: cover;
        }
        .product-price {
            color: var(--primary-color);
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 40px 0;
            margin-top: 60px;
        }
        .newsletter-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }
        .social-icons a {
            color: #555;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        .social-icons a:hover {
            color: var(--primary-color);
        }
        /* hover in a tags in footer */
        .footer a:hover {
            color: var(--primary-color);
            
        }

        .footer a {
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-rainbow"></i> GirlsCloset
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">New Arrivals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#category">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About US</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold">Beautiful Clothes for Beautiful Girls</h1>
            <p class="lead mb-4">Discover the latest trends in girls' fashion and find the perfect outfits for every occasion.</p>
            <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="row">
                <?php foreach ($featured_products as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card">
                        <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300'; ?>" class="card-img-top product-image" alt="<?php echo $product['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="product-price">Rs <?php echo number_format($product['price'], 2); ?></p>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                            <a href="cart-action.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-outline-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5 bg-light">
        <div id="category" class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4">
                    <div class="category-item p-4 bg-white rounded shadow-sm">
                        <i class="fas fa-tshirt fa-2x mb-3 text-primary"></i>
                        <h5>Tops</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="category-item p-4 bg-white rounded shadow-sm">
                        <i class="fas fa-vest fa-2x mb-3 text-primary"></i>
                        <h5>Dresses</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="category-item p-4 bg-white rounded shadow-sm">
                        <i class="fas fa-socks fa-2x mb-3 text-primary"></i>
                        <h5>Bottoms</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="category-item p-4 bg-white rounded shadow-sm">
                        <i class="fas fa-mitten fa-2x mb-3 text-primary"></i>
                        <h5>Accessories</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3>Subscribe to Our Newsletter</h3>
                    <p>Get updates on new arrivals, special offers, and more.</p>
                    <form class="newsletter-form d-flex mt-4">
                        <input type="email" class="form-control me-2" placeholder="Your email address">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>GirlsCloset</h5>
                    <p>We offer the latest trends in girls' clothing with a focus on quality, style, and affordability.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>Shop</h5>
                    <ul class="list-unstyled ">
                        <li><a href="#">New Arrivals</a></li>
                        <li><a href="#">Dresses</a></li>
                        <li><a href="#">Tops</a></li>
                        <li><a href="#">Bottoms</a></li>
                        <li><a href="#">Accessories</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Fashion Street, City, Country</p>
                    <p><i class="fas fa-phone me-2"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope me-2"></i> info@girlscloset.com</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2023 GirlsCloset. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
