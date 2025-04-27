    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3 fw-bold">ListItAll</h5>
                    <p class="mb-3">The modern way to buy, sell, and connect in your local community. Post your listings for free and find everything you need.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h6 class="mb-3 fw-bold">Categories</h6>
                    <ul class="list-unstyled">
                        <?php
                        $footerCategories = array_slice(getCategories(), 0, 5);
                        foreach ($footerCategories as $category) {
                            echo '<li class="mb-2"><a href="/index.php?category=' . $category['id'] . '" class="text-white text-decoration-none hover-opacity">' . $category['name'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h6 class="mb-3 fw-bold">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/index.php" class="text-white text-decoration-none hover-opacity">Home</a></li>
                        <li class="mb-2"><a href="/posts/create.php" class="text-white text-decoration-none hover-opacity">Post Ad</a></li>
                        <li class="mb-2"><a href="/about.php" class="text-white text-decoration-none hover-opacity">About Us</a></li>
                        <li class="mb-2"><a href="/contact.php" class="text-white text-decoration-none hover-opacity">Contact</a></li>
                        <li class="mb-2"><a href="/faq.php" class="text-white text-decoration-none hover-opacity">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="mb-3 fw-bold">Contact Us</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Market Street, Suite 456, San Francisco, CA 94103</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> (123) 456-7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@listitall.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> ListItAll. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="/terms.php" class="text-white text-decoration-none hover-opacity">Terms of Service</a></li>
                        <li class="list-inline-item ms-3"><a href="/privacy.php" class="text-white text-decoration-none hover-opacity">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
