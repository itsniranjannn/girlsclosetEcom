<?php
// orders.php
include 'db_connection.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

// Fetch user orders
$stmt = $conn->prepare("
    SELECT o.id, o.total_amount, o.status, o.created_at, COUNT(oi.id) as items_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
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
    <title>My Orders - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
        }
        .orders-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
        }
        .order-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'navbar.php'; ?>

    <!-- Page Content -->
    <div class="container mt-5">
        <h2 class="mb-4">My Orders</h2>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                            <h5 class="mt-2"><?php echo $_SESSION['username']; ?></h5>
                        </div>
                        
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">Profile Information</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="orders.php">Order History</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="about.php">About us</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="Contact.php">Contact Info</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Orders List -->
            <div class="col-md-9">
                <div class="orders-container">
                    <h4 class="mb-4">Order History</h4>
                    
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <small class="text-muted">Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></small>
                                </div>
                                <div>
                                    <span class="order-status 
                                        <?php 
                                        if ($order['status'] == 'completed') echo 'bg-success';
                                        elseif ($order['status'] == 'processing') echo 'bg-primary';
                                        elseif ($order['status'] == 'pending') echo 'bg-warning';
                                        else echo 'bg-danger';
                                        ?> text-white">
                                        <?php echo ucfirst($order['status']);
                                        ?>
                                          </span>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Items:</strong> <?php echo $order['items_count']; ?> product(s)</p>
                                    <p class="mb-1"><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                    <?php if ($order['status'] == 'completed'): ?>
                                        <button class="btn btn-outline-secondary btn-sm">Reorder</button>
                                        <button class="btn btn-outline-secondary btn-sm">Return</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-4x mb-3 text-muted"></i>
                            <h4>No orders yet</h4>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Pagination -->
                    <?php if (count($orders) > 0): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>