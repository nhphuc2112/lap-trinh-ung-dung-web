<?php
require_once 'includes/header.php';
require_once 'classes/Booking.php';
require_once 'classes/Room.php';
require_once 'classes/User.php';

// login check
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Vui lòng đăng nhập để xem hóa đơn!');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$booking = new Booking();
$room = new Room();
$user = new User();

// get booking info
$bookingId = $_GET['booking_id'] ?? 0;
$bookingInfo = $booking->getBookingById($bookingId);

// quyen kiem tra hoa don
if (!$bookingInfo || 
    ($bookingInfo['user_id'] !== $_SESSION['user_id'] && !isAdmin()) || 
    $bookingInfo['status'] !== 'checked_out') {
    setFlashMessage('danger', 'Không tìm thấy hóa đơn hoặc bạn không có quyền xem!');
    redirect('my-bookings.php');
}

// get room and user info
$roomInfo = $room->getRoomById($bookingInfo['room_id']);
$userInfo = $user->getUserById($bookingInfo['user_id']);

// nights check
$checkInDate = new DateTime($bookingInfo['check_in']);
$checkOutDate = new DateTime($bookingInfo['check_out']);
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?= $bookingId ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <style>
        @media print {
            body {
                padding: 20px;
            }
            .no-print {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .invoice-header p {
            margin: 0;
            color: #666;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-table th {
            background-color: #f8f9fa;
        }
        .invoice-total {
            text-align: right;
            margin-top: 20px;
        }
        .invoice-footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <!-- print button -->
                <div class="text-end mb-4 no-print">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> In hóa đơn
                    </button>
                </div>

                <!-- header hoa don -->
                <div class="invoice-header">
                    <h1>HÓA ĐƠN ĐẶT PHÒNG</h1>
                    <p>Mã hóa đơn: #<?= $bookingId ?></p>
                    <p>Ngày xuất: <?= date('d/m/Y H:i') ?></p>
                </div>

                <!-- thong tin khach hang va phong -->
                <div class="row invoice-details">
                    <div class="col-md-6">
                        <h5>Thông tin khách hàng</h5>
                        <p>
                            <strong>Họ tên:</strong> <?= htmlspecialchars($userInfo['name']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($userInfo['email']) ?><br>
                            <strong>Số điện thoại:</strong> <?= htmlspecialchars($userInfo['phone'] ?? 'N/A') ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin phòng</h5>
                        <p>
                            <strong>Số phòng:</strong> <?= htmlspecialchars($roomInfo['room_number']) ?><br>
                            <strong>Loại phòng:</strong> <?= htmlspecialchars($roomInfo['type']) ?><br>
                            <strong>Check-in:</strong> <?= date('d/m/Y', strtotime($bookingInfo['check_in'])) ?><br>
                            <strong>Check-out:</strong> <?= date('d/m/Y', strtotime($bookingInfo['check_out'])) ?>
                        </p>
                    </div>
                </div>

                <!-- chi tiet hoa don -->
                <table class="table invoice-table">
                    <thead>
                        <tr>
                            <th>Mô tả</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tiền phòng (<?= htmlspecialchars($roomInfo['type']) ?>)</td>
                            <td class="text-end"><?= number_format($roomInfo['price_per_night'], 0, ',', '.') ?> VNĐ</td>
                            <td class="text-end"><?= $nights ?> đêm</td>
                            <td class="text-end"><?= number_format($bookingInfo['total_price'], 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    </tbody>
                </table>

                <!-- tong tien -->
                <div class="invoice-total">
                    <h4>Tổng cộng: <?= number_format($bookingInfo['total_price'], 0, ',', '.') ?> VNĐ</h4>
                    <p class="text-muted">(Bằng chữ: <?= ucfirst(numberToWords($bookingInfo['total_price'])) ?> đồng)</p>
                </div>

                <!-- footer hoa don -->
                <div class="invoice-footer">
                    <p>Cam on quy khach da su dung dich vu cua chung toi!</p>
                    <p>Hóa đơn này có giá trị thanh toán và không cần chữ ký.</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // ham chuyen tien
    function numberToWords($number) {
        $ones = array(
            0 => '', 1 => 'một', 2 => 'hai', 3 => 'ba', 4 => 'bốn',
            5 => 'năm', 6 => 'sáu', 7 => 'bảy', 8 => 'tám', 9 => 'chín',
            10 => 'mười', 11 => 'mười một', 12 => 'mười hai', 13 => 'mười ba',
            14 => 'mười bốn', 15 => 'mười lăm', 16 => 'mười sáu',
            17 => 'mười bảy', 18 => 'mười tám', 19 => 'mười chín'
        );
        $tens = array(
            2 => 'hai mươi', 3 => 'ba mươi', 4 => 'bốn mươi', 5 => 'năm mươi',
            6 => 'sáu mươi', 7 => 'bảy mươi', 8 => 'tám mươi', 9 => 'chín mươi'
        );
        
        if ($number == 0) {
            return 'không';
        }
        
        $words = '';
        
        if (($number / 1000000000) >= 1) {
            $words .= numberToWords(floor($number / 1000000000)) . ' tỷ ';
            $number = $number % 1000000000;
        }
        
        if (($number / 1000000) >= 1) {
            $words .= numberToWords(floor($number / 1000000)) . ' triệu ';
            $number = $number % 1000000;
        }
        
        if (($number / 1000) >= 1) {
            $words .= numberToWords(floor($number / 1000)) . ' nghìn ';
            $number = $number % 1000;
        }
        
        if (($number / 100) >= 1) {
            $words .= $ones[floor($number / 100)] . ' trăm ';
            $number = $number % 100;
        }
        
        if ($number > 0) {
            if ($number < 20) {
                $words .= $ones[$number];
            } else {
                $words .= $tens[floor($number / 10)];
                if ($number % 10 > 0) {
                    $words .= ' ' . $ones[$number % 10];
                }
            }
        }
        
        return trim($words);
    }
    ?>
</body>
</html> 