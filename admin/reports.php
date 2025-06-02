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

// tham so
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // ngay dau thang
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // hien tai
$reportType = $_GET['type'] ?? 'revenue'; // doanh thu

// get stats
$stats = [
    'total_revenue' => $booking->getTotalRevenue($startDate, $endDate),
    'total_bookings' => $booking->getTotalBookings($startDate, $endDate),
    'total_rooms' => $room->getTotalRooms(),
    'total_users' => $user->getTotalUsers(),
    'occupancy_rate' => $room->getOccupancyRate($startDate, $endDate),
    'average_room_price' => $room->getAverageRoomPrice(),
    'booking_status' => $booking->getBookingStatusStats($startDate, $endDate),
    'room_type_stats' => $room->getRoomTypeStats($startDate, $endDate),
    'monthly_revenue' => $booking->getMonthlyRevenue(date('Y')),
    'top_rooms' => $room->getTopBookedRooms($startDate, $endDate, 5),
    'recent_bookings' => $booking->getRecentBookings(5)
];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Báo cáo thống kê</h1>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($reportType); ?>">
                <div class="input-group">
                    <span class="input-group-text">Từ</span>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Đến</span>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Lọc</button>
            </form>
            <div class="btn-group">
                <a href="?type=revenue&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" 
                   class="btn btn-outline-primary <?php echo $reportType === 'revenue' ? 'active' : ''; ?>">
                    Doanh thu
                </a>
                <a href="?type=bookings&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" 
                   class="btn btn-outline-primary <?php echo $reportType === 'bookings' ? 'active' : ''; ?>">
                    Đặt phòng
                </a>
                <a href="?type=rooms&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" 
                   class="btn btn-outline-primary <?php echo $reportType === 'rooms' ? 'active' : ''; ?>">
                    Phòng
                </a>
            </div>
        </div>
    </div>

    <!-- thong ke tong quan -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Tổng doanh thu</h6>
                    <h3 class="card-title mb-0"><?php echo number_format($stats['total_revenue']); ?> VNĐ</h3>
                    <small class="text-muted">Từ <?php echo date('d/m/Y', strtotime($startDate)); ?> đến <?php echo date('d/m/Y', strtotime($endDate)); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Tổng đặt phòng</h6>
                    <h3 class="card-title mb-0"><?php echo number_format($stats['total_bookings']); ?></h3>
                    <small class="text-muted">Đơn đặt phòng trong kỳ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Tỷ lệ lấp đầy</h6>
                    <h3 class="card-title mb-0"><?php echo number_format($stats['occupancy_rate'], 1); ?>%</h3>
                    <small class="text-muted">Tỷ lệ phòng được sử dụng</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Giá phòng trung bình</h6>
                    <h3 class="card-title mb-0"><?php echo number_format($stats['average_room_price']); ?> VNĐ</h3>
                    <small class="text-muted">Giá trung bình/đêm</small>
                </div>
            </div>
        </div>
    </div>


<?php require_once '../includes/footer.php'; ?> 