</div>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h5>About Us</h5>
                    <p>A modern blogging platform where you can share your thoughts, ideas, and stories with the world.</p>
                    <div class="social-links mt-3">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="/blog-main/index.php">Home</a></li>
                        <li><a href="#">Popular Posts</a></li>
                        <li><a href="#">Categories</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/blog-main/profile.php">My Profile</a></li>
                            <li><a href="/blog-main/create-post.php">Create Post</a></li>
                        <?php else: ?>
                            <li><a href="/blog-main/login.php">Login</a></li>
                            <li><a href="/blog-main/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-envelope"></i> contact@blogwebsite.com</p>
                    <p><i class="fas fa-phone"></i> +1 (123) 456-7890</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Blog Street, Web City</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> <span class="font-weight-bold">Blog Website</span> | All rights reserved | <i class="fas fa-code"></i> with <i class="fas fa-heart text-danger"></i></p>
            </div>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/blog-main/assets/js/script.js"></script>
</body>
</html>