<?php
require_once __DIR__ . '/init.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        
        .nav-link {
            font-weight: 500;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .dropdown-item {
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-item.active {
            background-color: #0d6efd;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .btn {
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-outline-primary:hover {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 0.375rem;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
        }
        
        .alert-info {
            background-color: #cff4fc;
            color: #055160;
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        .badge.bg-success {
            background-color: #198754 !important;
        }
        
        .badge.bg-danger {
            background-color: #dc3545 !important;
        }
        
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000;
        }
        
        .badge.bg-info {
            background-color: #0dcaf0 !important;
        }
        
        .badge.bg-secondary {
            background-color: #6c757d !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/rooms.php">Phòng</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/index.php">Quản trị</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">
                                        <i class="bi bi-person me-2"></i>Thông tin cá nhân
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/my-bookings.php">
                                        <i class="bi bi-calendar-check me-2"></i>Lịch sử đặt phòng
                                    </a>
                                </li>
                                <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/index.php">
                                            <i class="bi bi-speedometer2 me-2"></i>Quản trị
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Flash message -->
    <?php if ($flashMessage = getFlashMessage()): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main content -->
    <main> 
    <main class="container py-4"> 