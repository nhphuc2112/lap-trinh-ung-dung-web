<?php
require_once 'includes/init.php';
require_once 'classes/Booking.php';
require_once 'classes/Room.php';

// login check
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Vui lòng đăng nhập để xem đặt phòng của bạn!');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$booking = new Booking();
$room = new Room();

// cancel booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $bookingId = $_POST['booking_id'] ?? 0;
    $bookingInfo = $booking->getBookingById($bookingId);
    
    if ($bookingInfo && $bookingInfo['user_id'] === $_SESSION['user_id'] && $bookingInfo['status'] === 'pending') {
        if ($booking->updateStatus($bookingId, 'cancelled')) {
            setFlashMessage('success', 'Hủy đặt phòng thành công!');
        } else {
            setFlashMessage('danger', 'Hủy đặt phòng thất bại!');
        }
    } else {
        setFlashMessage('danger', 'Không thể hủy đặt phòng này!');
    }
    
    redirect('my-bookings.php');

}

require_once 'includes/header.php';
$bookings = $booking->getUserBookings($_SESSION['user_id']);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phòng của tôi</h1>
        <a href="rooms.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Đặt phòng mới
        </a>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">
            Bạn chưa có đặt phòng nào. <a href="rooms.php">Đặt phòng ngay</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đặt phòng</th>
                                <th>Phòng</th>
                                <th>Ngày đặt</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td>
                                        Phòng <?= htmlspecialchars($booking['room_number']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($booking['type']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($booking['created_at'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($booking['check_in'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($booking['check_out'])) ?></td>
                                    <td><?= number_format($booking['total_price'], 0, ',', '.') ?> VNĐ</td>
                                    <td>
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?php
                                            switch ($booking['status']) {
                                                case 'pending':
                                                    echo 'Chờ xác nhận';
                                                    break;
                                                case 'confirmed':
                                                    echo 'Đã xác nhận';
                                                    break;
                                                case 'checked_in':
                                                    echo 'Đã nhận phòng';
                                                    break;
                                                case 'checked_out':
                                                    echo 'Đã trả phòng';
                                                    break;
                                                case 'cancelled':
                                                    echo 'Đã hủy';
                                                    break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewBookingModal"
                                                    data-booking='<?= htmlspecialchars(json_encode($booking)) ?>'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy đặt phòng này?');">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($booking['status'] === 'checked_out'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="printInvoice(<?= $booking['id'] ?>)">
                                                    <i class="fas fa-print"></i>
                                                </button>
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
    <?php endif; ?>
</div>

<!-- Chi tiet dat phong -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đặt phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Thông tin phòng</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Số phòng:</th>
                                <td id="view_room_number"></td>
                            </tr>
                            <tr>
                                <th>Loại phòng:</th>
                                <td id="view_room_type"></td>
                            </tr>
                            <tr>
                                <th>Giá/đêm:</th>
                                <td id="view_room_price"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Thông tin đặt phòng</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Mã đặt phòng:</th>
                                <td id="view_booking_id"></td>
                            </tr>
                            <tr>
                                <th>Ngày đặt:</th>
                                <td id="view_booking_date"></td>
                            </tr>
                            <tr>
                                <th>Check-in:</th>
                                <td id="view_check_in"></td>
                            </tr>
                            <tr>
                                <th>Check-out:</th>
                                <td id="view_check_out"></td>
                            </tr>
                            <tr>
                                <th>Số đêm:</th>
                                <td id="view_nights"></td>
                            </tr>
                            <tr>
                                <th>Tổng tiền:</th>
                                <td id="view_total_price"></td>
                            </tr>
                            <tr>
                                <th>Trạng thái:</th>
                                <td id="view_status"></td>
                            </tr>
                            <tr>
                                <th>Ghi chú:</th>
                                <td id="view_notes"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" id="printInvoiceBtn" style="display: none;">
                    <i class="fas fa-print"></i> In hóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // xem chi tiet dat phong
    const viewButtons = document.querySelectorAll('[data-bs-target="#viewBookingModal"]');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const booking = JSON.parse(this.dataset.booking);
            
            // nights check
            const checkIn = new Date(booking.check_in);
            const checkOut = new Date(booking.check_out);
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            
            // info update
            document.getElementById('view_booking_id').textContent = '#' + booking.id;
            document.getElementById('view_room_number').textContent = 'Phòng ' + booking.room_number;
            document.getElementById('view_room_type').textContent = booking.type;
            document.getElementById('view_room_price').textContent = new Intl.NumberFormat('vi-VN').format(booking.total_price / nights) + ' VNĐ';
            
            document.getElementById('view_booking_date').textContent = new Date(booking.created_at).toLocaleDateString('vi-VN');
            document.getElementById('view_check_in').textContent = new Date(booking.check_in).toLocaleDateString('vi-VN');
            document.getElementById('view_check_out').textContent = new Date(booking.check_out).toLocaleDateString('vi-VN');
            document.getElementById('view_nights').textContent = nights + ' đêm';
            document.getElementById('view_total_price').textContent = new Intl.NumberFormat('vi-VN').format(booking.total_price) + ' VNĐ';
            
            let statusText = '';
            switch (booking.status) {
                case 'pending':
                    statusText = 'Chờ xác nhận';
                    break;
                case 'confirmed':
                    statusText = 'Đã xác nhận';
                    break;
                case 'checked_in':
                    statusText = 'Đã nhận phòng';
                    break;
                case 'checked_out':
                    statusText = 'Đã trả phòng';
                    break;
                case 'cancelled':
                    statusText = 'Đã hủy';
                    break;
            }
            document.getElementById('view_status').innerHTML = `<span class="status-badge status-${booking.status}">${statusText}</span>`;
            document.getElementById('view_notes').textContent = booking.notes || 'Không có';
            
            // print button show/hide
            const printBtn = document.getElementById('printInvoiceBtn');
            if (booking.status === 'checked_out') {
                printBtn.style.display = 'inline-block';
                printBtn.onclick = () => printInvoice(booking.id);
            } else {
                printBtn.style.display = 'none';
            }
        });
    });
});

// in hoa don
function printInvoice(bookingId) {
    window.open(`print-invoice.php?booking_id=${bookingId}`, '_blank', 'width=800,height=600');
}
</script>

<?php require_once 'includes/footer.php'; ?> 