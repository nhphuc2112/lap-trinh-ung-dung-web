<?php
require_once 'includes/header.php';
require_once 'classes/Room.php';

// filter parameters
$filters = [
    'type' => $_GET['type'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'capacity' => $_GET['capacity'] ?? '',
    'sort' => $_GET['sort'] ?? 'price_asc',
    'page' => max(1, intval($_GET['page'] ?? 1)),
    'per_page' => 9
];

// room get
$room = new Room();
$rooms = $room->getRooms($filters);
$totalRooms = $room->getTotalRooms($filters);
$totalPages = ceil($totalRooms / $filters['per_page']);

// get room types for filter
$roomTypes = $room->getRoomTypes();
?>

<!-- Hero section -->
<section class="bg-light py-5">
    <div class="container">
        <h1 class="h2 mb-0">Danh sách phòng</h1>
    </div>
</section>

<!-- Filters section -->
<section class="py-4 border-bottom">
    <div class="container">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Loại phòng</label>
                <select name="type" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" 
                                <?php echo $filters['type'] === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Giá tối thiểu</label>
                <input type="number" name="min_price" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['min_price']); ?>" 
                       placeholder="0">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Giá tối đa</label>
                <input type="number" name="max_price" class="form-control" 
                       value="<?php echo htmlspecialchars($filters['max_price']); ?>" 
                       placeholder="9999999">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Sức chứa</label>
                <select name="capacity" class="form-select">
                    <option value="">Tất cả</option>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?php echo $i; ?>" 
                                <?php echo $filters['capacity'] == $i ? 'selected' : ''; ?>>
                            <?php echo $i; ?> người
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Sắp xếp</label>
                <select name="sort" class="form-select">
                    <option value="price_asc" <?php echo $filters['sort'] === 'price_asc' ? 'selected' : ''; ?>>
                        Giá tăng dần
                    </option>
                    <option value="price_desc" <?php echo $filters['sort'] === 'price_desc' ? 'selected' : ''; ?>>
                        Giá giảm dần
                    </option>
                    <option value="capacity_asc" <?php echo $filters['sort'] === 'capacity_asc' ? 'selected' : ''; ?>>
                        Sức chứa tăng dần
                    </option>
                    <option value="capacity_desc" <?php echo $filters['sort'] === 'capacity_desc' ? 'selected' : ''; ?>>
                        Sức chứa giảm dần
                    </option>
                </select>
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Rooms section -->
<section class="py-5">
    <div class="container">
        <?php if (empty($rooms)): ?>
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted mb-3"></i>
                <h3 class="h4 mb-3">Không tìm thấy phòng phù hợp</h3>
                <p class="text-muted mb-0">
                    Vui lòng thử lại với bộ lọc khác hoặc 
                    <a href="rooms.php" class="text-decoration-none">xem tất cả phòng</a>.
                </p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($rooms as $room): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <img src="<?php echo htmlspecialchars($room['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($room['room_number']); ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($room['room_number']); ?> - 
                                    <?php echo htmlspecialchars($room['type']); ?>
                                </h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($room['description']); ?>
                                </p>
                                <ul class="list-unstyled mb-3">
                                    <li class="mb-2">
                                        <i class="bi bi-people me-2"></i>
                                        <?php echo $room['capacity']; ?> người
                                    </li>
                                </ul>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0 text-primary">
                                        <?php echo number_format($room['price_per_night']); ?>đ/đêm
                                    </span>
                                    <a href="room-details.php?id=<?php echo $room['id']; ?>" 
                                       class="btn btn-primary">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($filters['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $filters['page'] - 1])); ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $filters['page'] - 2);
                        $endPage = min($totalPages, $filters['page'] + 2);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($filters, ['page' => 1])) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($i === $filters['page'] ? 'active' : '') . '">';
                            echo '<a class="page-link" href="?' . http_build_query(array_merge($filters, ['page' => $i])) . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($filters, ['page' => $totalPages])) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <?php if ($filters['page'] < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $filters['page'] + 1])); ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 