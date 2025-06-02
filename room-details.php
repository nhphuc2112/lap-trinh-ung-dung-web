<?php
require_once 'includes/init.php';
require_once 'classes/Room.php';
require_once 'classes/Booking.php';
require_once 'classes/User.php';
$room = new Room();
$booking = new Booking();
$user = new User();

if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_availability') {
    header('Content-Type: application/json');
    try {
        $roomId = $_GET['room_id'] ?? 0;
        $checkIn = $_GET['check_in'] ?? '';
        $checkOut = $_GET['check_out'] ?? '';

        if (!$roomId || !$checkIn || !$checkOut) {
            throw new Exception('Missing required parameters');
        }

        // log to debug
        error_log("AJAX check availability - Room: $roomId, Check-in: $checkIn, Check-out: $checkOut");

        $available = $booking->isRoomAvailable($roomId, $checkIn, $checkOut);

        // log result
        error_log("Room availability result: " . ($available ? 'Available' : 'Not available'));

        echo json_encode([
            'success' => true,
            'available' => $available
        ]);
        exit;
    } catch (Exception $e) {
        error_log("Error in AJAX availability check: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// room get 
$roomId = $_GET['id'] ?? 0;
$roomInfo = $room->getRoomById($roomId);

if (!$roomInfo) {
    setFlashMessage('danger', 'Không tìm thấy thông tin phòng!');
    redirect('rooms.php');
}

// room request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Vui lòng đăng nhập để đặt phòng!');
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }

    $checkIn = $_POST['check_in'] ?? '';
    $checkOut = $_POST['check_out'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // date check
    if (!$checkIn || !$checkOut || strtotime($checkIn) >= strtotime($checkOut)) {
        setFlashMessage('danger', 'Ngày trả phòng phải sau ngày nhận phòng!');
        redirect("room-details.php?id=$roomId&check_in=$checkIn&check_out=$checkOut");
    }

    // room available check
    if (!$booking->isRoomAvailable($roomId, $checkIn, $checkOut)) {
        setFlashMessage('danger', 'Phòng đã được đặt trong khoảng thời gian này!');
        redirect("room-details.php?id=$roomId&check_in=$checkIn&check_out=$checkOut");
    }

    // tinh tong tien
    $nights = ceil((strtotime($checkOut) - strtotime($checkIn)) / (60 * 60 * 24));
    $totalPrice = $nights * $roomInfo['price_per_night'];

    $bookingData = [
        'user_id' => $_SESSION['user_id'],
        'room_id' => $roomId,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'total_price' => $totalPrice,
        'notes' => $notes,
        'status' => 'pending'
    ];

    $result = $booking->createBooking($bookingData);

    if ($result) {
        echo "<h1>Đặt phòng thành công!</h1>";
        redirect('my-bookings.php');
    } else {
        echo "<h1>Đặt phòng không thành công!</h1>";
        redirect("room-details.php?id=$roomId&check_in=$checkIn&check_out=$checkOut");
    }
}

// check in/out 
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';

// header include
require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Thông tin phòng -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php if ($roomInfo['image']): ?>
                            <div class="carousel-item active">
                                <img src="<?= htmlspecialchars($roomInfo['image']) ?>"
                                    class="d-block w-100"
                                    alt="Room <?= htmlspecialchars($roomInfo['room_number']) ?>"
                                    style="height: 400px; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="carousel-item active">
                                <img src="<?= ASSETS_URL ?>/images/room-placeholder.jpg"
                                    class="d-block w-100"
                                    alt="Room <?= htmlspecialchars($roomInfo['room_number']) ?>"
                                    style="height: 400px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($roomInfo['image']): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h1 class="card-title h3">Phòng <?= htmlspecialchars($roomInfo['room_number']) ?></h1>
                    <p class="text-muted"><?= htmlspecialchars($roomInfo['type']) ?></p>

                    <div class="room-features mb-4">
                        <h5>Tiện nghi phòng</h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <i class="fas fa-user-friends"></i> <?= $roomInfo['capacity'] ?> người
                            </div>
                            <div class="col-6 col-md-4">
                                <i class="fas fa-wifi"></i> WiFi miễn phí
                            </div>
                            <div class="col-6 col-md-4">
                                <i class="fas fa-tv"></i> TV màn hình phẳng
                            </div>
                            <div class="col-6 col-md-4">
                                <i class="fas fa-snowflake"></i> Điều hòa
                            </div>
                            <div class="col-6 col-md-4">
                                <i class="fas fa-shower"></i> Phòng tắm riêng
                            </div>
                            <div class="col-6 col-md-4">
                                <i class="fas fa-coffee"></i> Mini bar
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($roomInfo['description'])): ?>
                        <div class="room-description mb-4">
                            <h5>Mô tả</h5>
                            <p><?= nl2br(htmlspecialchars($roomInfo['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="room-description mb-4">
                        <h5>Thông tin chuyển khoản</h5>
                        <p>STK: 1234567890</p>
                        <p>Ngân hàng: Vietcombank</p>
                        <p>Chủ tài khoản: Nguyễn Văn A</p>
                        <p>Số tiền: <?= number_format($roomInfo['price_per_night'], 0, ',', '.') ?> VNĐ</p>
                        <p>Nội dung chuyển khoản: Đặt phòng <?= htmlspecialchars($roomInfo['room_number']) ?>, khách hàng <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                    </div>

                    <div class="room-price mb-4">
                        <h5>Giá phòng</h5>
                        <p class="h4 text-primary mb-0">
                            <?= number_format($roomInfo['price_per_night'], 0, ',', '.') ?> VNĐ
                            <small class="text-muted">/ đêm</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form đặt phòng -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Đặt phòng</h5>
                    <form method="POST" id="bookingForm" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="book">

                        <div class="mb-3">
                            <label for="check_in" class="form-label">Ngày nhận phòng</label>
                            <input type="date" class="form-control" id="check_in" name="check_in"
                                value="<?= htmlspecialchars($checkIn) ?>" required min="<?= date('Y-m-d') ?>">
                            <div class="invalid-feedback">Vui lòng chọn ngày nhận phòng</div>
                        </div>

                        <div class="mb-3">
                            <label for="check_out" class="form-label">Ngày trả phòng</label>
                            <input type="date" class="form-control" id="check_out" name="check_out"
                                value="<?= htmlspecialchars($checkOut) ?>" required>
                            <div class="invalid-feedback">Vui lòng chọn ngày trả phòng</div>
                        </div>

                        <div class="mb-3">
                            <label for="nights" class="form-label">Số đêm</label>
                            <input type="text" class="form-control" id="nights" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="total_price" class="form-label">Tổng tiền</label>
                            <input type="text" class="form-control" id="total_price" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Ghi chú..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Đặt phòng ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Flash message function
    function showMessage(type, message) {
        // alert 
        let alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            alertContainer.className = 'position-fixed top-0 end-0 p-3';
            alertContainer.style.zIndex = '1050';
            document.body.appendChild(alertContainer);
        }

        // Create alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // add to container
        alertContainer.appendChild(alertDiv);

        // auto hide after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bookingForm');
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const nightsInput = document.getElementById('nights');
        const totalPriceInput = document.getElementById('total_price');
        const pricePerNight = <?= $roomInfo['price_per_night'] ?>;

        async function checkRoomAvailability(checkIn, checkOut) {
            try {
                // format dates to YYYY-MM-DD
                const formattedCheckIn = new Date(checkIn).toISOString().split('T')[0];
                const formattedCheckOut = new Date(checkOut).toISOString().split('T')[0];

                const response = await fetch(`room-details.php?ajax=check_availability&room_id=<?= $roomId ?>&check_in=${formattedCheckIn}&check_out=${formattedCheckOut}`);
                const data = await response.json();

                if (!data.success) {
                    console.log ('Có lỗi xảy ra khi kiểm tra phòng trống!')
                    return false;
                }

                if (!data.available) {
                    showMessage('danger', 'Phòng đã được đặt trong khoảng thời gian này!');
                    return false;
                }

                return true;
            } catch (error) {
                console.error('Error checking availability:', error);
                showMessage('danger', 'Có lỗi xảy ra khi kiểm tra phòng trống!');
                return false;
            }
        }

        function calculateTotal() {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);

            if (!checkInInput.value || !checkOutInput.value || checkOut <= checkIn) {
                nightsInput.value = '';
                totalPriceInput.value = '';
                return;
            }

            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            const total = nights * pricePerNight;

            nightsInput.value = nights + ' đêm';
            totalPriceInput.value = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
        }

        // minimum date to today
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        checkInInput.min = today.toISOString().split('T')[0];

        // check-in change
        checkInInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            selectedDate.setHours(0, 0, 0, 0);

            // set minimum check-out date to next day
            const minCheckOut = new Date(selectedDate);
            minCheckOut.setDate(minCheckOut.getDate() + 1);
            checkOutInput.min = minCheckOut.toISOString().split('T')[0];

            // check out reset
            if (checkOutInput.value) {
                const checkOutDate = new Date(checkOutInput.value);
                checkOutDate.setHours(0, 0, 0, 0);
                if (checkOutDate <= selectedDate) {
                    checkOutInput.value = '';
                }
            }

            calculateTotal();
        });

        // checkout change
        checkOutInput.addEventListener('change', calculateTotal);

        // validate form
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            const available = await checkRoomAvailability(checkIn, checkOut);
            if (!available) return;

            form.submit(); // send if available
        });


        // calculate
        if (checkInInput.value && checkOutInput.value) {
            calculateTotal();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>