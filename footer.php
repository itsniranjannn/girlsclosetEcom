<?php
// footer.php
?>

<style>
/* Navbar styling */
.navbar .nav-link {
    color: #333 !important;
    font-weight: 500;
    transition: color 0.3s ease, transform 0.2s ease;
}
.navbar .nav-link:hover {
    color: #ff69b4 !important;
    transform: translateY(-2px);
}
.navbar .nav-link.active {
    color: #ff1493 !important;
    font-weight: 600;
}

/* Footer links */
.footer a {
    color: #555;
    text-decoration: none;
    transition: color 0.3s ease, padding-left 0.3s ease;
}
.footer a:hover {
    color: #ff69b4;
    padding-left: 5px;
}

/* Social icons */
.footer .social-icons a {
    color: #555;
    margin-right: 15px;
    font-size: 1.3rem;
    transition: color 0.3s ease, transform 0.2s ease;
}
.footer .social-icons a:hover {
    color: #ff69b4;
    transform: scale(1.2);
}
</style>

<footer class="footer mt-5" style="background-color: #f8f9fa; padding: 40px 0; margin-top: 60px;">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>GirlsCloset</h5>
                <p>We offer the latest trends in girls' clothing with a focus on quality, style, and affordability.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h5>Shop</h5>
                <ul class="list-unstyled">
                    <li><a href="products.php">New Arrivals</a></li>
                    <li><a href="products.php?category=Dresses">Dresses</a></li>
                    <li><a href="products.php?category=Tops">Tops</a></li>
                    <li><a href="products.php?category=Bottoms">Bottoms</a></li>
                    <li><a href="products.php?category=Accessories">Accessories</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4">
                <h5>Company</h5>
                <ul class="list-unstyled">
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="returns.php">Returns & Exchanges</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact Us</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> 123 Fashion Street, City, Country</p>
                <p><i class="fas fa-phone me-2"></i> +1 234 567 8900</p>
                <p><i class="fas fa-envelope me-2"></i> info@girlscloset.com</p>
                
                <h5 class="mt-4">Newsletter</h5>
                <form class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your email address">
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; 2023 GirlsCloset. All rights reserved.</p>
        </div>
    </div>
</footer>
