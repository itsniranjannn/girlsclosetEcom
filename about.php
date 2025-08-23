<?php
// about.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Girls Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff69b4;
        }
        .about-hero {
            background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url('https://images.unsplash.com/photo-1534452203293-494d7ddbf7e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            text-align: center;
        }
        .team-member {
            text-align: center;
            margin-bottom: 30px;
        }
        .team-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 5px solid #f8f9fa;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .value-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        .value-card:hover {
            transform: translateY(-5px);
        }
        .value-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
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

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">About GirlsCloset</h1>
            <p class="lead">We're passionate about providing beautiful, high-quality clothing for girls of all ages.</p>
        </div>
    </section>

    <!-- Page Content -->
    <div class="container mt-5">
        <!-- Our Story -->
        <div class="row mb-5">
            <div class="col-lg-6">
                <h2 class="mb-4">Our Story</h2>
                <p>Founded in 2015, GirlsCloset began as a small boutique with a simple mission: to provide parents with access to high-quality, stylish, and affordable clothing for their daughters.</p>
                <p>What started as a single store has now grown into a beloved online destination for families across the country. We carefully select each item in our collection, focusing on quality materials, thoughtful design, and timeless style.</p>
                <p>Our team is made up of parents, designers, and fashion enthusiasts who understand what matters most when it comes to children's clothing: comfort, durability, and of course, plenty of style!</p>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1566206091558-7f218b696731?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded" alt="Our Story">
            </div>
        </div>

        <!-- Our Values -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-5">
                <h2>Our Values</h2>
                <p class="lead">These principles guide everything we do</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Quality</h4>
                    <p>We believe in offering only the highest quality clothing that can withstand both playtime and special occasions.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Sustainability</h4>
                    <p>We're committed to ethical manufacturing processes and sustainable materials whenever possible.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h4>Customer Happiness</h4>
                    <p>Your satisfaction is our top priority. We're here to make shopping for your little ones a joyful experience.</p>
                </div>
            </div>
        </div>

        <!-- Our Team -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-5">
                <h2>Meet Our Team</h2>
                <p class="lead">The passionate people behind GirlsCloset</p>
            </div>
              <div class="col-md-3 col-sm-6">
                <div class="team-member">
                    <img src="n.jpg" class="team-img" alt="Michael Chen">
                    <h5>Niranjan Katwal</h5>
                    <p class="text-muted">Founder & CEO</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="team-img" alt="Sarah Johnson">
                    <h5>Sarah Johnson</h5>
                    <p class="text-muted">Marketing Director</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="team-img" alt="Emily Davis">
                    <h5>Emily Davis</h5>
                    <p class="text-muted">Head Designer</p>
                </div>
            </div>
            
          
            
            <div class="col-md-3 col-sm-6">
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="team-img" alt="Jessica Martinez">
                    <h5>Jessica Martinez</h5>
                    <p class="text-muted">Customer Support</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <div class="bg-light p-5 rounded">
                    <h3>Join Our Community</h3>
                    <p class="lead mb-4">Subscribe to our newsletter for exclusive offers, styling tips, and new product announcements.</p>
                    <form class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your email address">
                                <button class="btn btn-primary" type="submit">Subscribe</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>