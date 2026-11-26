# Chạy thử phần thiết bị và kiểm kê (Backend dùng chung)

Ứng dụng đặt trong thư mục `backend`. Cần PHP 8.3 với `pdo_mysql`, Composer và MySQL 8.0+. Mã dùng Laravel 13, quản lý bảng qua `database/migrations/` (khớp 1-1 với `docs/database-chung/schema.mysql.sql`, gồm 19 bảng, 47 khóa ngoại và 33 ràng buộc `CHECK`). Nhóm Khang và Tài đã chốt dùng chung ứng dụng `backend/` này; định hướng Laravel/MySQL vẫn chờ xác nhận chính thức của giảng viên nếu chưa có.

## Thiết lập trên máy mới (hoặc máy Khang)

1. MySQL phải đang chạy và có database `lvtn_integration` (`CHARACTER SET utf8mb4`).
2. Trong MySQL Workbench, tạo tài khoản kết nối riêng cho ứng dụng (ví dụ `lvtn_equipment@localhost`) và chỉ cấp quyền trên `lvtn_integration` (gồm tạo/đọc/ghi các bảng khi chạy migration và nghiệp vụ).
3. Sao chép `.env.example` thành `.env` nếu chưa có `.env`. Điền `DB_USERNAME` và `DB_PASSWORD`. File `.env` chỉ nằm cục bộ, không đưa lên Git.
4. Từ PowerShell trong `backend`, chạy:
   - `composer install`
   - `php artisan key:generate` (chỉ chạy một lần khi thiết lập mới)
   - `php artisan migrate` (trên database trống sẽ tạo đủ 19 bảng; trên `lvtn_integration` đã nhập từ `schema.mysql.sql` sẽ bỏ qua các bảng đã tồn tại và ghi nhận mốc migration mà không xóa dữ liệu)
   - `php artisan db:seed` (nạp dữ liệu mẫu chống trùng: 1 phòng, 2 tủ, micro 7/3, đầu chuyển 5; chạy lại nhiều lần không nhân đôi)
5. Tạo tài khoản phụ trách đăng nhập ứng dụng:
   - `php artisan equipment:create-staff khang "Khang"`
   - Nhập mật khẩu ít nhất 12 ký tự (hai lần).
6. Khởi chạy: `php artisan serve --host=127.0.0.1` và mở địa chỉ in ra màn hình.

## Phạm vi chức năng hiện có

- **Đăng nhập & phân quyền:** `/login`, `/logout`, kiểm tra tài khoản đang hoạt động (`is_active`) và quyền `staff` khi ghi dữ liệu.
- **F02 — Thiết bị & tồn (`/equipment`):** Danh sách theo từng vị trí, tìm theo mã/tên/QR, lọc theo phòng, thêm loại + tồn đầu trong một transaction, sửa thông tin có chống ghi đè bản cũ, xem chi tiết và lịch sử thay đổi.
- **F08 — Kiểm kê & tra mã/QR (`/inventory-sessions`, `/qr`):** Lập đợt kiểm kê theo từng vị trí (chụp số lượng tại tủ theo từng tình trạng: sẵn sàng và bị khóa chờ xử lý; chặn lập đợt cho tủ trống), nhập số thực đếm (phân biệt `0` và bỏ trống, chống ghi đè bản cũ khi sửa nháp), hiển thị biến động kho phát sinh trong lúc đếm, đóng/khóa đợt (chống bấm đúp, không tự sửa tồn kho), xem lại đợt và tra mã/QR ổn định.
- **Chưa triển khai:** Quét QR bằng camera, nhập thêm/chuyển vị trí sau khi tạo loại, và các màn hình mượn trả/bảo trì của Tài (sẽ ghép chung trong `backend/`). Giao diện demo tuần 3 vẫn nằm trong `prototype/frontend/`.

## Kiểm thử

`tests/EquipmentTest.php` và `tests/InventoryTest.php` dùng database tách riêng `lvtn_test` và tự rollback sau từng ca. Dựng `lvtn_test` bằng `php artisan migrate --force` (với biến môi trường trỏ tới server test riêng), sau đó chạy `php vendor/phpunit/phpunit/phpunit --testdox` trong thư mục `backend`. Không chạy lệnh reset database trên `lvtn_integration`.
