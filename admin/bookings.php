<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../includes/header.php';
require_once '../classes/Booking.php';
require_once '../classes/Room.php';
require_once '../classes/User.php';

$booking = new Booking();
$room = new Room();
$user = new User();

// booking get
$bookings = $booking->getAllBookings();

?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Danh sách đặt phòng</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Phòng</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($bookings)): ?>
    <tr><td colspan="7" class="text-center">Không có bản ghi đặt phòng nào.</td></tr>
<?php endif; ?>

                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['id'] ?></td>
                        <td><?= htmlspecialchars($booking['name']) ?> <br>
                            <small><?= htmlspecialchars($booking['email']) ?></small>
                        </td>
                        <td>Phòng <?= htmlspecialchars($booking['room_number']) ?> <br>
                            <small><?= htmlspecialchars($booking['type']) ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($booking['check_in'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($booking['check_out'])) ?></td>
                        <td><?= number_format($booking['total_price'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= ucfirst($booking['status']) ?></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>