<?php
// admin/products.php
include '../db_connection.php';
session_start();

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle delete action
if ($action == 'delete' && $product_id > 0) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Product deleted successfully.";
    header("Location: products.php");
    exit();
}

// Handle form submission for add/edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = isset($_POST['category']) ? intval($_POST['category']) : NULL;
    $size = trim($_POST['size']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $image_url = trim($_POST['image_url']);
    $upload_success = false;

    // Handle local image upload (priority over URL)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $max_size = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, PNG, GIF, or WEBP images are allowed.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = "File size must be less than 2MB.";
        } else {
            $upload_dir = "../uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = "uploads/" . $file_name; // save relative path
                $upload_success = true;
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        }
    }

    // If no image uploaded and no URL provided → use placeholder
    if (!$upload_success && empty($image_url)) {
        $image_url = "https://via.placeholder.com/300x300";
    }

    // Validate required fields
    if (empty($name) || empty($description) || $price <= 0) {
        $error = "Please fill all required fields correctly.";
    } else {
        if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
            // Update existing product
            $product_id = intval($_POST['product_id']);
            
            // If no new image provided, keep the existing one
            if (!$upload_success && empty($_POST['image_url'])) {
                $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $stmt->bind_result($existing_image);
                $stmt->fetch();
                $stmt->close();
                
                if (!empty($existing_image)) {
                    $image_url = $existing_image;
                }
            }
            
            $stmt = $conn->prepare("UPDATE products 
                SET name = ?, description = ?, price = ?, category = ?, size = ?, stock_quantity = ?, image_url = ? 
                WHERE id = ?");
            // ✅ Corrected binding: image_url must be string "s"
            $stmt->bind_param("ssdisisi", $name, $description, $price, $category, $size, $stock_quantity, $image_url, $product_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Product updated successfully.";
            } else {
                $error = "Error updating product: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Add new product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, size, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            // ✅ Corrected binding: image_url must be string "s"
            $stmt->bind_param("ssdisis", $name, $description, $price, $category, $size, $stock_quantity, $image_url);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Product added successfully.";
            } else {
                $error = "Error adding product: " . $stmt->error;
            }
            $stmt->close();
        }
        
        if (!isset($error)) {
            header("Location: products.php");
            exit();
        }
    }
}

// Fetch product for editing
$edit_product = null;
if ($action == 'edit' && $product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $edit_product = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all products with category names
$products = [];
$result = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category = c.id 
    ORDER BY p.created_at DESC
");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
if ($cat_result->num_rows > 0) {
    while($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar a {
            color: #ddd;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: #495057;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar-admin {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 20px;
            margin-left: 250px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .product-image-admin {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .current-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" style="width: 250px;">
        <div class="text-center mb-4">
            <h4><i class="fas fa-rainbow"></i> GirlsCloset Admin</h4>
        </div>
        <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php" class="active"><i class="fas fa-tshirt"></i> Products</a>
        <a href="categories.php"><i class="fas fa-list"></i> Categories</a>
        <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Navbar -->
    <nav class="navbar-admin">
        <div class="container-fluid">
            <span class="navbar-brand">Manage Products</span>
            <span class="navbar-text">Welcome, <?php echo $_SESSION['username']; ?></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Products Management</h2>
                <a href="products.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Product Form -->
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $action == 'add' ? 'Add New Product' : 'Edit Product'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php if ($action == 'edit'): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price (Rs)</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_product && $edit_product['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="size" class="form-label">Size</label>
                                <select class="form-select" id="size" name="size">
                                    <option value="">Select Size</option>
                                    <option value="XS" <?php echo ($edit_product && $edit_product['size'] == 'XS') ? 'selected' : ''; ?>>XS</option>
                                    <option value="S" <?php echo ($edit_product && $edit_product['size'] == 'S') ? 'selected' : ''; ?>>S</option>
                                    <option value="M" <?php echo ($edit_product && $edit_product['size'] == 'M') ? 'selected' : ''; ?>>M</option>
                                    <option value="L" <?php echo ($edit_product && $edit_product['size'] == 'L') ? 'selected' : ''; ?>>L</option>
                                    <option value="XL" <?php echo ($edit_product && $edit_product['size'] == 'XL') ? 'selected' : ''; ?>>XL</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo $edit_product ? $edit_product['stock_quantity'] : '0'; ?>" required min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image (Local)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Max file size: 2MB. Allowed formats: JPG, PNG, GIF, WEBP</div>
                            
                            <?php if ($edit_product && !empty($edit_product['image_url'])): ?>
                                <div class="mt-2">
                                    <p>Current Image:</p>
                                    <?php if (strpos($edit_product['image_url'], 'http') === 0): ?>
                                        <img src="<?php echo $edit_product['image_url']; ?>" class="current-image" alt="Current product image">
                                    <?php else: ?>
                                        <img src="../<?php echo $edit_product['image_url']; ?>" class="current-image" alt="Current product image">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="image_url" class="form-label">Or Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_url']) : ''; ?>" placeholder="https://example.com/image.jpg">
                            <div class="form-text">Leave both image fields empty to use default placeholder</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?php echo $action == 'add' ? 'Add Product' : 'Update Product'; ?></button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Products List -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($products) > 0): ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($product['image_url'])): ?>
                                                <?php if (strpos($product['image_url'], 'http') === 0): ?>
                                                    <img src="<?php echo $product['image_url']; ?>" class="product-image-admin" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <?php else: ?>
                                                    <img src="../<?php echo $product['image_url']; ?>" class="product-image-admin" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/60x60" class="product-image-admin" alt="No image">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo $product['category_name'] ? htmlspecialchars($product['category_name']) : 'Uncategorized'; ?></td>
                                        <td>Rs <?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No products found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple validation to ensure either file upload or URL is provided, but not necessarily both
        document.querySelector('form').addEventListener('submit', function(e) {
            const imageFile = document.getElementById('image').files[0];
            const imageUrl = document.getElementById('image_url').value;
            
            if (!imageFile && !imageUrl) {
                // This is okay - we'll use the placeholder
                return true;
            }
            
            // Additional validation can be added here if needed
        });
    </script>
</body>
</html> 