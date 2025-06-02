<?php
require_once 'includes/header.php';
require_once 'classes/Room.php';

$room = new Room();
$featuredRooms = $room->getAllRooms();
?>

<!-- Hero Section -->
<section class="hero bg-dark text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4 bd`">Chào mừng đến với Hotel Management</h1>
                <p class="lead mb-4">Khám phá trải nghiệm nghỉ dưỡng tuyệt vời với dịch vụ đẳng cấp và tiện nghi hiện đại.</p>
                <a href="rooms.php" class="btn btn-primary btn-lg">Đặt phòng ngay</a>
            </div>
            <div class="col-md-6">
                <img src="<?= ASSETS_URL ?>/images/hero-image.jpg" alt="Hotel" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section mb-5">
    <div class="container">
        <div class="card shadow">
            <div class="card-body">
                <form id="searchForm" action="rooms.php" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="check_in" class="form-label">Ngày nhận phòng</label>
                        <input type="date" class="form-control" id="check_in" name="check_in" required>
                    </div>
                    <div class="col-md-3">
                        <label for="check_out" class="form-label">Ngày trả phòng</label>
                        <input type="date" class="form-control" id="check_out" name="check_out" required>
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">Loại phòng</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Tất cả</option>
                            <?php foreach ($room->getRoomTypes() as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="capacity" class="form-label">Số người</label>
                        <select class="form-select" id="capacity" name="capacity">
                            <option value="">Tất cả</option>
                            <option value="1">1 người</option>
                            <option value="2">2 người</option>
                            <option value="3">3 người</option>
                            <option value="4">4 người</option>
                        </select>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg">Tìm phòng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Featured Rooms -->
<section class="featured-rooms mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Phòng nổi bật</h2>
        <div class="row g-4">
            <?php foreach ($featuredRooms as $room): ?>
                <div class="col-md-4">
                    <div class="card room-card h-100">
                        <img src="<?= ASSETS_URL ?>/images/rooms/<?= htmlspecialchars($room['room_number']) ?>.jpg" 
                             class="card-img-top" 
                             alt="Room <?= htmlspecialchars($room['room_number']) ?>"
                             onerror="this.src='<?= ASSETS_URL ?>/images/room-placeholder.jpg'">
                        <div class="room-status <?= $room['status'] ?>">
                            <?= ucfirst($room['status']) ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Phòng <?= htmlspecialchars($room['room_number']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($room['type']) ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <?= $room['capacity'] ?> người
                                </small>
                            </p>
                            <p class="room-price mb-3">
                                <?= number_format($room['price_per_night'], 0, ',', '.') ?> VNĐ/đêm
                            </p>
                            <a href="room-details.php?id=<?= $room['id'] ?>" class="btn btn-outline-primary">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features mb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <i class="fas fa-concierge-bell fa-3x text-primary mb-3"></i>
                    <h3>Dịch vụ đẳng cấp</h3>
                    <p class="text-muted">Trải nghiệm dịch vụ 5 sao với đội ngũ nhân viên chuyên nghiệp</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <i class="fas fa-utensils fa-3x text-primary mb-3"></i>
                    <h3>Ẩm thực đa dạng</h3>
                    <p class="text-muted">Thưởng thức các món ăn đặc sắc từ các đầu bếp hàng đầu</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <i class="fas fa-spa fa-3x text-primary mb-3"></i>
                    <h3>Tiện nghi hiện đại</h3>
                    <p class="text-muted">Tận hưởng các tiện nghi cao cấp và không gian thoáng đãng</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 