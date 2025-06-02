<?php
require_once 'includes/header.php';
require_once 'classes/User.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// register check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // data check
    if (empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        $user = new User();
        $result = $user->createUser([
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'user'
        ]);
        
        if ($result) {
            $success = 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.';
        } else {
            $error = 'Email đã tồn tại hoặc có lỗi xảy ra';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4 text-center">Đăng ký tài khoản</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-primary btn-sm">Đăng nhập ngay</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ và tên</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Vui lòng nhập họ và tên</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Vui lòng nhập số điện thoại</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="6" required>
                                <div class="invalid-feedback">Mật khẩu phải có ít nhất 6 ký tự</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" minlength="6" required>
                                <div class="invalid-feedback">Vui lòng xác nhận mật khẩu</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // pass check
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Mật khẩu không khớp');
                event.preventDefault();
                event.stopPropagation();
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // pass input check
        const password = form.querySelector('#password');
        const confirmPassword = form.querySelector('#confirm_password');
        
        confirmPassword.addEventListener('input', () => {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Mật khẩu không khớp');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?> 