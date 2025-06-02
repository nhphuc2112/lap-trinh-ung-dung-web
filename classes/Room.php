<?php
class Room {
    private $db;
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    private $maxFileSize = 5242880; // 5MB

    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadDir = dirname(__DIR__) . '/assets/images/rooms/';
        
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function createRoom($data, $image = null) {
        try {
            if ($this->getRoomByNumber($data['room_number'])) {
                return false;
            }

            $imagePath = null;
            if ($image && $image['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($image, $data['room_number']);
                if ($imagePath === false) {
                    return false;
                }
            }

            $sql = "INSERT INTO rooms (room_number, type, price_per_night, capacity, description, status, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $result = $this->db->query($sql, [
                $data['room_number'],
                $data['type'],
                $data['price_per_night'],
                $data['capacity'],
                $data['description'] ?? null,
                $data['status'] ?? 'available',
                $imagePath
            ]);

            if (!$result || $result->rowCount() === 0) {
                if ($imagePath) {
                    $this->deleteImage($imagePath);
                }
                return false;
            }

            return true;
        } catch (Exception $e) {
            if (isset($imagePath) && $imagePath) {
                $this->deleteImage($imagePath);
            }
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateRoom($roomId, $data, $image = null) {
        try {
            $allowedFields = ['type', 'price_per_night', 'capacity', 'description', 'status'];
            $updates = [];
            $values = [];

            if ($image && $image['error'] === UPLOAD_ERR_OK) {
                $room = $this->getRoomById($roomId);
                if ($room) {
                    if ($room['image']) {
                        $this->deleteImage($room['image']);
                    }
                    $imagePath = $this->handleImageUpload($image, $room['room_number']);
                    if ($imagePath) {
                        $updates[] = "image = ?";
                        $values[] = $imagePath;
                    }
                }
            }

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "$key = ?";
                    $values[] = $value;
                }
            }

            if (empty($updates)) {
                return false;
            }

            $values[] = $roomId;
            $sql = "UPDATE rooms SET " . implode(', ', $updates) . " WHERE id = ?";
            return $this->db->query($sql, $values)->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function handleImageUpload($file, $roomNumber) {
        try {
            if (!in_array($file['type'], $this->allowedTypes)) {
                setFlashMessage('danger', 'Chỉ chấp nhận file ảnh (JPG, JPEG, PNG)');
                return false;
            }

            if ($file['size'] > $this->maxFileSize) {
                setFlashMessage('danger', 'Kích thước file không được vượt quá 5MB');
                return false;
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = $roomNumber . '_' . time() . '.' . $extension;
            $uploadPath = $this->uploadDir . $newFileName;
            $relativePath = 'assets/images/rooms/' . $newFileName;

            if (!is_dir($this->uploadDir)) {
                if (!mkdir($this->uploadDir, 0777, true)) {
                    setFlashMessage('danger', 'Không thể tạo thư mục upload');
                    return false;
                }
            }

            if (!is_writable($this->uploadDir)) {
                setFlashMessage('danger', 'Thư mục upload không có quyền ghi');
                return false;
            }

            // Upload file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                setFlashMessage('danger', 'Không thể upload ảnh. Vui lòng thử lại.');
                return false;
            }

            if (!file_exists($uploadPath)) {
                setFlashMessage('danger', 'Không thể tạo file ảnh');
                return false;
            }

            return $relativePath;
        } catch (Exception $e) {
            error_log($e->getMessage());
            setFlashMessage('danger', 'Lỗi khi upload ảnh: ' . $e->getMessage());
            return false;
        }
    }

    private function deleteImage($imagePath) {
        try {
            $fullPath = dirname(__DIR__) . '/' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function getRoomById($roomId) {
        try {
            $sql = "SELECT * FROM rooms WHERE id = ?";
            return $this->db->fetch($sql, [$roomId]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getRoomByNumber($roomNumber) {
        try {
            $sql = "SELECT * FROM rooms WHERE room_number = ?";
            return $this->db->fetch($sql, [$roomNumber]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getAllRooms() {
        try {
            $sql = "SELECT * FROM rooms ORDER BY room_number";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getAvailableRooms($checkIn, $checkOut) {
        try {
            $sql = "SELECT r.* 
                    FROM rooms r
                    WHERE r.status = 'available'
                    AND r.id NOT IN (
                        SELECT room_id 
                        FROM bookings 
                        WHERE status != 'cancelled'
                        AND (
                            (check_in <= ? AND check_out >= ?)
                            OR (check_in <= ? AND check_out >= ?)
                            OR (check_in >= ? AND check_out <= ?)
                        )
                    )
                    ORDER BY r.room_number";
            
            return $this->db->fetchAll($sql, [
                $checkOut, $checkIn,
                $checkIn, $checkIn,
                $checkIn, $checkOut
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function deleteRoom($roomId) {
        try {
            $sql = "SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status != 'cancelled'";
            $count = $this->db->fetch($sql, [$roomId])['COUNT(*)'];
            
            if ($count > 0) {
                return false;
            }

            $room = $this->getRoomById($roomId);
            if ($room && $room['image']) {
                $this->deleteImage($room['image']);
            }

            $sql = "DELETE FROM rooms WHERE id = ?";
            return $this->db->query($sql, [$roomId])->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateRoomStatus($roomId, $status) {
        try {
            $sql = "UPDATE rooms SET status = ? WHERE id = ?";
            return $this->db->query($sql, [$status, $roomId])->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getRoomTypes() {
        try {
            $sql = "SELECT DISTINCT type FROM rooms ORDER BY type";
            $types = $this->db->fetchAll($sql);
            return array_column($types, 'type');
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function searchRooms($filters = []) {
        try {
            $conditions = [];
            $values = [];

            if (!empty($filters['type'])) {
                $conditions[] = "type = ?";
                $values[] = $filters['type'];
            }

            if (!empty($filters['min_price'])) {
                $conditions[] = "price_per_night >= ?";
                $values[] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $conditions[] = "price_per_night <= ?";
                $values[] = $filters['max_price'];
            }

            if (!empty($filters['capacity'])) {
                $conditions[] = "capacity >= ?";
                $values[] = $filters['capacity'];
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $values[] = $filters['status'];
            }

            $sql = "SELECT * FROM rooms";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            $sql .= " ORDER BY room_number";

            return $this->db->fetchAll($sql, $values);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tổng số phòng theo bộ lọc
     * 
     * @param array $filters Các tham số lọc (type, min_price, max_price, capacity)
     * @return int Tổng số phòng
     */
    public function getTotalRooms($filters = []) {
        try {
            $conditions = [];
            $values = [];

            // room type filter
            if (!empty($filters['type'])) {
                $conditions[] = "type = ?";
                $values[] = $filters['type'];
            }

            // price filter
            if (!empty($filters['min_price'])) {
                $conditions[] = "price_per_night >= ?";
                $values[] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $conditions[] = "price_per_night <= ?";
                $values[] = $filters['max_price'];
            }

            // capacity filter
            if (!empty($filters['capacity'])) {
                $conditions[] = "capacity = ?";
                $values[] = $filters['capacity'];
            }

             //sql query
            $sql = "SELECT COUNT(*) as total FROM rooms";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $result = $this->db->fetch($sql, $values);
            return (int) $result['total'];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getOccupancyRate($startDate, $endDate) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT r.id) * DATEDIFF(?, ?) as total_nights,
                        SUM(
                            CASE 
                                WHEN b.check_in <= ? AND b.check_out >= ? 
                                THEN DATEDIFF(
                                    LEAST(b.check_out, ?),
                                    GREATEST(b.check_in, ?)
                                )
                                ELSE 0 
                            END
                        ) as booked_nights
                    FROM rooms r
                    LEFT JOIN bookings b ON r.id = b.room_id 
                        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
                        AND b.check_in <= ? 
                        AND b.check_out >= ?";
            
            $result = $this->db->fetch($sql, [
                $endDate, $startDate,
                $endDate, $startDate,
                $endDate, $startDate,
                $endDate, $startDate
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy giá trung bình của phòng
     * @return float Giá trung bình
     */
    public function getAverageRoomPrice() {
        try {
            $sql = "SELECT AVG(price_per_night) as avg_price FROM rooms WHERE status = 'available'";
            $result = $this->db->fetch($sql);
            return round($result['avg_price'] ?? 0, 2);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy thống kê theo loại phòng
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @return array Thống kê theo loại phòng
     */
    public function getRoomTypeStats($startDate, $endDate) {
        try {
            $sql = "SELECT 
                        r.type,
                        COUNT(DISTINCT r.id) as total_rooms,
                        COUNT(DISTINCT b.id) as total_bookings,
                        AVG(r.price_per_night) as avg_price
                    FROM rooms r
                    LEFT JOIN bookings b ON r.id = b.room_id 
                        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
                        AND b.check_in <= ? 
                        AND b.check_out >= ?
                    GROUP BY r.type
                    ORDER BY total_bookings DESC";
            
            return $this->db->fetchAll($sql, [$endDate, $startDate]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getTopBookedRooms($startDate, $endDate, $limit = 5) {
        try {
            $sql = "SELECT 
                        r.*,
                        COUNT(b.id) as booking_count,
                        AVG(DATEDIFF(b.check_out, b.check_in)) as avg_stay_days
                    FROM rooms r
                    LEFT JOIN bookings b ON r.id = b.room_id 
                        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
                        AND b.check_in <= ? 
                        AND b.check_out >= ?
                    GROUP BY r.id
                    ORDER BY booking_count DESC, avg_stay_days DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$endDate, $startDate, $limit]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    public function getRooms($filters = []) {
        try {
            $conditions = [];
            $values = [];

            if (!empty($filters['type'])) {
                $conditions[] = "type = ?";
                $values[] = $filters['type'];
            }

            if (!empty($filters['min_price'])) {
                $conditions[] = "price_per_night >= ?";
                $values[] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $conditions[] = "price_per_night <= ?";
                $values[] = $filters['max_price'];
            }

            if (!empty($filters['capacity'])) {
                $conditions[] = "capacity = ?";
                $values[] = $filters['capacity'];
            }

            $sql = "SELECT * FROM rooms";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            switch ($filters['sort'] ?? 'price_asc') {
                case 'price_desc':
                    $sql .= " ORDER BY price_per_night DESC";
                    break;
                case 'capacity_asc':
                    $sql .= " ORDER BY capacity ASC";
                    break;
                case 'capacity_desc':
                    $sql .= " ORDER BY capacity DESC";
                    break;
                default:
                    $sql .= " ORDER BY price_per_night ASC";
            }

            if (!empty($filters['per_page'])) {
                $offset = ($filters['page'] - 1) * $filters['per_page'];
                $sql .= " LIMIT ? OFFSET ?";
                $values[] = $filters['per_page'];
                $values[] = $offset;
            }

            return $this->db->fetchAll($sql, $values);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách phòng nổi bật
     * 
     * @param int $limit Số lượng phòng cần lấy
     * @return array Danh sách phòng nổi bật
     */
    public function getFeaturedRooms($limit = 6) {
        try {
            $sql = "SELECT * FROM rooms WHERE status = 'available' ORDER BY RAND() LIMIT ?";
            return $this->db->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Kiểm tra phòng có trống trong khoảng thời gian không
     * @param int $roomId ID phòng
     * @param string $checkIn Ngày check-in (Y-m-d)
     * @param string $checkOut Ngày check-out (Y-m-d)
     * @return bool True nếu phòng trống, False nếu đã được đặt
     */
    public function isRoomAvailable($roomId, $checkIn, $checkOut) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM bookings 
                    WHERE room_id = ? 
                    AND status != 'cancelled'
                    AND (
                        (check_in <= ? AND check_out >= ?)
                        OR (check_in <= ? AND check_out >= ?)
                        OR (check_in >= ? AND check_out <= ?)
                    )";
            
            $result = $this->db->fetch($sql, [
                $roomId,
                $checkOut, $checkIn,
                $checkIn, $checkIn,
                $checkIn, $checkOut
            ]);

            return $result['count'] === 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
} 