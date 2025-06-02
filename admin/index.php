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

// get stats
$bookingStats = $booking->getBookingStats();
$totalRooms = count($room->getAllRooms());
$totalUsers = count($user->getAllUsers());

// get recent bookings
$recentBookings = $booking->getAllBookings(5);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Dashboard</h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Tổng đặt phòng</h6>
                            <h2 class="mt-2 mb-0"><?= $bookingStats['total'] ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Doanh thu</h6>
                            <h2 class="mt-2 mb-0"><?= number_format($bookingStats['total_revenue'] ?? 0, 0, ',', '.') ?> VNĐ</h2>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Tổng phòng</h6>
                            <h2 class="mt-2 mb-0"><?= $totalRooms ?></h2>
                        </div>
                        <i class="fas fa-door-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Tổng người dùng</h6>
                            <h2 class="mt-2 mb-0"><?= $totalUsers ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Trạng thái đặt phòng</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="status-badge status-pending me-2"><?= $bookingStats['pending'] ?? 0 ?></div>
                                <span>Đang chờ</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="status-badge status-confirmed me-2"><?= $bookingStats['confirmed'] ?? 0 ?></div>
                                <span>Đã xác nhận</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="status-badge status-completed me-2"><?= $bookingStats['completed'] ?? 0 ?></div>
                                <span>Hoàn thành</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="status-badge status-cancelled me-2"><?= $bookingStats['cancelled'] ?? 0 ?></div>
                                <span>Đã hủy</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Đặt phòng gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Phòng</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td>#<?= $booking['id'] ?></td>
                                        <td><?= htmlspecialchars($booking['name']) ?></td>
                                        <td><?= htmlspecialchars($booking['room_number']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $booking['status'] ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="booking-details.php?id=<?= $booking['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thao tác nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="rooms.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-door-open me-2"></i>Quản lý phòng
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="bookings.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-check me-2"></i>Quản lý đặt phòng
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="users.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-users me-2"></i>Quản lý người dùng
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="reports.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-chart-bar me-2"></i>Báo cáo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 