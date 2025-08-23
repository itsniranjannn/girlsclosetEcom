<?php
include 'db_connection.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total = 0;

// Fetch cart items
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.image_url, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-5">
  <h2>Shopping Cart</h2>

  <?php if (count($cart_items) > 0): ?>
    <?php foreach ($cart_items as $item): ?>
      <div class="d-flex align-items-center border-bottom py-3">
        <img src="<?= $item['image_url'] ?: 'https://via.placeholder.com/100'; ?>" width="80" height="80" class="me-3">
        <div class="flex-grow-1">
          <h5><?= $item['name']; ?></h5>
          <p>Rs <?= number_format($item['price'], 2); ?></p>
          <div class="input-group" style="width:120px;">
            <a href="cart-action.php?action=decrease&id=<?= $item['id']; ?>" class="btn btn-outline-secondary">-</a>
            <input type="text" class="form-control text-center" value="<?= $item['quantity']; ?>" readonly>
            <a href="cart-action.php?action=increase&id=<?= $item['id']; ?>" class="btn btn-outline-secondary">+</a>
          </div>
          <a href="cart-action.php?action=remove&id=<?= $item['id']; ?>" class="text-danger d-block mt-2">Remove</a>
        </div>
        <div><strong>Subtotal: Rs <?= number_format($item['subtotal'], 2); ?></strong></div>
      </div>
    <?php endforeach; ?>

    <div class="text-end mt-4">
      <h4>Total: Rs <?= number_format($total, 2); ?></h4>
      <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
      <a href="cart-action.php?action=clear" class="btn btn-danger">Clear Cart</a>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <h3>Your cart is empty</h3>
      <a href="products.php" class="btn btn-primary">Start Shopping</a>
    </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
