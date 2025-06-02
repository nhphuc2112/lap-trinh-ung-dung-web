<?php
require_once '../includes/init.php';
requireAdmin();

require_once '../classes/Room.php';

$room = new Room();
$message = '';

// add new rom
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $data = [
            'room_number' => $_POST['room_number'],
            'type' => $_POST['type'],
            'price_per_night' => $_POST['price_per_night'],
            'capacity' => $_POST['capacity'],
            'description' => $_POST['description'],
            'status' => $_POST['status']
        ];
        
        if ($room->createRoom($data, $_FILES['image'] ?? null)) {
            setFlashMessage('success', 'Thêm phòng thành công!');
        } else {
            setFlashMessage('danger', 'Thêm phòng thất bại! Số phòng có thể đã tồn tại.');
        }
    }
    // update room
    elseif ($_POST['action'] === 'edit' && isset($_POST['room_id'])) {
        $data = [
            'type' => $_POST['type'],
            'price_per_night' => $_POST['price_per_night'],
            'capacity' => $_POST['capacity'],
            'description' => $_POST['description'],
            'status' => $_POST['status']
        ];
        
        if ($room->updateRoom($_POST['room_id'], $data, $_FILES['image'] ?? null)) {
            setFlashMessage('success', 'Cập nhật phòng thành công!');
        } else {
            setFlashMessage('danger', 'Cập nhật phòng thất bại!');
        }
    }
    // delete room
    elseif ($_POST['action'] === 'delete' && isset($_POST['room_id'])) {
        if ($room->deleteRoom($_POST['room_id'])) {
            setFlashMessage('success', 'Xóa phòng thành công!');
        } else {
            setFlashMessage('danger', 'Không thể xóa phòng đang được đặt!');
        }
    }
    redirect('/admin/rooms.php');
}

// Include header after all redirects are handled
require_once '../includes/header.php';

// Lấy danh sách phòng
$rooms = $room->getAllRooms();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Quản lý phòng</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus"></i> Thêm phòng mới
        </button>
    </div>

    <!-- Filter và Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="tableFilter" placeholder="Tìm kiếm...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="available">Có sẵn</option>
                        <option value="occupied">Đã đặt</option>
                        <option value="maintenance">Bảo trì</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">Tất cả loại phòng</option>
                        <?php foreach ($room->getRoomTypes() as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách phòng -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>Số phòng</th>
                            <th data-sort>Loại phòng</th>
                            <th data-sort>Giá/đêm</th>
                            <th data-sort>Sức chứa</th>
                            <th data-sort>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?= $room['id'] ?></td>
                                <td><?= htmlspecialchars($room['room_number']) ?></td>
                                <td><?= htmlspecialchars($room['type']) ?></td>
                                <td><?= number_format($room['price_per_night'], 0, ',', '.') ?> VNĐ</td>
                                <td><?= $room['capacity'] ?> người</td>
                                <td>
                                    <span class="status-badge status-<?= $room['status'] ?>">
                                        <?= ucfirst($room['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-room" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editRoomModal"
                                            data-room='<?= htmlspecialchars(json_encode($room)) ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm phòng -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm phòng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="room_number" class="form-label">Số phòng</label>
                        <input type="text" class="form-control" id="room_number" name="room_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Loại phòng</label>
                        <input type="text" class="form-control" id="type" name="type" required>
                    </div>
                    <div class="mb-3">
                        <label for="price_per_night" class="form-label">Giá/đêm (VNĐ)</label>
                        <input type="number" class="form-control" id="price_per_night" name="price_per_night" required>
                    </div>
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Sức chứa</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Ảnh phòng</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Chấp nhận file JPG, JPEG, PNG. Tối đa 5MB.</div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available">Có sẵn</option>
                            <option value="maintenance">Bảo trì</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa phòng -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="room_id" id="edit_room_id">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa thông tin phòng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Loại phòng</label>
                        <input type="text" class="form-control" id="edit_type" name="type" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price_per_night" class="form-label">Giá/đêm (VNĐ)</label>
                        <input type="number" class="form-control" id="edit_price_per_night" name="price_per_night" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_capacity" class="form-label">Sức chứa</label>
                        <input type="number" class="form-control" id="edit_capacity" name="capacity" required min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Ảnh phòng</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        <div class="form-text">Chấp nhận file JPG, JPEG, PNG. Tối đa 5MB.</div>
                        <div id="current_image" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="available">Có sẵn</option>
                            <option value="occupied">Đã đặt</option>
                            <option value="maintenance">Bảo trì</option>
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
    // Xử lý sửa phòng
    const editButtons = document.querySelectorAll('.edit-room');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const room = JSON.parse(this.dataset.room);
            document.getElementById('edit_room_id').value = room.id;
            document.getElementById('edit_type').value = room.type;
            document.getElementById('edit_price_per_night').value = room.price_per_night;
            document.getElementById('edit_capacity').value = room.capacity;
            document.getElementById('edit_description').value = room.description || '';
            document.getElementById('edit_status').value = room.status;
            
            // Hiển thị ảnh hiện tại nếu có
            const currentImageDiv = document.getElementById('current_image');
            if (room.image) {
                currentImageDiv.innerHTML = `
                    <img src="/${room.image}" alt="Current room image" class="img-thumbnail" style="max-height: 200px;">
                    <p class="text-muted mt-2">Ảnh hiện tại</p>
                `;
            } else {
                currentImageDiv.innerHTML = '<p class="text-muted">Chưa có ảnh</p>';
            }
        });
    });

    // Xử lý filter
    const tableFilter = document.getElementById('tableFilter');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    function filterTable() {
        const searchValue = tableFilter.value.toLowerCase();
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;

        for (let row of rows) {
            const cells = row.getElementsByTagName('td');
            const roomNumber = cells[1].textContent.toLowerCase();
            const type = cells[2].textContent.toLowerCase();
            const status = cells[5].querySelector('.status-badge').classList[1].replace('status-', '');

            const matchesSearch = roomNumber.includes(searchValue);
            const matchesStatus = !statusValue || status === statusValue;
            const matchesType = !typeValue || type === typeValue.toLowerCase();

            row.style.display = matchesSearch && matchesStatus && matchesType ? '' : 'none';
        }
    }

    tableFilter.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);
});
</script>

<?php require_once '../includes/footer.php'; ?> 