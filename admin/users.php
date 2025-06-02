<?php
require_once '../includes/init.php';
requireAdmin();

require_once '../classes/User.php';

$user = new User();

// a/e/d user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'phone' => $_POST['phone'] ?? '',
                'is_admin' => $_POST['is_admin'] ?? 'user'
            ];
            
            if ($user->createUser($data)) {
                setFlashMessage('success', 'Thêm người dùng thành công!');
            } else {
                setFlashMessage('danger', 'Thêm người dùng thất bại! Email có thể đã tồn tại.');
            }
            break;
            
        case 'edit':
            $userId = $_POST['user_id'];
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? '',
                'is_admin' => $_POST['is_admin'] ?? 'user'
            ];
            
            // cap nhat neu ko trung` password
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            
            if ($user->updateUser($userId, $data)) {
                setFlashMessage('success', 'Cập nhật người dùng thành công!');
            } else {
                setFlashMessage('danger', 'Cập nhật người dùng thất bại!');
            }
            break;
            
        case 'delete':
            $userId = $_POST['user_id'];
            // can't delete admin account
            if ($userId == $_SESSION['user_id']) {
                setFlashMessage('danger', 'Không thể xóa tài khoản của chính mình!');
            } else {
                if ($user->deleteUser($userId)) {
                    setFlashMessage('success', 'Xóa người dùng thành công!');
                } else {
                    setFlashMessage('danger', 'Xóa người dùng thất bại!');
                }
            }
            break;
    }
      redirect('/admin/users.php');
}
require_once '../includes/header.php';

// get user list
$users = $user->getAllUsers();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Quản lý người dùng</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i> Thêm người dùng
        </button>
    </div>

    <!-- fil and search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="tableFilter" placeholder="Tìm kiếm theo tên hoặc email...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="is_adminFilter">
                        <option value="">Tất cả vai trò</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- user list -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>Họ tên</th>
                            <th data-sort>Email</th>
                            <th data-sort>Số điện thoại</th>
                            <th data-sort>Vai trò</th>
                            <th data-sort>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $userData): ?>
                            <tr>
                                <td><?= $userData['id'] ?></td>
                                <td><?= htmlspecialchars($userData['name']) ?></td>
                                <td><?= htmlspecialchars($userData['email']) ?></td>
                                <td><?= htmlspecialchars($userData['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $userData['is_admin'] === 'admin' ? 'danger' : 'primary' ?>">
                                        <?= ucfirst($userData['is_admin']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($userData['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal"
                                                data-user='<?= htmlspecialchars(json_encode($userData)) ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($userData['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= $userData['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- add user modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">Vui lòng nhập mật khẩu</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="is_admin" class="form-label">Vai trò</label>
                        <select class="form-select" id="is_admin" name="is_admin" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- edit user modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa thông tin người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                        <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_is_admin" class="form-label">Vai trò</label>
                        <select class="form-select" id="edit_is_admin" name="is_admin" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // edit user
    const editButtons = document.querySelectorAll('[data-bs-target="#editUserModal"]');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_is_admin').value = user.is_admin;
            document.getElementById('edit_password').value = '';
        });
    });

    // filter
    const tableFilter = document.getElementById('tableFilter');
    const is_adminFilter = document.getElementById('is_adminFilter');
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    function filterTable() {
        const searchValue = tableFilter.value.toLowerCase();
        const is_adminValue = is_adminFilter.value;

        for (let row of rows) {
            const cells = row.getElementsByTagName('td');
            const name = cells[1].textContent.toLowerCase();
            const email = cells[2].textContent.toLowerCase();
            const is_admin = cells[4].querySelector('.badge').textContent.toLowerCase();

            const matchesSearch = name.includes(searchValue) || email.includes(searchValue);
            const matchesis_admin = !is_adminValue || is_admin === is_adminValue.toLowerCase();

            row.style.display = matchesSearch && matchesis_admin ? '' : 'none';
        }
    }

    tableFilter.addEventListener('keyup', filterTable);
    is_adminFilter.addEventListener('change', filterTable);

    // validate form
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 