<?php
// order-details.php
include 'db_connection.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header("Location: orders.php");
    exit();
}

// Fetch order details
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items
$order_items = [];
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
        }
        .order-details-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .product-image-order {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 20px 0;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #ddd;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
        }
        .timeline-item.completed:before {
            background-color: #28a745;
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
                <li class="breadcrumb-item"><a href="orders.php">My Orders</a></li>
                <li class="breadcrumb-item active">Order #<?php echo $order['id']; ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-8">
                <div class="order-details-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Order #<?php echo $order['id']; ?></h3>
                        <span class="order-status 
                            <?php 
                            if ($order['status'] == 'completed') echo 'bg-success';
                            elseif ($order['status'] == 'processing') echo 'bg-primary';
                            elseif ($order['status'] == 'pending') echo 'bg-warning';
                            else echo 'bg-danger';
                            ?> text-white">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Details</h5>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                            <p><strong>Payment Method:</strong> Credit Card</p>
                            <p><strong>Payment ID:</strong> <?php echo $order['stripe_payment_id'] ? substr($order['stripe_payment_id'], 0, 10) . '...' : 'N/A'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Shipping Address</h5>
                            <p>
                                <?php echo $_SESSION['username']; ?><br>
                                123 Main Street<br>
                                City, State 12345<br>
                                United States
                            </p>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Order Items</h5>
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="row">
                            <div class="col-md-2">
                                <img src="<?php echo $item['image_url'] ?: 'https://via.placeholder.com/80x80'; ?>" class="product-image-order" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-6">
                                <h6><?php echo $item['name']; ?></h6>
                                <p class="text-muted">Size: M</p>
                            </div>
                            <div class="col-md-2">
                                <p>Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="col-md-2 text-end">
                                <p>$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($order['total_amount'] - 5 - ($order['total_amount'] * 0.1), 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>$5.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$<?php echo number_format($order['total_amount'] * 0.1, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item <?php echo $order['status'] != 'pending' ? 'completed' : ''; ?>">
                                <h6>Order Placed</h6>
                                <p class="text-muted"><?php echo date('M j, g:i a', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="timeline-item <?php echo $order['status'] == 'processing' || $order['status'] == 'completed' ? 'completed' : ''; ?>">
                                <h6>Order Confirmed</h6>
                                <p class="text-muted"><?php echo $order['status'] != 'pending' ? date('M j, g:i a', strtotime($order['created_at']) + 3600) : 'Pending'; ?></p>
                            </div>
                            <div class="timeline-item <?php echo $order['status'] == 'processing' || $order['status'] == 'completed' ? 'completed' : ''; ?>">
                                <h6>Processing</h6>
                                <p class="text-muted"><?php echo $order['status'] != 'pending' ? date('M j, g:i a', strtotime($order['created_at']) + 7200) : 'Pending'; ?></p>
                            </div>
                            <div class="timeline-item <?php echo $order['status'] == 'completed' ? 'completed' : ''; ?>">
                                <h6>Shipped</h6>
                                <p class="text-muted"><?php echo $order['status'] == 'completed' ? date('M j, g:i a', strtotime($order['created_at']) + 10800) : 'Pending'; ?></p>
                            </div>
                            <div class="timeline-item <?php echo $order['status'] == 'completed' ? 'completed' : ''; ?>">
                                <h6>Delivered</h6>
                                <p class="text-muted"><?php echo $order['status'] == 'completed' ? date('M j, g:i a', strtotime($order['created_at']) + 14400) : 'Pending'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p>If you have any questions about your order, please contact our customer service.</p>
                        <div class="d-grid gap-2">
                            <a href="contact.php" class="btn btn-outline-primary">Contact Support</a>
                            <button class="btn btn-outline-secondary">Track Package</button>
                            <?php if ($order['status'] == 'completed'): ?>
                                <button class="btn btn-outline-secondary">Return Items</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>