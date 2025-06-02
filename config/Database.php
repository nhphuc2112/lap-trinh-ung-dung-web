<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $host = 'localhost';
            $dbname = 'hotel_management2';
            $username = 'root';
            $password = '';
            
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Thực thi câu truy vấn và trả về số dòng bị ảnh hưởng
     * 
     * @param string $sql Câu truy vấn SQL
     * @param array $params Các tham số cho câu truy vấn
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Query failed");
        }
    }
    
    /**
     * Lấy một dòng dữ liệu
     * 
     * @param string $sql Câu truy vấn SQL
     * @param array $params Các tham số cho câu truy vấn
     * @return array|null
     */
    public function fetch($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Fetch failed: " . $e->getMessage());
            return null;
        }
    }
    public function getConnection() {
        return $this->pdo;
    }
    /**
     * Lấy nhiều dòng dữ liệu
     * 
     * @param string $sql Câu truy vấn SQL
     * @param array $params Các tham số cho câu truy vấn
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("FetchAll failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy giá trị của một cột
     * 
     * @param string $sql Câu truy vấn SQL
     * @param array $params Các tham số cho câu truy vấn
     * @return mixed
     */
    public function fetchColumn($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("FetchColumn failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Lấy ID của dòng vừa được thêm vào
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Bắt đầu transaction
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollBack() {
        $this->pdo->rollBack();
    }
} 