# Đối chiếu dữ liệu mẫu giao diện với bối cảnh STU

Ngày đối chiếu: 18/11/2026. Phạm vi: bản mẫu frontend trong `prototype/frontend/`.

| Nội dung | Điều nguồn STU xác nhận | Cách dùng trong demo |
|---|---|---|
| Phòng máy CNTT | [Trang Khoa Công nghệ thông tin STU](https://stu.edu.vn/index.php/khoa/khoa-cong-nghe-thong-tin) nêu khoa quản lý 11 phòng máy, mỗi phòng khoảng 30 máy tính. | Gọi chung “Phòng máy CNTT”; chuột USB là ví dụ phụ kiện phù hợp bối cảnh phòng máy. Không gán mã phòng vì nguồn không nêu. |
| Không gian thực hành Điện–Điện tử | [Trang cơ sở vật chất Khoa Điện–Điện tử STU](https://stu.edu.vn/khoa/khoa-dien-dien-tu/co-so-vat-chat) liệt kê PTN Kỹ thuật điện tử B203, Xưởng thực tập điện tử B201 và PTN Viễn thông A307. | Dùng đúng tên phòng để minh họa; việc gán từng loại thiết bị, vị trí tủ và số lượng cho từng phòng là giả lập. |
| Âm thanh, trình chiếu | [Tài liệu trả lời đối thoại STU năm học 2017–2018](https://oldversion.stu.edu.vn/uploads1511/redactor/c7cb2045baf6ccd3c83f156246ed73a9.pdf) đề cập micro, loa, máy chiếu cố định tại nhiều phòng học và máy chiếu dự phòng. | Minh họa micro, loa, máy chiếu dự phòng. Máy chiếu lắp cố định chỉ xuất hiện trong kịch bản bảo trì, không đưa vào danh mục cho mượn. Nguồn lịch sử không chứng minh quy trình hay tồn kho hiện tại. |

Các tên “đầu chuyển USB-C sang USB-A”, “cáp HDMI 2 m”, “chuột USB có dây”, “bàn phím USB có dây” là **vật dụng mẫu hợp lý cho giảng dạy/thực hành**, chưa tìm được danh mục tài sản STU công khai xác nhận trường đang quản lý đúng các loại này. Tên người, mã thiết bị, phiếu, số lượng, ngày, trạng thái, vị trí tủ, gán phòng và chi phí bảo trì trong demo đều là **dữ liệu giả lập**, không phải số liệu hoặc quy trình chính thức của STU.

Các trang mượn trả và bảo trì vẫn dùng bộ mẫu HTML riêng, chưa đồng bộ với `localStorage` của phần thiết bị/kiểm kê. Trang tổng quan và ba trang phần Khang cùng hiển thị bộ số liệu mẫu ban đầu 38 đơn vị; khi người dùng sửa dữ liệu trên trình duyệt, chỉ ba trang phần Khang đổi theo. Không dùng nguyên mẫu để kết luận chức năng backend đã triển khai.
