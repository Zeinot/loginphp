    </main>

    <!-- Modern Footer 2025 Design -->
    <footer class="modern-footer">
        <!-- Decorative wave SVG separator -->
        <div class="footer-wave-separator">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="currentColor" fill-opacity="1" d="M0,288L48,272C96,256,192,224,288,213.3C384,203,480,213,576,229.3C672,245,768,267,864,261.3C960,256,1056,224,1152,208C1248,192,1344,192,1392,192L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
        
        <!-- Upper footer with gradient background -->
        <div class="footer-upper">
            <div class="container">
                <div class="footer-grid">
                    <!-- Brand section with animated logo -->
                    <div class="footer-brand">
                        <div class="footer-logo-container">
                            <div class="footer-logo">ListItAll</div>
                            <div class="footer-tagline">Connect • Buy • Sell</div>
                        </div>
                        <p class="footer-description">The modern marketplace for your local community. Join millions already using ListItAll to connect, buy, and sell.</p>
                        
                        <!-- App badges -->
                        <div class="app-badges">
                            <a href="#" class="app-badge">
                                <i class="fab fa-apple"></i>
                                <div class="badge-text">
                                    <span>Download on the</span>
                                    <strong>App Store</strong>
                                </div>
                            </a>
                            <a href="#" class="app-badge">
                                <i class="fab fa-google-play"></i>
                                <div class="badge-text">
                                    <span>GET IT ON</span>
                                    <strong>Google Play</strong>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Categories with visual indicators -->
                    <div class="footer-categories">
                        <h3 class="footer-heading">Categories</h3>
                        <ul class="modern-footer-list">
                            <?php
                            $footerCategories = array_slice(getCategories(), 0, 5);
                            $categoryIcons = [
                                'for-sale' => 'tag',
                                'housing' => 'home',
                                'jobs' => 'briefcase',
                                'services' => 'tools',
                                'community' => 'users',
                                'vehicles' => 'car',
                                'electronics' => 'laptop',
                                'furniture' => 'couch'
                            ];
                            
                            foreach ($footerCategories as $category) {
                                $icon = 'tag'; // Default icon
                                foreach($categoryIcons as $slug => $iconName) {
                                    if (stripos($category['name'], $slug) !== false || 
                                        stripos($category['slug'], $slug) !== false) {
                                        $icon = $iconName;
                                        break;
                                    }
                                }
                                echo '<li><a href="/index.php?category=' . $category['id'] . '" class="footer-link"><i class="fas fa-' . $icon . '"></i><span>' . $category['name'] . '</span></a></li>';
                            }
                            ?>
                            <li><a href="/categories.php" class="footer-link footer-more-link"><i class="fas fa-th-large"></i><span>All Categories</span></a></li>
                        </ul>
                    </div>
                    
                    <!-- Quick links with hover effects -->
                    <div class="footer-links">
                        <h3 class="footer-heading">Quick Links</h3>
                        <div class="footer-links-grid">
                            <ul class="modern-footer-list">
                                <li><a href="/index.php" class="footer-link"><i class="fas fa-home"></i><span>Home</span></a></li>
                                <li><a href="/posts/create.php" class="footer-link"><i class="fas fa-plus-circle"></i><span>Post Ad</span></a></li>
                                <li><a href="/about.php" class="footer-link"><i class="fas fa-info-circle"></i><span>About Us</span></a></li>
                            </ul>
                            <ul class="modern-footer-list">
                                <li><a href="/contact.php" class="footer-link"><i class="fas fa-envelope"></i><span>Contact</span></a></li>
                                <li><a href="/faq.php" class="footer-link"><i class="fas fa-question-circle"></i><span>FAQ</span></a></li>
                                <li><a href="/safety-tips.php" class="footer-link"><i class="fas fa-shield-alt"></i><span>Safety</span></a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Contact and newsletter with interactive elements -->
                    <div class="footer-contact">
                        <h3 class="footer-heading">Stay Connected</h3>
                        
                        <!-- Newsletter subscription -->
                        <div class="newsletter-container">
                            <p class="newsletter-text">Subscribe for updates and promotions</p>
                            <form class="newsletter-form">
                                <div class="newsletter-input-group">
                                    <input type="email" placeholder="Your email address" class="newsletter-input" required>
                                    <button type="submit" class="newsletter-button">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Contact info with animations -->
                        <div class="contact-info">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-text">123 Market Street, Suite 456<br>San Francisco, CA 94103</div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="contact-text">(123) 456-7890</div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-text">info@listitall.com</div>
                            </div>
                        </div>
                        
                        <!-- Social links with floating animation -->
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lower footer with copyright and legal links -->
        <div class="footer-lower">
            <div class="container">
                <div class="footer-lower-content">
                    <div class="copyright">
                        <p>&copy; <?php echo date('Y'); ?> ListItAll. All rights reserved.</p>
                    </div>
                    <div class="legal-links">
                        <a href="/terms.php">Terms of Service</a>
                        <a href="/privacy.php">Privacy Policy</a>
                        <a href="/cookies.php">Cookie Policy</a>
                        <a href="/sitemap.php">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for Ajax functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Font Awesome Kit for Icons (using CDN) -->
    <script src="https://kit.fontawesome.com/123456789a.js" crossorigin="anonymous"></script>
    <!-- Custom JavaScript -->
    <script src="/assets/js/main.js"></script>
    <!-- Modern scripts with animations and interactions -->
    <script src="/assets/js/modern-scripts.js"></script>
    
    <!-- Initialize modern features -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activate footer animations
            const footerElements = document.querySelectorAll('.footer-link, .social-link, .app-badge');
            footerElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.classList.add('pulse');
                });
                element.addEventListener('mouseleave', function() {
                    this.classList.remove('pulse');
                });
            });
        });
    </script>
</body>
</html>
