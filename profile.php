<?php
require_once 'includes/header.php';
require_once 'classes/User.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

$user = new User();
$currentUser = $_SESSION['user'];
$error = '';
$success = '';

// update 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        if (empty($fullName) || empty($email) || empty($phone)) {
            $error = 'Vui lòng nhập đầy đủ thông tin';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ';
        } else {
            $result = $user->updateUser($currentUser['id'], [
                'name' => $fullName,
                'email' => $email,
                'phone' => $phone
            ]);
            
            if ($result) {
                $currentUser['name'] = $fullName;
                $currentUser['email'] = $email;
                $currentUser['phone'] = $phone;
                $_SESSION['user'] = $currentUser;
                
                $success = 'Cập nhật thông tin thành công';
            } else {
                $error = 'Email đã tồn tại hoặc có lỗi xảy ra';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Vui lòng nhập đầy đủ thông tin';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp';
        } else {
            $result = $user->changePassword($currentUser['id'], $currentPassword, $newPassword);
            
            if ($result) {
                $success = 'Đổi mật khẩu thành công';
            } else {
                $error = 'Mật khẩu hiện tại không đúng';
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mb-3">
                            <span class="initials">
                                <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                            </span>
                        </div>
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($currentUser['name']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-person me-2"></i>Thông tin cá nhân
                        </a>
                        <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-key me-2"></i>Đổi mật khẩu
                        </a>
                        <a href="my-bookings.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-check me-2"></i>Lịch sử đặt phòng
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Quản trị
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="tab-content">
                <!-- Profile information -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin cá nhân</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                                    <div class="invalid-feedback">Vui lòng nhập họ và tên</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                                    <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($currentUser['phone']); ?>" required>
                                    <div class="invalid-feedback">Vui lòng nhập số điện thoại</div>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    Cập nhật thông tin
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change password -->
                <div class="tab-pane fade" id="password">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Đổi mật khẩu</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password" 
                                           name="current_password" required>
                                    <div class="invalid-feedback">Vui lòng nhập mật khẩu hiện tại</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" minlength="6" required>
                                    <div class="invalid-feedback">Mật khẩu mới phải có ít nhất 6 ký tự</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" minlength="6" required>
                                    <div class="invalid-feedback">Vui lòng xác nhận mật khẩu mới</div>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    background-color: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.initials {
    font-size: 32px;
    font-weight: bold;
    color: #6c757d;
}
</style>

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
            if (form.querySelector('#new_password')) {
                const newPassword = form.querySelector('#new_password');
                const confirmPassword = form.querySelector('#confirm_password');
                
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Mật khẩu không khớp');
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // input pass check
        const newPassword = form.querySelector('#new_password');
        const confirmPassword = form.querySelector('#confirm_password');
        
        if (newPassword && confirmPassword) {
            confirmPassword.addEventListener('input', () => {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Mật khẩu không khớp');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?> 