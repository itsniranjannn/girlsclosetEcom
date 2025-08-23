<?php
// admin/index.php for admin use
session_start();
include '../db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$users_count = 0;
$products_count = 0;
$orders_count = 0;
$recent_orders = [];

// Count users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $users_count = $row['count'];
}
$stmt->close();

// Count products
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $products_count = $row['count'];
}
$stmt->close();

// Count orders
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $orders_count = $row['count'];
}
$stmt->close();

// Get recent orders
$stmt = $conn->prepare("
    SELECT o.id, o.total_amount, o.status, o.created_at, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
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
    <title>Admin Dashboard - GirlsCloset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
            --secondary-color: #ffc2dd;
            --dark-color: #333;
            --light-color: #f8f9fa;
        }
        body {
            background-color: #f5f5f5;
        }
        .sidebar {
            background-color: var(--dark-color);
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stat-card h2 {
            font-size: 2.5rem;
            margin: 10px 0;
        }
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-warning {
            background-color: #ffc107;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .quick-action {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }
        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        }
        .quick-action i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        .admin-header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h3><i class="fas fa-rainbow"></i> GirlsCloset Admin</h3>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-tshirt"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-list"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h2>Dashboard Overview</h2>
                    <div>
                        <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <i class="fas fa-users text-primary"></i>
                            <h2><?php echo $users_count; ?></h2>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <i class="fas fa-tshirt text-info"></i>
                            <h2><?php echo $products_count; ?></h2>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <i class="fas fa-shopping-cart text-success"></i>
                            <h2><?php echo $orders_count; ?></h2>
                            <p>Total Orders</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Recent Orders</span>
                                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($recent_orders) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo $order['username']; ?></td>
                                                <td>Rs.<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php 
                                                    $badge_class = 'badge-warning';
                                                    if ($order['status'] == 'completed') $badge_class = 'badge-success';
                                                    if ($order['status'] == 'cancelled') $badge_class = 'badge-danger';
                                                    if ($order['status'] == 'shipped') $badge_class = 'badge-info';
                                                    ?>
                                                    <span class="badge badge-status <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted my-4">No orders found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions & Recent Activity -->
                    <div class="col-md-4">
                        <!-- Quick Actions -->
                        <div class="card mb-4">
                            <div class="card-header">Quick Actions</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="quick-action" onclick="window.location.href='products.php?action=add'">
                                            <i class="fas fa-plus-circle"></i>
                                            <p>Add New Product</p>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="quick-action" onclick="window.location.href='orders.php'">
                                            <i class="fas fa-shopping-cart"></i>
                                            <p>View Orders</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="quick-action" onclick="window.location.href='users.php'">
                                            <i class="fas fa-user-cog"></i>
                                            <p>Manage Users</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="quick-action" onclick="window.location.href='categories.php'">
                                            <i class="fas fa-list"></i>
                                            <p>Categories</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Activity -->
                        <div class="card">
                            <div class="card-header">Recent Activity</div>
                            <div class="card-body">
                                <div class="activity-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>New order received</strong>
                                        <small class="text-muted">2 hours ago</small>
                                    </div>
                                    <p class="mb-0">Order #1052 for Rs.45.99</p>
                                </div>
                                <div class="activity-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>New user registered</strong>
                                        <small class="text-muted">5 hours ago</small>
                                    </div>
                                    <p class="mb-0">Sarah Johnson joined</p>
                                </div>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>Product updated</strong>
                                        <small class="text-muted">Yesterday</small>
                                    </div>
                                    <p class="mb-0">Summer Dress stock increased</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
