<?php
// admin/categories.php
include '../db_connection.php';
session_start();

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle delete action
if ($action == 'delete' && $category_id > 0) {
    // Before deleting a category, update products with this category to NULL
    $stmt = $conn->prepare("UPDATE products SET category = NULL WHERE category = ?");
    $stmt->bind_param("s", $category_name);
    
    // Get category name first
    $get_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $get_stmt->bind_param("i", $category_id);
    $get_stmt->execute();
    $get_stmt->bind_result($category_name);
    $get_stmt->fetch();
    $get_stmt->close();
    
    $stmt->execute();
    $stmt->close();
    
    // Now delete the category
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Category deleted successfully.";
    header("Location: categories.php");
    exit();
}

// Handle form submission for add/edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if (empty($name)) {
        $error = "Please enter a category name.";
    } else {
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            // Update existing category
            $category_id = intval($_POST['category_id']);
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $category_id);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['message'] = "Category updated successfully.";
        } else {
            // Add new category
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['message'] = "Category added successfully.";
        }
        
        header("Location: categories.php");
        exit();
    }
}

// Fetch category for editing
$edit_category = null;
if ($action == 'edit' && $category_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $edit_category = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY name");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
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
    <title>Manage Categories - Admin Panel</title>
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" style="width: 250px;">
        <div class="text-center mb-4">
            <h4><i class="fas fa-rainbow"></i> GirlsCloset Admin</h4>
        </div>
        <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php"><i class="fas fa-tshirt"></i> Products</a>
        <a href="categories.php" class="active"><i class="fas fa-list"></i> Categories</a>
        <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Navbar -->
    <nav class="navbar-admin">
        <div class="container-fluid">
            <span class="navbar-brand">Manage Categories</span>
            <span class="navbar-text">Welcome, <?php echo $_SESSION['username']; ?></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Categories Management</h2>
                <a href="categories.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            </div>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Category Form -->
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $action == 'add' ? 'Add New Category' : 'Edit Category'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($action == 'edit'): ?>
                            <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_category ? $edit_category['name'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_category ? $edit_category['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?php echo $action == 'add' ? 'Add Category' : 'Update Category'; ?></button>
                            <a href="categories.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Categories List -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($categories) > 0): ?>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td><?php echo $category['name']; ?></td>
                                        <td><?php echo $category['description']; ?></td>
                                        <td>
                                            <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No categories found.</td>
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
</body>
</html>