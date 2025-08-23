<?php
// payment-failed.php
session_start();
$error = $_GET['error'] ?? 'Payment was cancelled or failed.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
        }
        .error-container {
            text-align: center;
            padding: 50px 20px;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 20px;
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

    <!-- Error Message -->
    <div class="container">
        <div class="error-container">
            <i class="fas fa-times-circle error-icon"></i>
            <h2>Payment Failed</h2>
            <p class="lead"><?php echo htmlspecialchars($error); ?></p>
            <p>Please try again or contact support if the problem persists.</p>
            
            <div class="mt-5">
                <a href="cart.php" class="btn btn-primary">Return to Cart</a>
                <a href="contact.php" class="btn btn-outline-primary">Contact Support</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>