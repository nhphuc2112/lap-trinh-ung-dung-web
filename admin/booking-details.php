<?php
require_once '../includes/init.php';
requireAdmin();
require_once '../classes/Booking.php';
require_once '../classes/Room.php';
require_once '../classes/User.php';

$booking = new Booking();
$room = new Room();
$user = new User();

// get booking ID
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$bookingInfo = $booking->getBookingById($bookingId);
if (!$bookingInfo) {
    setFlashMessage('danger', 'Không tìm thấy thông tin đặt phòng!');
    redirect('bookings.php');
}
$userInfo = $user->getUserById($bookingInfo['user_id']);
$roomInfo = $room->getRoomById($bookingInfo['room_id']);

// update booking status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    if ($booking->updateStatus($bookingId, $newStatus)) {
        echo 'Cập nhật trạng thái thành công!';
        redirect('bookings.php');
    } else {
        echo 'Cập nhật trạng thái thất bại!';
        redirect('booking-details.php?id=' . $bookingId);
    }
}
require_once '../includes/header.php';

?>
<div class="container py-4">
    <h1 class="h3 mb-4">Chi tiết đặt phòng #<?= $bookingInfo['id'] ?></h1>
    <?php if ($msg = getFlashMessage()): ?>
        <div class="alert alert-<?= $msg['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Thông tin khách hàng</h5>
                    <ul class="list-unstyled mb-0">
                        <li><b>Họ tên:</b> <?= htmlspecialchars($userInfo['name']) ?></li>
                        <li><b>Email:</b> <?= htmlspecialchars($userInfo['email']) ?></li>
                        <li><b>SĐT:</b> <?= htmlspecialchars($userInfo['phone'] ?? 'N/A') ?></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Thông tin phòng</h5>
                    <ul class="list-unstyled mb-0">
                        <li><b>Số phòng:</b> <?= htmlspecialchars($roomInfo['room_number']) ?></li>
                        <li><b>Loại phòng:</b> <?= htmlspecialchars($roomInfo['type']) ?></li>
                        <li><b>Giá/đêm:</b> <?= number_format($roomInfo['price_per_night'], 0, ',', '.') ?> VNĐ</li>
                    </ul>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Thông tin đặt phòng</h5>
                    <ul class="list-unstyled mb-0">
                        <li><b>Check-in:</b> <?= date('d/m/Y', strtotime($bookingInfo['check_in'])) ?></li>
                        <li><b>Check-out:</b> <?= date('d/m/Y', strtotime($bookingInfo['check_out'])) ?></li>
                        <li><b>Số đêm:</b> <?= ceil((strtotime($bookingInfo['check_out']) - strtotime($bookingInfo['check_in'])) / (60 * 60 * 24)) ?></li>
                        <li><b>Tổng tiền:</b> <?= number_format($bookingInfo['total_price'], 0, ',', '.') ?> VNĐ</li>
                        <li><b>Ghi chú:</b> <?= htmlspecialchars($bookingInfo['notes']) ?></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Trạng thái đặt phòng</h5>
                    <form method="POST" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <select name="status" class="form-select">
                                <option value="pending" <?= $bookingInfo['status'] === 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?= $bookingInfo['status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                <option value="checked_in" <?= $bookingInfo['status'] === 'checked_in' ? 'selected' : '' ?>>Đã nhận phòng</option>
                                <option value="checked_out" <?= $bookingInfo['status'] === 'checked_out' ? 'selected' : '' ?>>Đã trả phòng</option>
                                <option value="cancelled" <?= $bookingInfo['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
            <a href="bookings.php" class="btn btn-secondary">Quay lại danh sách</a>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?> 