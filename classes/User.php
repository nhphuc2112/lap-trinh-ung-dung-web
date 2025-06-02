<?php
class User {
    private $db;
    private $id;
    private $email;
    private $name;
    private $is_admin;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($email, $password, $name) {
        try {
            // email checking
            if ($this->getUserByEmail($email)) {
                return false;
            }

            // ma khoa pass
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // create user
            $sql = "INSERT INTO users (email, password, name, is_admin) VALUES (?, ?, ?, 0)";
            return $this->db->query($sql, [$email, $hashedPassword, $name])->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error registering user: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password) {
        try {
            $user = $this->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                // cap nhat session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];

                // cap nhat thong tin object
                $this->id = $user['id'];
                $this->email = $user['email'];
                $this->name = $user['name'];
                $this->is_admin = (bool)$user['is_admin'];

                // khong tra ve mat khau
                unset($user['password']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error during login: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['name', 'email'];
            $updates = [];
            $values = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "$key = :$key";
                    $values[$key] = $value;
                }
            }

            if (empty($updates)) {
                return false;
            }

            $values['id'] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($values);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $user = $this->getUserById($userId);

            if ($user && password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = :password WHERE id = :id";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute(['password' => $hashedPassword, 'id' => $userId]);

                return $stmt->rowCount() > 0;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById($userId) {
        try {
            $sql = "SELECT id, email, name, is_admin FROM users WHERE id = ?";
            return $this->db->fetch($sql, [$userId]);
        } catch (PDOException $e) {
            error_log("Error getting user: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            return $this->db->fetch($sql, [$email]);
        } catch (PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers() {
        try {
            $sql = "SELECT id, email, name, is_admin, created_at FROM users ORDER BY id DESC";
            return $this->db->fetchAll($sql);
        } catch (PDOException $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    public function deleteUser($userId) {
        try {
            $user = $this->getUserById($userId);
            if ($user && $user['is_admin']) {
                return false;
            }

            $sql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
            $result = $this->db->fetch($sql, [$userId]);
            
            if ($result && $result['count'] > 0) {
                return false; 
            }

            $sql = "DELETE FROM users WHERE id = ?";
            return $this->db->query($sql, [$userId])->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    public function toggleAdmin($userId) {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return false;
            }

            $sql = "UPDATE users SET is_admin = ? WHERE id = ?";
            return $this->db->query($sql, [!$user['is_admin'], $userId])->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error toggling admin status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo người dùng mới
     * @param array $data Thông tin người dùng
     * @return int|false ID của người dùng mới hoặc false nếu thất bại
     */
    public function createUser($data) {
        try {
            // email checking
            $sql = "SELECT id FROM users WHERE email = ?";
            if ($this->db->fetch($sql, [$data['email']])) {
                return false;
            }

            // create user
            $sql = "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)";
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $name = $data['name'] ?? $data['name'] ?? ''; // Handle both name and name
            $isAdmin = ($data['role'] ?? 'user') === 'admin' ? 1 : 0;
            
            if ($this->db->query($sql, [
                $name,
                $data['email'],
                $password,
                $isAdmin
            ])) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thông tin người dùng
     * @param int $userId ID người dùng
     * @param array $data Thông tin cần cập nhật
     * @return bool True nếu cập nhật thành công, false nếu thất bại
     */
    public function updateUser($userId, $data) {
        try {
            // email check 
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $existingUser = $this->db->fetch($sql, [$data['email'], $userId]);
            
            if ($existingUser) {
                return false;
            }

            // update profile
            $updates = [];
            $values = [];

            $name = $data['name'] ?? $data['name'] ?? '';
            $updates[] = "name = ?";
            $values[] = $name;

            $updates[] = "email = ?";
            $values[] = $data['email'];
            if (!empty($data['password'])) {
                $updates[] = "password = ?";
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if (isset($data['role'])) {
                $updates[] = "is_admin = ?";
                $values[] = ($data['role'] === 'admin') ? 1 : 0;
            }

            $values[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            return $this->db->query($sql, $values)->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tổng số người dùng
     * @return int Tổng số người dùng
     */
    public function getTotalUsers() {
        try {
            $sql = "SELECT COUNT(*) as total FROM users";
            $result = $this->db->fetch($sql);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error getting total users: " . $e->getMessage());
            return 0;
        }
    }
} 