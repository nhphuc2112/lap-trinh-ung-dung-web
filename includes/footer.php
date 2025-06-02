    </main>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3"><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted mb-0">
                        Hệ thống quản lý khách sạn chuyên nghiệp, giúp bạn dễ dàng quản lý phòng, đặt phòng và theo dõi doanh thu.
                    </p>
                </div>

                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Liên kết nhanh</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>" class="text-decoration-none text-muted">
                                <i class="bi bi-house-door me-2"></i>Trang chủ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/rooms.php" class="text-decoration-none text-muted">
                                <i class="bi bi-door-open me-2"></i>Phòng
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/my-bookings.php" class="text-decoration-none text-muted">
                                    <i class="bi bi-calendar-check me-2"></i>Phòng của tôi
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo SITE_URL; ?>/profile.php" class="text-decoration-none text-muted">
                                    <i class="bi bi-person me-2"></i>Thông tin cá nhân
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/login.php" class="text-decoration-none text-muted">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo SITE_URL; ?>/register.php" class="text-decoration-none text-muted">
                                    <i class="bi bi-person-plus me-2"></i>Đăng ký
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-md-4">
                    <h5 class="mb-3">Liên hệ</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            <span class="text-muted">10 Huỳnh Văn Nghệ, Bửu Long, Biên Hòa, Đồng Nai, Việt Nam</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:+84328093454" class="text-decoration-none text-muted">+84 123 456 789</a>
                        </li>
                        <li>
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:nguyenhuuphuc@duck.com" class="text-decoration-none text-muted">info@hotel.com</a>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                    </p>
                </div>

                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <a href="#" class="text-decoration-none text-muted me-3">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="text-decoration-none text-muted me-3">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="text-decoration-none text-muted me-3">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="text-decoration-none text-muted">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        document.addEventListener('DOMContentLoaded', function() {
            var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    </body>

    </html>