<?php
// contact.php
include 'db_connection.php';
session_start();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Save to database if user is logged in
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = "Your message has been sent successfully! We'll get back to you soon.";
            
            // Clear form fields
            $name = $email = $subject = $message = '';
        } else {
            $error_message = "Sorry, there was an error sending your message. Please try again later.";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
            --secondary-color: #ffc2dd;
        }
        .contact-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
        }
        .contact-info-card {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            height: 100%;
        }
        .contact-form {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .contact-icon {
            width: 50px;
            height: 50px;
            background-color: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .contact-icon i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        .map-container {
            border-radius: 10px;
            overflow: hidden;
            height: 300px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'navbar.php'; ?>

    <!-- Page Content -->
    <div class="container mt-5 mb-5">
        <h2 class="mb-4">Contact Us</h2>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                            <h5 class="mt-2"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?></h5>
                        </div>
                        
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">Profile Information</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="orders.php">Order History</a>
                            </li>
<li class="nav-item">
                                <a class="nav-link" href="about.php">About GirlsCloset</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="contact.php">Contact Us</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Contact Content -->
            <div class="col-md-9">
                <div class="contact-container">
                    <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-5">
                        <div class="col-md-4">
                            <div class="contact-info-card">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h5>Our Location</h5>
                                <p>Kapan, Faika Chok<br>KATHMANDU, NEPAL</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-info-card">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <h5>Call Us</h5>
                                <p>+977 123-4567-343</p>
                                <p>+977 987-6543-888</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-info-card">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h5>Email Us</h5>
                                <p>support@girlsclothing.com</p>
                                <p>info@girlsclothing.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-7">
                            <div class="contact-form">
                                <h4 class="mb-4">Send Us a Message</h4>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Send Message</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h4 class="mb-4">Store Location</h4>
                            <div class="map-container">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d742.4080926023947!2d85.36160233138077!3d27.732474946636547!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb1b1ff0cd1baf%3A0x70b1345604d7465d!2sFaika%20Chowk%2C%20Kapan!5e0!3m2!1sen!2snp!4v1755784476401!5m2!1sen!2snp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Business Hours</h5>
                                <table class="table">
                                    <tr>
                                        <td>Monday - Friday</td>
                                        <td>9:00 AM - 8:00 PM</td>
                                    </tr>
                                    <tr>
                                        <td>Saturday</td>
                                        <td>10:00 AM - 6:00 PM</td>
                                    </tr>
                                    <tr>
                                        <td>Sunday</td>
                                        <td>11:00 AM - 5:00 PM</td>
                                    </tr>
                                </table>
                            </div>
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