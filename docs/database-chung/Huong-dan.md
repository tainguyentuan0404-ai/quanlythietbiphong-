# Database chung của Khang và Tài

Mốc đầu tiên của lần 4 là để hai phần mềm đọc cùng một mã loại, một vị trí và một số tồn. Khang nhập 7 micro vào tủ A thì Tài thấy 7 chiếc có thể giao tại A. Tài duyệt phiếu mượn 3, Khang vẫn thấy 7. Khi Tài xác nhận đã giao 3, Khang thấy còn 4 tại A. Tủ B của cùng loại giữ nguyên số lượng.

## Dùng bộ SQL

1. [schema.mysql.sql](schema.mysql.sql) tạo 19 bảng trên một database **trống**. Bản SQL được thử trên MySQL 8.4.4. Người phụ trách môi trường tạo database rồi chọn đúng database trước khi nhập. File chỉ tạo bảng, không chứa lệnh xóa hoặc chọn sẵn database. Nếu đã có bảng Laravel hoặc dữ liệu làm việc, đối chiếu schema trước khi viết migration bổ sung.
2. [du-lieu-mau.sql](du-lieu-mau.sql) dùng một lần trên database thử vừa tạo. Dữ liệu giả gồm một phòng, hai tủ, micro, đầu chuyển và các biến động nhập đầu. Tài khoản ghi nhận nhập mẫu bị khóa. Tài khoản đăng nhập thật sẽ do ứng dụng cấp. Trong MySQL client dùng lệnh dưới đây, thay đường dẫn bằng thư mục trên máy đang dùng.

```sql
CREATE DATABASE lvtn_integration CHARACTER SET utf8mb4;
USE lvtn_integration;
SELECT DATABASE();
SOURCE C:/duong-dan/database-chung/schema.mysql.sql
SOURCE C:/duong-dan/database-chung/du-lieu-mau.sql
```

3. **Khi phát triển riêng**, mỗi máy có thể dùng cùng hai file để có cấu trúc và bộ dữ liệu khởi đầu giống nhau. **Khi kiểm tra tích hợp**, hai ứng dụng phải kết nối đến cùng máy chủ MySQL và cùng database `lvtn_integration` để thấy các cập nhật của nhau. Chọn một môi trường nhóm có quyền sử dụng, cấp tài khoản kết nối riêng, lưu thông tin kết nối trong cấu hình cục bộ. Người phụ trách nhập SQL một lần. Người còn lại chỉ kết nối. Tài khoản chạy test dùng database tách riêng.
4. Cả hai chạy truy vấn dưới đây trên môi trường tích hợp. Ban đầu kết quả có micro **7 ở A, 3 ở B**, đầu chuyển **5 ở A**. Giữ cùng mã thiết bị và mã vị trí trong ví dụ của cả nhóm, lấy ID từ database khi tạo dòng phiếu.

```sql
SELECT e.code, e.name, r.code AS room_code, l.code AS location_code,
       b.physical_condition, b.use_state, b.quantity
FROM stock_balances AS b
JOIN equipment_types AS e ON e.id = b.equipment_type_id
JOIN locations AS l ON l.id = b.location_id
JOIN rooms AS r ON r.id = l.room_id
ORDER BY e.code, l.code;
```

## Bảng nào phục vụ chức năng nào?

| Chức năng hiện tại | Bảng chính | Ai dùng và khi nào dữ liệu thay đổi |
|---|---|---|
| F01. Đăng nhập và quản lý tài khoản | `roles`, `users` | Cả hai dùng chung ID người thao tác. Người mượn tự xác nhận mục đích trên phiếu. |
| F02. Quản lý thiết bị và tồn | `rooms`, `locations`, `equipment_types`, `stock_balances`, `stock_movements` | Khang quản lý danh mục và số lượng đầu. Tài đọc cùng mã loại, vị trí và lượng tốt/sẵn sàng. |
| F03. Gửi và theo dõi yêu cầu | `borrow_requests`, `borrow_request_items` | Tài ghi người mượn, mục đích, thời điểm xác nhận, thời hạn cùng từng loại, vị trí và lượng cần mượn. |
| F04. Xét duyệt | `borrow_requests` | Tài ghi quyết định, người duyệt, thời điểm và lý do từ chối. Chỉ cập nhật phiếu. |
| F05. Bàn giao | `handovers`, `handover_items`, bảng tồn và biến động | Một phiếu giao đủ một lần. Chỉ trừ ở vị trí thực giao. Khang đọc lại được số mới. |
| F06. Tiếp nhận và xác nhận trả | `return_records`, `return_items`, `loss_cases`, bảng tồn và biến động | Tài ghi một lần xác nhận cho toàn phiếu. Phần tốt và hỏng thực nhận về hai nhóm tồn riêng. Phần thiếu mở vụ việc. |
| F07. Báo hỏng và bảo trì | `damage_reports`, `maintenance_jobs`, bảng tồn và biến động | Cho báo hỏng khi đang mượn. Bắt đầu sửa phần đã nhận lại. Nghiệm thu đạt bao nhiêu thì trả về sẵn sàng bấy nhiêu. |
| F08. QR và kiểm kê | `equipment_types.qr_code`, `inventory_sessions`, `inventory_items` | Khang tra loại bằng QR, chụp số hồ sơ theo vị trí, lưu số đếm thực và tính chênh lệch. Đóng đợt giữ nguyên tồn. |
| F09. Lịch sử và báo cáo | `activity_logs`, `stock_movements` và các bảng phiếu | Hai phần ghi người, thời điểm UTC, hành động và đối tượng. Khi tổng hợp lượt mượn, đếm phiếu đã giao. Khi tổng hợp số chiếc, cộng dòng giao. |

## Quy tắc tích hợp

- Mỗi loại có một mã/QR. Mỗi số dư là một tổ hợp loại + vị trí + tình trạng vật lý + trạng thái sử dụng. Cột `category` tùy chọn được thêm để lưu trường “Nhóm” hiện có ở form Khang. Cả hai tra loại bằng ID/mã thay cho tên hiển thị.
- Mốc tích hợp đầu tiên chỉ cần danh mục, số tồn, phiếu mượn, quyết định duyệt và bàn giao. Các bảng trả, sự cố và kiểm kê đã có cấu trúc để hai người nối sau. Tạo bảng chưa cung cấp xử lý nghiệp vụ.
- Backend phải kiểm tra đủ **mọi dòng** của phiếu tại thời điểm giao, khóa các dòng tồn theo thứ tự nhất quán và ghi phiếu, số dư, biến động, lịch sử trong cùng transaction. Một công việc chỉ có một nơi cập nhật tồn. Dùng cùng khóa thao tác khi gửi lại cùng một biến động.
- Khi trả, tổng tốt + hỏng + thiếu phải bằng số đã giao của đúng dòng, đúng phiếu. Khi bảo trì, đối chiếu đúng loại, nguồn và số hỏng thực nhận trước khi cho sửa. Các điều kiện liên bảng, phân quyền, chống gửi trùng toàn giao dịch và kiểm thử hai người thao tác đồng thời thuộc phần backend.
- Kiểm kê trong schema theo **một vị trí/đợt**, chi tiết theo **loại và tình trạng** như ERD. Demo cũ cho chọn cả phòng và lấy số sẵn sàng làm mốc. Nhóm cần thống nhất phạm vi đếm thực tế trước khi nối màn hình kiểm kê. Mẫu số cho tồn theo hồ sơ và các số đang sửa ngoài kho cũng cần thống nhất trước khi lập báo cáo.

## Bằng chứng kiểm tra

Trên MySQL 8.4.4 tạm, đã tạo đủ **19 bảng, 47 khóa ngoại**, nhập dữ liệu mẫu và kiểm tra ba biến động nhập có ba dòng nhật ký. Trong giao dịch SQL thử, duyệt 3 micro tại A giữ số tồn A/B là 7/3. Sau khi ghi bàn giao và cập nhật tồn cùng giao dịch, A/B là 4/3. MySQL từ chối bốn trường hợp thử gồm QR trùng, vị trí không tồn tại, lượng yêu cầu bằng 0 và phiếu bàn giao thứ hai của cùng yêu cầu. Bản kiểm tra này chạy trên một máy. Môi trường tích hợp hai máy và các giao diện backend vẫn cần được cấu hình và kiểm thử.

Nguồn nội bộ: [9 chức năng đã cập nhật](../tuan-2/lam-lai/04-danh-sach-chuc-nang.md), [mô hình quan hệ](../tuan-2/lam-lai/02-mo-hinh-du-lieu.md), [từ điển trường](../tuan-2/lam-lai/03-tu-dien-truong.md), [đối chiếu bản Word L3](../../Report3-2.md).
