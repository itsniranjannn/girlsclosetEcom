<?php
session_start();
include 'db_connection.php';
require 'vendor/autoload.php';

// Set your Stripe keys - MAKE SURE THESE ARE CORRECT
$stripeSecretKey = 'sk_test_51Ryl1YKoAgVcK5IZcfYBRVbBJ0kHn81oIGsEQcjq1JIFE5XhPh4BkdMIGCYWT21rLvARbGjFZdatQrupPzh8MQED00kes8QadD';
$stripePublicKey = 'pk_test_51Ryl1YKoAgVcK5IZMuUoZ9khUYTIYESgfaEGHuDzW1Bzt4YoHkfxMUATxz3GUMryzhcHo1QDZAOS3KvNdymLDWyu00KUspbXvL';

// Initialize variables
$error = '';
$checkout_session = null;
$debug_info = '';

// Check if Stripe PHP library is loaded
if (!class_exists('Stripe\Stripe')) {
    $error = "Stripe PHP library not found. Make sure to run 'composer require stripe/stripe-php'";
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Test database connection
if (!$conn || $conn->connect_error) {
    $error = "Database connection failed: " . ($conn ? $conn->connect_error : "Unknown error");
}

// Fetch user details from DB
if (empty($error)) {
    $stmt = $conn->prepare("SELECT username, email, address, city, country FROM users WHERE id = ?");
    if (!$stmt) {
        $error = "Database prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            $error = "Database execute failed: " . $stmt->error;
        } else {
            $stmt->bind_result($username, $email, $address, $city, $country);
            $stmt->fetch();
            $stmt->close();
            
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid user email. Please update your profile.";
            }
        }
    }
}

// Fetch cart items if no error
if (empty($error)) {
    $stmt = $conn->prepare("
        SELECT c.product_id, p.name, p.price, c.quantity, p.image_url 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    
    if (!$stmt) {
        $error = "Database prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            $error = "Database execute failed: " . $stmt->error;
        } else {
            $result = $stmt->get_result();
            $cart_items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (empty($cart_items)) {
                $error = "Your cart is empty.";
            }
        }
    }
}

// Process payment if no errors
if (empty($error)) {
    // Set Stripe API key with error handling
    try {
        \Stripe\Stripe::setApiKey($stripeSecretKey);
        
        // Test Stripe connection by making a simple request
        \Stripe\Balance::retrieve();
        
    } catch (Exception $e) {
        $error = "Stripe connection failed: " . $e->getMessage();
        error_log("Stripe Connection Error: " . $e->getMessage());
    }
}

if (empty($error)) {
    // Prepare Stripe line items
    $line_items = [];
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'npr',
                'product_data' => [
                    'name' => $item['name'],
                    'images' => [$item['image_url']],
                ],
                'unit_amount' => $item['price'] * 100, // Stripe expects cents
            ],
            'quantity' => $item['quantity'],
        ];
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Calculate additional costs
    $shipping = 50; // flat shipping
    $tax = round($total_amount * 0.01, 2); // 1% tax
    $grand_total = $total_amount + $shipping + $tax;

    // Prepare shipping address string
    $shipping_address = implode(", ", array_filter([
        $address, 
        $city, 
        $country
    ]));

    try {
        // Create Stripe checkout session
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'success_url' => $baseUrl . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => $baseUrl . '/cart.php',
    'customer_email' => $email,
    'metadata' => [
        'user_id' => $user_id
    ],
    'shipping_options' => [
        [
            'shipping_rate_data' => [
                'type' => 'fixed_amount',
                'fixed_amount' => [
                    'amount' => $shipping * 100,
                    'currency' => 'npr',
                ],
                'display_name' => 'Standard shipping',
            ]
        ]
    ],
]);


        // Insert order with shipping information
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, created_at) VALUES (?, ?, 'pending', ?, NOW())");
        $stmt->bind_param("ids", $user_id, $grand_total, $shipping_address);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }
        $stmt->close();

        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, amount, currency, payment_status, stripe_session_id, created_at) VALUES (?, ?, ?, 'npr', 'pending', ?, NOW())");
        $stmt->bind_param("iids", $order_id, $user_id, $grand_total, $checkout_session->id);
        $stmt->execute();
        $stmt->close();

        // Store order ID in session for later reference
        $_SESSION['current_order_id'] = $order_id;

    } catch (Exception $e) {
        $error = "Error creating checkout session: " . $e->getMessage();
        error_log("Stripe Session Creation Error: " . $e->getMessage());
        
        // Add debug information
        $debug_info = "Error Type: " . get_class($e) . "\n";
        $debug_info .= "Error Message: " . $e->getMessage() . "\n";
        if (method_exists($e, 'getJsonBody')) {
            $debug_info .= "Stripe Error: " . print_r($e->getJsonBody(), true) . "\n";
        }
    }
}

// For debugging - check if we can output information
$debug_mode = true; // Set to false in production
if ($debug_mode && !empty($debug_info)) {
    error_log("Checkout Debug: " . $debug_info);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Girls Clothing Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://js.stripe.com/v3/"></script>
<style>
:root { 
    --primary-color: #ff69b4; 
    --secondary-color: #ffc2e2;
}
body { background-color: #f8f9fa; }
.checkout-container { background-color: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.summary-card { background-color: white; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
.btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
.btn-primary:hover { background-color: #ff1493; border-color: #ff1493; }
.checkout-item { border-bottom: 1px solid #eee; padding: 15px 0; }
.item-image { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
.address-section { background-color: #f8f9fa; border-radius: 10px; padding: 20px; }
.debug-info { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; font-family: monospace; white-space: pre-wrap; }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
    <h2 class="mb-4 text-center">Checkout</h2>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <h4><i class="fas fa-exclamation-triangle me-2"></i>Payment Error</h4>
        <p class="mb-3"><?php echo $error; ?></p>
        
        <?php if ($debug_mode && !empty($debug_info)): ?>
        <div class="debug-info">
            <h5>Debug Information:</h5>
            <?php echo htmlspecialchars($debug_info); ?>
        </div>
        <?php endif; ?>
        
        <div class="mt-3">
            <p>Please try the following:</p>
            <ul>
                <li>Check your internet connection</li>
                <li>Verify your payment details</li>
                <li>Try again in a few minutes</li>
                <li>Contact support if the problem persists</li>
            </ul>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="cart.php" class="btn btn-primary me-2"><i class="fas fa-shopping-cart me-2"></i>Return to Cart</a>
        <a href="contact.php" class="btn btn-outline-primary"><i class="fas fa-headset me-2"></i>Contact Support</a>
    </div>
    
    <?php else: ?>
    <div class="row g-4">
        <!-- Order summary and address sections remain the same as before -->
        <div class="col-lg-5">
            <div class="summary-card mb-4">
                <h4 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>Order Summary</h4>
                <?php foreach ($cart_items as $item): ?>
                <div class="checkout-item d-flex align-items-center">
                    <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="item-image me-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                        <p class="text-muted mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0">Rs <?php echo number_format($item['price'], 2); ?></p>
                        <p class="mb-0"><strong>Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="checkout-item pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rs <?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>Rs <?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (1%)</span>
                        <span>Rs <?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold pt-2 border-top">
                        <strong>Total</strong>
                        <strong>Rs <?php echo number_format($grand_total, 2); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="address-section">
                <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h5>
                <p class="mb-2"><strong><?php echo $username; ?></strong></p>
                <p class="mb-2">
                    <?php 
                    echo implode(', ', array_filter([$address, $city]));
                    if ($country) echo '<br>' . $country;
                    ?>
                </p>
                <p class="mb-0"><strong>Email:</strong> <?php echo $email; ?></p>
                <a href="profile.php" class="btn btn-outline-primary btn-sm mt-3">Change Address</a>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="checkout-container">
                <h4 class="mb-4"><i class="fas fa-credit-card me-2"></i>Payment Information</h4>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You will be redirected to Stripe's secure payment page to complete your purchase.
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Payment Method</h5>
                        
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                            <i class="fab fa-cc-stripe fa-2x me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1">Stripe Checkout</h6>
                                <p class="text-muted mb-0">Secure payment processing</p>
                            </div>
                        </div>
                        
                        <div class="payment-methods mb-4">
                            <p class="mb-2">Accepted payment methods:</p>
                            <div class="d-flex gap-2">
                                <i class="fab fa-cc-visa fa-2x text-dark"></i>
                                <i class="fab fa-cc-mastercard fa-2x text-dark"></i>
                                <i class="fab fa-cc-amex fa-2x text-dark"></i>
                                <i class="fab fa-cc-discover fa-2x text-dark"></i>
                            </div>
                        </div>
                        
                        <button id="checkout-button" class="btn btn-primary w-100 btn-lg py-3">
                            <i class="fas fa-lock me-2"></i> Pay Now - Rs <?php echo number_format($grand_total, 2); ?>
                        </button>
                        
                        <p class="text-center text-muted mt-3 small">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your payment is secured with SSL encryption
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (empty($error) && isset($checkout_session)): ?>
<script>
    // IMPORTANT: Replace with your actual Stripe publishable key
    var stripe = Stripe('<?php echo $stripePublicKey; ?>');
    
    document.getElementById('checkout-button').addEventListener('click', function() {
        // Show loading state
        var originalText = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        this.disabled = true;
        
        stripe.redirectToCheckout({ 
            sessionId: '<?php echo $checkout_session->id; ?>' 
        }).then(function(result) {
            if (result.error) {
                // Show error message to user
                alert('Payment Error: ' + result.error.message);
                
                // Reset button
                document.getElementById('checkout-button').innerHTML = originalText;
                document.getElementById('checkout-button').disabled = false;
            }
        });
    });
</script>
<?php endif; ?>
</body>
</html>
