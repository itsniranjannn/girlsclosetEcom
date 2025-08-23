<?php
// admin/orders.php
include '../db_connection.php';
session_start();

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle status update
if ($action == 'update_status' && $order_id > 0) {
    $new_status = isset($_POST['status']) ? $_POST['status'] : '';
    
    if (!empty($new_status)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Order status updated successfully.";
        header("Location: orders.php");
        exit();
    }
}

// Fetch order details if viewing a specific order
$order_details = null;
$order_items = [];
if ($action == 'view' && $order_id > 0) {
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $order_details = $result->fetch_assoc();
    }
    $stmt->close();
    
    // Fetch order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    if ($items_result->num_rows > 0) {
        while($row = $items_result->fetch_assoc()) {
            $order_items[] = $row;
        }
    }
    $stmt->close();
}

// Fetch all orders
$orders = [];
$result = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
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
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .product-image-admin {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
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
        <a href="categories.php"><i class="fas fa-list"></i> Categories</a>
        <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Navbar -->
    <nav class="navbar-admin">
        <div class="container-fluid">
            <span class="navbar-brand">Manage Orders</span>
            <span class="navbar-text">Welcome, <?php echo $_SESSION['username']; ?></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Orders Management</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'view' && $order_details): ?>
            <!-- Order Details -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Order #<?php echo $order_details['id']; ?></h5>
                        <span class="order-status 
                            <?php 
                            if ($order_details['status'] == 'completed') echo 'bg-success';
                            elseif ($order_details['status'] == 'processing') echo 'bg-primary';
                            elseif ($order_details['status'] == 'pending') echo 'bg-warning';
                            else echo 'bg-danger';
                            ?> text-white">
                            <?php echo ucfirst($order_details['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order_details['created_at'])); ?></p>
                            <p><strong>Customer:</strong> <?php echo $order_details['username']; ?> (<?php echo $order_details['email']; ?>)</p>
                            <p><strong>Payment Method:</strong> Credit Card</p>
                            <p><strong>Payment ID:</strong> <?php echo $order_details['stripe_payment_id'] ? substr($order_details['stripe_payment_id'], 0, 10) . '...' : 'N/A'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Shipping Address</h6>
                            <p>
                                <?php echo $order_details['username']; ?><br>
                                123 Main Street<br>
                                City, State 12345<br>
                                United States
                            </p>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $item['image_url'] ?: 'https://via.placeholder.com/60x60'; ?>" class="product-image-admin me-2" alt="<?php echo $item['name']; ?>">
                                            <div><?php echo $item['name']; ?></div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>$<?php echo number_format($order_details['total_amount'] - 5 - ($order_details['total_amount'] * 0.1), 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td>$5.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                    <td>$<?php echo number_format($order_details['total_amount'] * 0.1, 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td>$<?php echo number_format($order_details['total_amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <h6>Update Order Status</h6>
                        <form method="POST" action="orders.php?action=update_status&id=<?php echo $order_details['id']; ?>" class="d-flex gap-2 align-items-center">
                            <select name="status" class="form-select" style="width: auto;">
                                <option value="pending" <?php echo $order_details['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order_details['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $order_details['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order_details['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </div>
                    
                    <div class="mt-3">
                        <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Orders List -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($orders) > 0): ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo $order['username']; ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="order-status 
                                                <?php 
                                                if ($order['status'] == 'completed') echo 'bg-success';
                                                elseif ($order['status'] == 'processing') echo 'bg-primary';
                                                elseif ($order['status'] == 'pending') echo 'bg-warning';
                                                else echo 'bg-danger';
                                                ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No orders found.</td>
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