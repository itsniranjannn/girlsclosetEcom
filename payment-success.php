<?php
session_start();
include 'db_connection.php';
require 'vendor/autoload.php';

// Set your Stripe secret key
$stripeSecretKey = 'sk_test_51Ryl1YKoAgVcK5IZcfYBRVbBJ0kHn81oIGsEQcjq1JIFE5XhPh4BkdMIGCYWT21rLvARbGjFZdatQrupPzh8MQED00kes8QadD';
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Initialize variables
$success = false;
$message = '';
$order_id = 0;
$order_details = [];
$user_id = $_SESSION['user_id'] ?? 0;

// Check if user is logged in
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Get session ID from URL
$session_id = $_GET['session_id'] ?? '';

if ($session_id) {
    try {
        // Retrieve the session from Stripe
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        
        if ($session->payment_status == 'paid') {
            // Get order ID from session or database
            $order_id = $_SESSION['current_order_id'] ?? 0;
            
            if (!$order_id) {
                // Try to find order by stripe_session_id
                $stmt = $conn->prepare("SELECT id FROM orders WHERE stripe_payment_id = ? OR id = (SELECT order_id FROM payments WHERE stripe_session_id = ?)");
                $stmt->bind_param("ss", $session->payment_intent, $session_id);
                $stmt->execute();
                $stmt->bind_result($order_id);
                $stmt->fetch();
                $stmt->close();
            }
            
            if ($order_id) {
                // Update order status
                $stmt = $conn->prepare("UPDATE orders SET status = 'completed', stripe_payment_id = ? WHERE id = ?");
                $stmt->bind_param("si", $session->payment_intent, $order_id);
                $stmt->execute();
                
                // Update payment status
                $stmt = $conn->prepare("UPDATE payments SET payment_status = 'succeeded', stripe_payment_intent = ? WHERE order_id = ?");
                $stmt->bind_param("si", $session->payment_intent, $order_id);
                $stmt->execute();
                
                // Clear the cart
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                
                // Get order details for display
                $stmt = $conn->prepare("
                    SELECT o.id, o.total_amount, o.created_at, o.shipping_address, 
                           p.name, p.image_url, oi.quantity, oi.price
                    FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    JOIN products p ON oi.product_id = p.id
                    WHERE o.id = ?
                ");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $order_details = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                
                // Clear session order ID
                unset($_SESSION['current_order_id']);
                
                $success = true;
                $message = "Your payment was successful! Your order will be shipped soon.";
            } else {
                $success = false;
                $message = "Order not found. Please contact support with this reference: " . $session_id;
            }
        } else {
            $success = false;
            $message = "Payment not completed. Please try again.";
        }
    } catch (Exception $e) {
        $success = false;
        $message = "Error verifying payment: " . $e->getMessage();
        error_log("Payment Verification Error: " . $e->getMessage());
    }
} else {
    $success = false;
    $message = "Invalid payment session. Please try again or contact support.";
}

// If payment failed, redirect to failed page
if (!$success) {
    header("Location: payment-failed.php?message=" . urlencode($message) . "&session_id=" . urlencode($session_id));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Successful - Girls Clothing Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root { 
    --primary-color: #ff69b4; 
    --secondary-color: #ffc2e2;
}
body { background-color: #f8f9fa; }
.status-container { max-width: 800px; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.status-icon { font-size: 5rem; }
.order-item { border-bottom: 1px solid #eee; padding: 15px 0; }
.order-item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
.confirmation-email { background-color: #f8f9fa; border-radius: 10px; padding: 20px; }
.timeline { position: relative; padding-left: 30px; margin: 20px 0; }
.timeline::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background-color: var(--primary-color); }
.timeline-step { position: relative; margin-bottom: 25px; }
.timeline-step::before { content: ''; position: absolute; left: -30px; top: 5px; width: 16px; height: 16px; border-radius: 50%; background-color: var(--primary-color); }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
    <div class="status-container mx-auto">
        <div class="text-center mb-5">
            <i class="fas fa-check-circle status-icon text-success mb-4"></i>
            <h2 class="mb-3">Payment Successful!</h2>
            <p class="lead mb-4"><?php echo $message; ?></p>
            <p class="text-muted mb-4">Order ID: #<?php echo $order_id; ?></p>
        </div>

        <?php if (!empty($order_details)): ?>
        <div class="row mb-5">
            <div class="col-md-6">
                <h4 class="mb-4"><i class="fas fa-box me-2"></i>Order Details</h4>
                <?php foreach ($order_details as $item): ?>
                <div class="order-item d-flex align-items-center">
                    <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="order-item-img me-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                        <p class="text-muted mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0">Rs <?php echo number_format($item['price'], 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="order-item pt-3">
                    <div class="d-flex justify-content-between fw-bold pt-2 border-top">
                        <strong>Total Amount</strong>
                        <strong>Rs <?php echo number_format($order_details[0]['total_amount'], 2); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <h4 class="mb-4"><i class="fas fa-truck me-2"></i>Shipping Information</h4>
                <p class="mb-2"><strong>Shipping Address:</strong></p>
                <p class="mb-3"><?php echo nl2br($order_details[0]['shipping_address']); ?></p>
                
                <p class="mb-2"><strong>Order Date:</strong></p>
                <p class="mb-3"><?php echo date('F j, Y, g:i a', strtotime($order_details[0]['created_at'])); ?></p>
                
                <p class="mb-2"><strong>Estimated Delivery:</strong></p>
                <p class="mb-0"><?php echo date('F j, Y', strtotime($order_details[0]['created_at'] . ' + 5 days')); ?> (5-7 business days)</p>
            </div>
        </div>
        <?php endif; ?>


        <div class="mb-5">
            <h4 class="mb-4"><i class="fas fa-list-alt me-2"></i>Order Timeline</h4>
            <div class="timeline">
                <div class="timeline-step">
                    <h6 class="mb-1">Order Placed</h6>
                    <p class="text-muted mb-0"><?php echo date('F j, g:i a'); ?></p>
                </div>
                <div class="timeline-step">
                    <h6 class="mb-1">Payment Confirmed</h6>
                    <p class="text-muted mb-0"><?php echo date('F j, g:i a'); ?></p>
                </div>
                <div class="timeline-step">
                    <h6 class="mb-1">Processing Order</h6>
                    <p class="text-muted mb-0">Expected within 24 hours</p>
                </div>
                <div class="timeline-step">
                    <h6 class="mb-1">Shipped</h6>
                    <p class="text-muted mb-0">Expected in 1-2 business days</p>
                </div>
                <div class="timeline-step">
                    <h6 class="mb-1">Delivery</h6>
                    <p class="text-muted mb-0">Expected in 5-7 business days</p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-muted mb-4">Need help with your order? <a href="contact.php">Contact our support team</a></p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="orders.php" class="btn btn-primary me-md-2"><i class="fas fa-list me-2"></i>View All Orders</a>
                <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-shopping-bag me-2"></i>Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>