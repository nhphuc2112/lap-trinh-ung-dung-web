# lap-trinh-ung-dung-web
# Giới thiệu
- Môn: Lập trình ứng dụng web
- Đề tài: Quản lý khách sạn với PHP
- Mã số sinh viên: 424000060
- Họ và tên: Nguyễn Hữu Phúc
# Cài đặt với xampp:
```sh
git clone https://github.com/nhphuc2112/lap-trinh-ung-dung-web
```
- Cài đặt xampp: ``` https://www.apachefriends.org/download.html ```
- Copy vào C://xampp/htdocs
- Cài đặt SQL vào PHPMyAdmin
- Mở port 80:443 và 3306
- Start với xampp
- Browse: localhost
# Cài đặt với CPanel
- Trỏ domain với hosting
- File Manager -> public -> Upload lap-trinh-ung-dung-web-main.zip
- Cài đặt SQL Account -> PHPMyAdmin -> Upload file.sql
- Config database ở /config/Database.php
- Truy cập tên miền
# Cài đặt với VPS
- Trỏ domain với VPS
### Cách đơn giản nhất -> Sử dụng xampp
- Cài đặt xampp: ``` https://www.apachefriends.org/download.html ```
- Copy vào C://xampp/htdocs
- Cài đặt SQL vào PHPMyAdmin
- Mở port 80:443 và 3306
- Start với xampp
- Browse: localhost
### Cách tùy chỉnh sử dụng IIS
- Bật IIS trong Control Panel
- Cài đặt PHP: ```https://windows.php.net/download/```
- Upload và chạy website ở C://inetpub/wwwroot
