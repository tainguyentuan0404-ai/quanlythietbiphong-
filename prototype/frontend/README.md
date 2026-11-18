# LabDevice Hub — bản thử giao diện

Mở `index.html` hoặc chạy máy chủ tĩnh trong thư mục này. Bốn trang gồm tổng quan, thiết bị/kiểm kê, mượn trả và báo hỏng/bảo trì. Bootstrap CSS, Icons và Bundle JS hiện tải từ jsDelivr nên cần kết nối mạng khi xem.

## Thử Unslop ngày 14/11/2026

- **Sản phẩm:** Giao diện theo dõi thiết bị, mượn trả và bảo trì cho phòng thực hành.
- **Tính cách:** Điềm tĩnh, rõ ràng, chính xác.
- **Bối cảnh:** Bàn trực phòng thực hành, danh sách thiết bị và phiếu xử lý.
- **Điểm nhấn:** Số liệu tồn/trạng thái và biểu đồ phân bố theo phòng.
- **Dấu hiệu riêng:** Vạch xanh ở mục nghiệp vụ đang xem.

Trước khi sửa, sidebar xanh đen chiếm nhiều chú ý, biểu tượng màu lặp ở bốn thẻ số liệu, nhãn điều hướng viết hoa và nhiều khoảng đệm không cùng nhịp. Bản thử chuyển sidebar sang nền sáng, dùng nền và viền trung tính, nhấn màu xanh ở hành động chính, làm số liệu dễ đọc, thống nhất khoảng cách và các nút. Màu xanh lá/vàng/đỏ ở biểu đồ vẫn biểu thị các trạng thái thiết bị khác nhau. Không thêm ảnh trang trí cho màn hình quản trị.

Ở bản thử ngày 14/11, số liệu, tên người và phiếu trong HTML là **dữ liệu mẫu**. Các biểu mẫu và nút nghiệp vụ chưa kết nối cơ sở dữ liệu. Màn hình thiết bị khi đó còn theo mã riêng lẻ, trong khi mô hình nghiệp vụ đã chọn quản lý **loại thiết bị theo số lượng**. Phần thiết bị được chỉnh lại ngày 18/11 như mô tả bên dưới.

Khả năng phục hồi: nhánh `codex/unslop-demo` chứa bản thử; giao diện trước khi chỉnh ở commit `2b25ebb` trên `main`. Chưa push nhánh này.

Ý tưởng và quy trình chỉnh giao diện tham khảo **Unslop** của Damir Sokolovsky ([@heydamir](https://x.com/heydamir)), [mã nguồn](https://github.com/heydamir/unslop), giấy phép CC BY 4.0.

## Bắt đầu giao diện theo phân công ngày 18/11/2026

Sau khi nhóm chốt [bố cục chung trên Figma](https://www.figma.com/design/fumnz2EWYk3dsrV3N8lkyE), phần Khang có ba màn hình: `equipment.html` (danh sách loại và số lượng), `equipment-detail.html?id=TB-L001` (chi tiết một loại), `inventory.html` (nhập số đếm thử và xem chênh lệch). Dữ liệu minh họa nằm trong HTML và `assets/js/khang.js`; CSS riêng ở `assets/css/khang.css`. Bốn loại mẫu có tổng 38 đơn vị: 29 sẵn sàng, 6 đang mượn, 3 đang bảo trì.

Lúc khởi tạo ba màn hình, tìm kiếm, lọc phòng, mở chi tiết và tính chênh lệch hoạt động trên trình duyệt. Các thao tác còn lại được bổ sung trong bản cập nhật bên dưới. QR theo mô hình này nhận diện **loại thiết bị**, không nhận diện từng chiếc. Phần mượn trả/bảo trì của Tài vẫn ở trang cũ để ghép và đồng bộ nghiệp vụ ở bước tiếp theo.

Mã nguồn bản này nằm ở nhánh `codex/unslop-demo`; bản `equipment.html` trước khi thay đổi được lưu tạm ở `tmp/equipment-before-khang-18-11.html` và còn có thể lấy lại từ Git. Chưa push.

## Bản demo tương tác cục bộ ngày 18/11/2026

Ba màn hình của Khang dùng chung bốn loại thiết bị giả lập. Mỗi loại có mã `TB-L...`, tên, nhóm, phòng, vị trí, tổng số lượng, đang mượn và đang bảo trì; số sẵn sàng được tính từ các số này. Dữ liệu lưu bằng `localStorage` trên trình duyệt hiện tại, nên thêm/sửa và số đếm còn sau khi tải lại trang. Trình duyệt khác hoặc thiết bị khác không chia sẻ dữ liệu này.

- **Danh sách:** tìm theo mã/tên, lọc phòng, đặt lại bộ lọc, thêm loại thiết bị. Biểu mẫu chặn số âm và trường hợp đang mượn + bảo trì vượt tổng.
- **Chi tiết:** xem một loại, sửa thông tin, xem lịch sử thêm/sửa và kết quả kiểm kê liên quan.
- **Kiểm kê:** tạo đợt theo phòng, nhập số thực đếm cho tất cả loại trong phòng, xem chênh lệch, lưu bản đối soát, mở lại đợt cũ. Bản nháp và kết quả hoàn tất được lưu cục bộ; hoàn tất hai lần bị chặn. Kết quả kiểm kê **không tự sửa số tồn**.
- **Tra mã:** nhập mã loại để mở trang chi tiết. Bản này chưa tạo ảnh QR và chưa quét bằng camera.

Đây là nguyên mẫu frontend, chưa có tài khoản, phân quyền máy chủ, API hoặc database. Số đang mượn và bảo trì được nhập tay trong dữ liệu mẫu; các trang của Tài chưa đồng bộ với bộ dữ liệu cục bộ này. Không dùng kết quả demo để kết luận nghiệp vụ thật đã được triển khai.

## Đổi thiết bị mẫu ngày 18/11/2026

Các loại mặc định của phần Khang nay là micro không dây cầm tay, đầu chuyển USB-C sang USB-A, bút trình chiếu không dây và cáp HDMI 2 m. Tên thiết bị trong phiếu mượn, bảo trì và hoạt động gần đây được đổi theo cùng nhóm vật dụng thường mượn khi dạy học. Dòng giải thích công thức tồn ở cuối danh sách và trang chi tiết đã bỏ theo yêu cầu.

Khi mở phiên đã có dữ liệu cũ, script chỉ đổi những bản ghi còn giữ đúng tên mẫu cũ; số lượng và bản ghi người dùng tự sửa vẫn được giữ. Bản ghi thử nghiệm `TB-L005` do lượt kiểm thử giao diện tạo được đổi thành micro cài áo không dây. Số liệu tổng trên trình duyệt đã dùng thử có thể khác bốn loại mặc định trong HTML.

## Đối chiếu bối cảnh STU ngày 18/11/2026

Đã đổi bộ mẫu mặc định thành micro không dây cầm tay, đầu chuyển USB-C sang USB-A, chuột USB có dây và máy chiếu dự phòng. Tên phòng dùng trong demo lấy theo trang cơ sở vật chất của STU; phòng máy CNTT dùng tên chung khi nguồn không có mã phòng. Màn hình mượn trả/bảo trì dùng các ví dụ quen thuộc như cáp HDMI, bàn phím, loa và máy chiếu. Tổng quan đồng bộ số liệu ban đầu 38 đơn vị với danh mục phần Khang.

Nguồn, giới hạn xác minh và phần dữ liệu hoàn toàn giả lập được ghi trong [đối chiếu dữ liệu demo STU](../../docs/doi-chieu-du-lieu-demo-stu.md). Thay đổi dữ liệu mẫu cũ trong `localStorage` chỉ áp dụng khi trường vẫn bằng giá trị mặc định trước đó, nhằm giữ thông tin người dùng đã sửa.

## Điều hướng thiết bị và kiểm kê ngày 18/11/2026

Thanh bên của sáu trang dùng chung nhóm “Thiết bị & Kiểm kê”. Bấm tên nhóm để mở hoặc thu hai mục con “Thiết bị” và “Kiểm kê”. Nhóm tự mở khi đang ở một trong hai trang này hoặc trang chi tiết thiết bị; khi chuyển sang Tổng quan, Mượn trả hoặc Bảo trì, nhóm tự thu lại. Giao diện dùng thẻ HTML `details`, nên thao tác được bằng chuột và bàn phím.

Theo phản hồi sau đó, sáu trang đã được đối chiếu để có cùng thứ tự và nhãn menu. Đã bỏ mũi tên ở mục “Thiết bị & Kiểm kê”; bấm trực tiếp vào dòng này vẫn mở hoặc thu cây.

## Đồng bộ trình bày ngày 18/11/2026

Sáu trang hiện dùng cùng nhãn người dùng “Nhân viên phòng thực hành”, cùng căn lề nội dung và cỡ tiêu đề/nút chính. Các liên kết nội bộ mang phiên bản giao diện để tránh trình duyệt mở lại HTML cũ khi chuyển trang trong bản demo tĩnh. Nếu đang mở một tab từ trước lần cập nhật này, tải lại trang một lần trước khi thử menu. Dữ liệu phiếu mượn và bảo trì vẫn là dữ liệu minh họa riêng, chưa liên kết với danh mục thiết bị cục bộ.
