<nav class="navbar navbar-expand-lg navbar-light sticky-top" style="background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div class="container">
        <a class="navbar-brand" href="index.php" style="color: #ff69b4; font-weight: bold; font-size: 1.5rem;">
            <i class="fas fa-rainbow"></i> GirlsCloset
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">Products</a>
                </li>
           <li class="nav-item">
    <a class="nav-link" href="products.php?category=1">Dresses</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="products.php?category=2">Tops</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="products.php?category=3">Bottoms</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="products.php?category=4">Accessories</a>
</li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php
                            include 'db_connection.php';
                            $user_id = $_SESSION['user_id'];
                            $cart_count = 0;
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $cart_count = $row['count'];
                            }
                            $stmt->close();
                            $conn->close();
                            if ($cart_count > 0) {
                                echo '<span class="badge bg-primary rounded-pill">' . $cart_count . '</span>';
                            }
                            ?>
                        </a>
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
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>