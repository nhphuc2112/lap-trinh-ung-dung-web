<?php
class Booking {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    //tao dat phong moi
    public function createBooking($data) {
        try {
            $requiredFields = ['user_id', 'room_id', 'check_in', 'check_out', 'total_price', 'status'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new InvalidArgumentException("Missing required field: $field");
                }
            }
            $notes = $data['notes'] ?? null;
            $this->db->beginTransaction();
    
            $sql = "INSERT INTO bookings (user_id, room_id, check_in, check_out, total_price, status, notes) 
                    VALUES (:user_id, :room_id, :check_in, :check_out, :total_price, :status, :notes)";
            $stmt = $this->db->prepare($sql);
    
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':room_id', $data['room_id']);
            $stmt->bindParam(':check_in', $data['check_in']);
            $stmt->bindParam(':check_out', $data['check_out']);
            $stmt->bindParam(':total_price', $data['total_price']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':notes', $notes);
    
            if (!$stmt->execute()) {
                $this->db->rollBack();
                return false;
            }
    
            // cap nhat trang thai phong
            $sql = "UPDATE rooms SET status = 'occupied' WHERE id = :room_id";
            $stmt2 = $this->db->prepare($sql);
            $stmt2->bindParam(':room_id', $data['room_id']);
            if (!$stmt2->execute()) {
                $this->db->rollBack();
                return false;
            }
    
            // transaction commit
            $this->db->commit();
    
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error creating booking: " . $e->getMessage());
            return false;
        }
    }
    
//lay thong tin dat phong theo id
    public function getBookingById($id) {
        try {
            $sql = "SELECT * FROM bookings WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (PDOException $e) {
            error_log("Error getting booking: " . $e->getMessage());
            return false;
        }
    }
//lay danh sach dat phong
    public function getUserBookings($userId) {
        try {
            $sql = "SELECT b.*, r.room_number, r.type 
                    FROM bookings b 
                    INNER JOIN rooms r ON b.room_id = r.id 
                    WHERE b.user_id = :user_id 
                    ORDER BY b.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error getting user bookings: " . $e->getMessage());
            return [];
        }
    }
    public function getAllBookings($limit = null)
    {
        try {
            $sql = "
                SELECT b.*, r.room_number, r.type, u.name, u.email 
                FROM bookings b 
                INNER JOIN rooms r ON b.room_id = r.id 
                INNER JOIN users u ON b.user_id = u.id 
                ORDER BY b.created_at DESC
            ";
    
            if ($limit !== null) {
                $limit = (int)$limit; // injection 
                $sql .= " LIMIT $limit";
            }
    
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error getting all bookings: " . $e->getMessage());
            return [];
        }
        
    }
    //kiem tra phong co trong khoang thoi gian khong
    public function isRoomAvailable($roomId, $checkIn, $checkOut) {
        try {
            // format day
            $checkIn = date('Y-m-d', strtotime($checkIn));
            $checkOut = date('Y-m-d', strtotime($checkOut));

            // check avalable date
            if (strtotime($checkIn) >= strtotime($checkOut)) {
                error_log("Invalid date range: check-in ($checkIn) is not before check-out ($checkOut)");
                return false;
            }

            $sql = "SELECT COUNT(*) as count 
                    FROM bookings 
                    WHERE room_id = :room_id 
                    AND status IN ('pending', 'confirmed', 'checked_in')  /* Chỉ xét các trạng thái đang hoạt động */
                    AND (
                        (check_in <= :check_out AND check_out >= :check_in)
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':room_id', $roomId);
            $stmt->bindParam(':check_in', $checkIn);
            $stmt->bindParam(':check_out', $checkOut);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Log để debug
            error_log("Checking room availability for room $roomId from $checkIn to $checkOut. Count: " . $result['count']);
            
            return $result && $result['count'] == 0;
        } catch (PDOException $e) {
            error_log("Error checking room availability: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($bookingId, $status) {
        try {
            $sql = "UPDATE bookings SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $bookingId);
            
            if ($stmt->execute()) {
                // check trang thai phong
                if ($status === 'checked_out' || $status === 'cancelled') {
                    $sql = "UPDATE rooms r 
                           INNER JOIN bookings b ON r.id = b.room_id 
                           SET r.status = 'available' 
                           WHERE b.id = :booking_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':booking_id', $bookingId);
                    $stmt->execute();
                }
                // if checked_in
                elseif ($status === 'checked_in') {
                    $sql = "UPDATE rooms r 
                           INNER JOIN bookings b ON r.id = b.room_id 
                           SET r.status = 'occupied' 
                           WHERE b.id = :booking_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':booking_id', $bookingId);
                    $stmt->execute();
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error updating booking status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa đặt phòng
     * Chỉ cho phép xóa đặt phòng ở trạng thái pending
     * @return bool True nếu xóa thành công, false nếu thất bại
     */
    public function deleteBooking($bookingId) {
        try {
            $sql = "SELECT status FROM bookings WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $bookingId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result || $result['status'] !== 'pending') {
                return false;
            }

            // Xóa đặt phòng
            $sql = "DELETE FROM bookings WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $bookingId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting booking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tổng doanh thu trong khoảng thời gian
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @return int Tổng doanh thu
     */
    public function getTotalRevenue($startDate, $endDate) {
        try {
            $sql = "SELECT COALESCE(SUM(total_price), 0) as total 
                    FROM bookings 
                    WHERE check_in BETWEEN :start_date AND :end_date 
                    AND status IN ('checked_in', 'checked_out')";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error getting total revenue: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy tổng số đặt phòng trong khoảng thời gian
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @return int Tổng số đặt phòng
     */
    public function getTotalBookings($startDate, $endDate) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM bookings 
                    WHERE check_in BETWEEN :start_date AND :end_date";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error getting total bookings: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy thống kê trạng thái đặt phòng
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @return array Thống kê theo trạng thái
     */
    public function getBookingStatusStats($startDate, $endDate) {
        try {
            $sql = "SELECT status, COUNT(*) as count 
                    FROM bookings 
                    WHERE check_in BETWEEN :start_date AND :end_date 
                    GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $stats = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[] = [
                    'status' => $this->getStatusText($row['status']),
                    'count' => (int)$row['count']
                ];
            }
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting booking status stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy doanh thu theo tháng trong năm
     * @param string $year Năm cần thống kê
     * @return array Doanh thu theo tháng
     */
    public function getMonthlyRevenue($year) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(check_in, '%m') as month,
                        COALESCE(SUM(total_price), 0) as revenue
                    FROM bookings 
                    WHERE YEAR(check_in) = :year 
                    AND status IN ('checked_in', 'checked_out')
                    GROUP BY DATE_FORMAT(check_in, '%m')
                    ORDER BY month";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            $revenue = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $revenue[] = [
                    'month' => 'Tháng ' . (int)$row['month'],
                    'revenue' => (int)$row['revenue']
                ];
            }
            return $revenue;
        } catch (PDOException $e) {
            error_log("Error getting monthly revenue: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách đặt phòng gần đây
     * @param int $limit Số lượng đặt phòng cần lấy
     * @return array Danh sách đặt phòng
     */
    public function getRecentBookings($limit = 5) {
        try {
            $sql = "SELECT b.*, r.room_number, r.type, u.name, u.email 
                    FROM bookings b 
                    JOIN rooms r ON b.room_id = r.id 
                    JOIN users u ON b.user_id = u.id 
                    ORDER BY b.created_at DESC 
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error getting recent bookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Chuyển đổi mã trạng thái sang text
     * @param string $status Mã trạng thái
     * @return string Text trạng thái
     */
    private function getStatusText($status) {
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'cancelled' => 'Đã hủy'
        ];
        return $statusMap[$status] ?? $status;
    }

    /**
     * Lấy thống kê đặt phòng cho dashboard
     * @return array Thống kê đặt phòng
     */
    public function getBookingStats() {
        try {
            $stats = [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'checked_in' => 0,
                'checked_out' => 0,
                'cancelled' => 0,
                'today' => 0,
                'this_week' => 0,
                'this_month' => 0
            ];

            $sql = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[$row['status']] = (int)$row['count'];
                $stats['total'] += (int)$row['count'];
            }

            $sql = "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['today'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $sql = "SELECT COUNT(*) as count FROM bookings WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['this_week'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $sql = "SELECT COUNT(*) as count FROM bookings WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['this_month'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting booking stats: " . $e->getMessage());
            return [];
        }
    }
} 