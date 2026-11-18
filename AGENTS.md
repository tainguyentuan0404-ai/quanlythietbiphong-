# Quy tắc bảo vệ và thực hiện luận văn

Ngày lập: 14/10/2026. Áp dụng cho mọi trợ lý AI và mọi thay đổi trong dự án này.

## 1. Nguyên tắc cao nhất

Ưu tiên tính đúng đắn, khả năng khôi phục, bằng chứng và khả năng giải thích khi bảo vệ. Không đánh đổi dữ liệu, nội dung đã duyệt hoặc tính trung thực học thuật để hoàn thành nhanh. Khi thiếu thông tin, ghi rõ điều chưa biết; không tự biến giả định thành yêu cầu đã được chấp thuận.

Bộ quy tắc này giảm rủi ro, không bảo đảm loại bỏ mọi sai sót. Chỉ tuyên bố hoàn thành những gì đã kiểm tra và có bằng chứng.

## 2. Nguồn bối cảnh và trạng thái quyết định

- Đọc `C:\Users\Admin\Downloads\Images\handoverlv.md` khi bắt đầu phiên làm việc mới. Nếu không truy cập được, báo thiếu nguồn và chỉ tiếp tục phần không phụ thuộc nguồn đó.
- File bàn giao là ảnh chụp bối cảnh ngày 14/10/2026, không phải bằng chứng rằng hệ thống đã được xây dựng, công nghệ đã được thầy duyệt hay dịch vụ bên ngoài còn nguyên trạng.
- Phân biệt rõ: **đã chốt**, **định hướng**, **giả định**, **chờ xác nhận**, **đã triển khai**, **đã kiểm chứng**. Không dùng các trạng thái này thay thế nhau.
- Đề tài đã chốt: **Xây dựng hệ thống quản lý mượn trả và bảo trì thiết bị phòng thực hành**.
- Khang phụ trách thiết bị và kiểm kê; Tài phụ trách mượn trả và bảo trì. Phân tích, thiết kế, tích hợp, kiểm thử, triển khai và báo cáo là công việc chung.
- Định hướng: Modular Monolith, Laravel MVC, Service Layer khi cần, Blade, MySQL; Bootstrap hoặc Tailwind chưa được chọn dứt điểm. Repository chỉ thêm khi có nhu cầu thực tế.
- Cần thầy xác nhận: Laravel/PHP/MySQL; mức độ tách API/frontend; sự phù hợp của kiến trúc; cách công nhận nội dung thống kê và môn tích hợp. Không viết các điểm này thành quyết định của thầy khi chưa có bằng chứng.
- Các bảng dữ liệu trong bàn giao là bản nháp. Không triển khai hàng loạt migration chỉ dựa trên danh sách đó.
- Khi có mâu thuẫn giữa yêu cầu, tài liệu, mã nguồn hoặc phản hồi giảng viên, chỉ ra mâu thuẫn và nguồn/ngày tương ứng. Không âm thầm chọn nguồn thuận tiện. Chỉ hỏi phần ảnh hưởng trực tiếp đến quyết định đang làm.
- Khi viện dẫn yêu cầu của thầy, đối chiếu `C:\Users\Admin\Downloads\Instruction of Implementations_LVTN.pdf` nếu có thể đọc. Nếu chỉ có bàn giao, ghi rõ là thông tin trích lại, chưa đối chiếu PDF.
- Hạn 19/10/2026 là hạn báo cáo định kỳ ghi trong bàn giao, không được suy ra là hạn nộp toàn bộ luận văn. Các mốc M1–M7 chưa có ngày không được tự gán thành cam kết.

## 3. Phạm vi và quyền tự chủ

- Ưu tiên phạm vi khả thi cho nhóm hai người trong khoảng bốn tháng: thiết bị, mượn trả, bảo trì, phân quyền, QR/kiểm kê, lịch sử và báo cáo.
- Không tự thêm microservices, ứng dụng di động, AI, IoT, thanh toán, đa trường hoặc tính năng lớn ngoài đề tài. Đề xuất mở rộng phải nêu lợi ích, chi phí thời gian, tác động đến tài liệu/kiểm thử và được người dùng chấp thuận trước khi triển khai.
- Không tự thay framework, cơ sở dữ liệu, kiến trúc hoặc trách nhiệm thành viên đã thống nhất. Chọn phiên bản cụ thể dựa trên môi trường, tài liệu chính thức và tính tương thích.
- Được chủ động đọc, phân tích, soạn bản nháp, sửa lỗi và thay đổi cục bộ có thể phục hồi trong phạm vi đã giao. Không hỏi lại quyền đã được người dùng cấp.
- Trước thao tác phá hủy hoặc khó đảo ngược chưa được cho phép, phải trình bày chính xác đối tượng, ảnh hưởng, bản sao lưu và cách phục hồi để xin quyết định. Không dùng lời hỏi chung chung thay cho chuẩn bị phương án cụ thể.
- Không tự gửi email, nhắn thành viên/giảng viên, nộp bài, công khai tài liệu hay thay đổi dịch vụ bên ngoài khi chưa được giao hành động đó.
- Chỉ dùng skill phù hợp công việc hiện tại; danh sách skill trong bàn giao không yêu cầu nạp tất cả. Không chạy công cụ tấn công hay kiểm thử trên hệ thống ngoài phạm vi được phép.

## 4. Bảo toàn tệp, mã nguồn và công sức của nhóm

- Trước khi sửa, xác định đúng thư mục dự án, đọc quy tắc liên quan, xem trạng thái Git nếu có và nhận diện thay đổi sẵn có. Không ghi đè công việc của người dùng hoặc thành viên khác.
- Không mặc định thư mục đã có ứng dụng Laravel hoặc Git. Kiểm tra trước khi khởi tạo, cài đặt hoặc chạy lệnh.
- Ưu tiên thay đổi nhỏ, đúng nhiệm vụ; không tiện tay đổi tên hàng loạt, format toàn bộ kho, nâng dependency hoặc tái cấu trúc ngoài phạm vi.
- Tuyệt đối không viết comment trong mã nguồn dưới bất kỳ dạng nào, gồm comment HTML, CSS, JavaScript, PHP, Python, JSDoc/PHPDoc và docstring. Dùng tên rõ nghĩa; khi cần giải thích, ghi trong README hoặc tài liệu riêng ngoài mã nguồn.
- Không tự chạy `git reset --hard`, `git clean -fd`, force push, xóa nhánh có công việc chưa lưu, xóa thư mục đệ quy hoặc ghi đè bản báo cáo gốc. Với thao tác đã được phép, vẫn xác minh đúng đích và khả năng phục hồi.
- Trước thay đổi lớn, lưu điểm phục hồi bằng commit phù hợp hoặc bản sao riêng. Không tự commit toàn bộ thư mục; xem diff và chỉ đưa vào các tệp thuộc nhiệm vụ, không chứa bí mật.
- Không đổi lịch sử Git dùng chung. Khi xung đột, giữ ý nghĩa thay đổi của cả hai người và kiểm tra phần tích hợp; không chọn toàn bộ một phía để hết lỗi.
- Khi đã có quy trình nhóm, dùng nhánh và review theo quy trình đó; không tự push/merge/phát hành ngoài quyền đã giao.
- Giữ bản gốc tài liệu, bảng điểm, hình ảnh và số liệu khảo sát. Bản sửa phải có tên/phiên bản rõ ràng; không thay thế bản cuối đã duyệt bằng bản nháp.
- Không coi Git là bản sao lưu đầy đủ: database, tệp tải lên và tài liệu bị ignore cần sao lưu riêng. Kiểm tra có thể khôi phục, không chỉ kiểm tra có tệp backup.
- Trước mỗi lần commit/push, bắt buộc rà soát toàn bộ `prototype/frontend/` và `.gitignore`: lập danh sách file thực tế, tìm tên/nội dung có thể chứa mật khẩu, token, khóa, cấu hình riêng, dữ liệu cá nhân, log, file tạm và output sinh tự động; đối chiếu từng nhóm với `.gitignore`. File nguồn HTML/CSS/JS, README và asset cần cho demo phải được giữ lại; file bảo mật hoặc không cần thiết phải được ignore hoặc loại khỏi danh sách commit.
- Kiểm tra trạng thái Git và danh sách file sẽ commit trước khi push; không coi việc một file đã bị ignore là bằng chứng file đó chưa từng bị track. Nếu phát hiện bí mật đã track, dừng push, gỡ khỏi lịch sử/index theo quy trình an toàn và thay khóa nếu cần; không chỉ thêm mẫu vào `.gitignore` rồi tiếp tục.

## 5. Thiết kế nghiệp vụ trước khi triển khai

- Chốt vai trò, phạm vi quyền, Use Case, quy trình, ERD và từ điển dữ liệu đủ cho phần đang làm trước khi viết nghiệp vụ tương ứng.
- Làm rõ một bản ghi thiết bị đại diện cho một tài sản có mã riêng hay một nhóm tính theo số lượng. Không trộn hai mô hình làm sai tồn kho, QR và lịch sử.
- Tách tình trạng vật lý, khả năng sẵn sàng, vị trí và trạng thái phiếu nghiệp vụ. Không dùng một trường trạng thái duy nhất để diễn tả mọi khía cạnh.
- Định nghĩa bảng chuyển trạng thái cho mượn, trả, báo hỏng, bảo trì và kiểm kê: trạng thái đầu/cuối, tác nhân, điều kiện, tác động dữ liệu và sự kiện lịch sử.
- Chưa chốt thì phải ghi rõ: thời điểm giữ chỗ; khoảng thời gian bị khóa; hủy/hết hạn yêu cầu; trả một phần; trả trễ; mất/hỏng; xử lý báo hỏng khi đang mượn; lịch bảo trì xung đột. Không tự suy ra từ tên màn hình.
- Không đồng nhất phê duyệt với bàn giao thực tế; không đồng nhất tiếp nhận yêu cầu trả với xác nhận đã nhận thiết bị.
- Phân biệt trạng thái ghi nhận đã mất với lệnh xóa tài sản. Không xóa dấu vết để làm số liệu khớp.
- Thống nhất mã định danh, hợp đồng giữa module, quyền sở hữu dữ liệu và nơi thực hiện chuyển trạng thái. Tránh để hai module tự cập nhật cùng quy tắc theo hai cách khác nhau.

## 6. Các bất biến dữ liệu bắt buộc bảo vệ

- Một tài sản riêng lẻ không được có hai lượt bàn giao đang hiệu lực. Nếu quản lý theo số lượng, lượng cấp phát và giữ chỗ theo quy tắc đã chốt không được vượt lượng khả dụng.
- Thiết bị thuộc diện không được sử dụng, như đang bảo trì, mất hoặc ngừng sử dụng, không được bàn giao cho mượn. Điều kiện báo hỏng và đặt lịch tương lai phải theo quyết định nghiệp vụ đã ghi nhận.
- Không trả nhiều hơn đã bàn giao, không xác nhận cùng một lần trả hai lần, không kết thúc bảo trì hai lần gây tăng tồn hoặc đổi trạng thái sai.
- Kiểm tra lại quyền, trạng thái và khả dụng tại thời điểm ghi dữ liệu trên máy chủ. Không dựa vào nút bị ẩn hoặc dữ liệu trang đã tải trước đó.
- Các cập nhật liên quan phải nguyên tử: chi tiết phiếu, trạng thái thiết bị, tồn và lịch sử cùng thành công hoặc cùng thất bại. Dùng transaction và cơ chế khóa/ràng buộc phù hợp, có kiểm thử hai yêu cầu đồng thời.
- Thao tác có thể gửi lại do bấm nhiều lần, timeout hoặc retry phải được chống xử lý trùng bằng cơ chế phù hợp. Không tự retry thao tác ghi khi chưa biết lần trước đã thành công hay chưa.
- Dùng khóa ngoại, unique, kiểm tra số lượng/ngày tháng và ràng buộc phù hợp ở database, kết hợp validation phía máy chủ. Không chỉ dựa vào giao diện.
- Không xóa dây chuyền tài sản, người dùng hoặc danh mục khiến mất lịch sử mượn trả/bảo trì. Chọn vô hiệu hóa, lưu trữ hoặc chính sách xóa đã thiết kế rõ.
- Nhật ký cần ghi tác nhân, thời điểm, đối tượng, hành động và thay đổi cần thiết; không ghi mật khẩu/token. Không cho người dùng thông thường sửa lịch sử để che dấu nghiệp vụ.
- Quét QR chỉ xác định đối tượng; không tự cấp quyền hoặc tự thay đổi dữ liệu. Mọi hành động sau quét vẫn phải xác thực và kiểm tra quyền.
- Kiểm kê ghi nhận chênh lệch để đối soát; không tự ghi đè vị trí, số lượng hoặc tình trạng chuẩn khi chưa qua bước xử lý đã thống nhất.
- Thống nhất múi giờ và quy tắc tính quá hạn ở ứng dụng, database và báo cáo. Xác định mẫu số, trạng thái và khoảng thời gian của mỗi chỉ tiêu để tránh đếm trùng khi JOIN.

## 7. Migration, môi trường và bảo mật

- Xác định môi trường và database đích trước mọi migration, seed, import hoặc script sửa dữ liệu. Không in chuỗi kết nối chứa mật khẩu để chứng minh đích.
- Không chạy `migrate:fresh`, `migrate:refresh`, truncate, drop hoặc seed đè lên dữ liệu cần giữ. Chỉ dùng trên database thử nghiệm được xác định rõ là có thể bỏ hoặc khi đã được cho phép cụ thể.
- Migration đã chia sẻ/áp dụng phải thay đổi bằng migration mới. Với đổi schema có dữ liệu, đánh giá tương thích, backfill, ràng buộc và cách khôi phục trước khi chạy.
- Thử migration trên môi trường phù hợp; có backup và phương án phục hồi cho dữ liệu quan trọng. Không giả định hàm rollback có thể khôi phục dữ liệu đã xóa.
- Tách môi trường phát triển, kiểm thử và demo. Trước test có thao tác reset database, kiểm tra cấu hình hiệu lực để không chạm database làm việc.
- Không commit `.env`, khóa riêng, token, mật khẩu, bản dump chứa dữ liệu cá nhân hoặc tài khoản demo dùng lại mật khẩu thật. Dùng `.env.example` với giá trị mẫu.
- Kiểm soát truy cập ở máy chủ cho từng hành động và từng tài nguyên; kiểm tra cả URL trực tiếp, ID thay đổi và endpoint nội bộ. Quyền quản trị không được cấp ngầm qua form đăng ký.
- Sử dụng cơ chế xác thực, hash mật khẩu, CSRF, validation và truy vấn tham số hóa phù hợp của framework. Kiểm soát trường được phép cập nhật và dữ liệu đầu ra để tránh nâng quyền/XSS.
- Tệp tải lên phải giới hạn loại, kích thước và quyền truy cập; không cho thực thi mã tải lên. Không hiển thị debug hoặc bí mật trong lỗi môi trường công khai.
- Ưu tiên dữ liệu giả lập cho demo. Chỉ thu thập và chia sẻ dữ liệu khảo sát/cá nhân cần thiết theo quyền được cấp.

## 8. Kiểm thử và tiêu chí hoàn thành

- Mỗi chức năng phải có tiêu chí chấp nhận truy vết được về yêu cầu. Kiểm thử mức phù hợp rủi ro; sửa chính tả không cần bộ test phần mềm mới.
- Nghiệp vụ lõi phải được kiểm tra đường thành công, đầu vào sai, thiếu quyền, sai trạng thái, gửi trùng, cạnh tranh đồng thời và rollback khi lỗi giữa chừng.
- Phải có kịch bản xuyên module: thiết bị → yêu cầu → duyệt → bàn giao → trả → báo hỏng/bảo trì → sẵn sàng; cùng các nhánh mất, hỏng, quá hạn và trả một phần nếu thuộc phạm vi đã chốt.
- Kiểm tra QR không vượt quyền; kiểm kê không tự làm sai dữ liệu; báo cáo khớp bộ dữ liệu mẫu có kết quả tính độc lập.
- Khi sửa lỗi, bổ sung kiểm thử hồi quy phù hợp cho lỗi có nguy cơ tái diễn. Không làm test chỉ lặp lại cách cài đặt mà không kiểm chứng hành vi.
- Không gọi là hoàn thành chỉ vì code chạy, build thành công hoặc màn hình đẹp. Cần đáp ứng tiêu chí, kiểm thử liên quan đạt, không có hồi quy đã biết và cập nhật tài liệu bị ảnh hưởng.
- Không báo test đạt khi chưa chạy; không bịa ảnh chụp, log hoặc số lượng test. Ghi rõ lệnh/kịch bản đã chạy, kết quả và phần chưa kiểm tra do thiếu môi trường.
- Không liên tục chạy lại toàn bộ kiểm thử khi không có thay đổi hay bằng chứng lỗi mới. Sau kiểm tra cần thiết, hoàn tất bàn giao.

## 9. Báo cáo và tính trung thực học thuật

- Giao diện phải trình bày như sản phẩm phục vụ người dùng: không đặt tên nhóm, MSSV, phân công phát triển, nhãn luận văn, banner demo hoặc kiến trúc kỹ thuật lên màn hình nghiệp vụ. Ghi dữ liệu mẫu và giới hạn trong README/tài liệu; thao tác chưa kết nối phải phản hồi đúng rằng chưa lưu hoặc chưa xử lý, không báo thành công giả. Đây là yêu cầu giao diện của người dùng ngày 14/10/2026.

- Không bịa khảo sát, phỏng vấn, số liệu, kết quả thực nghiệm, trích dẫn, tài liệu tham khảo hoặc nhận xét của giảng viên. Dữ liệu mô phỏng phải được ghi là mô phỏng.
- Không khẳng định chức năng dự kiến là đã triển khai. Nội dung báo cáo, ERD, UML, workflow, giao diện, database và code phải thống nhất phiên bản.
- Chứng minh tích hợp ít nhất 5 môn bằng nội dung áp dụng và minh chứng thực tế. Danh sách 6 nhóm môn trong bàn giao là đề xuất, chưa thay thế xác nhận của thầy hoặc tên môn chính xác trên bảng điểm.
- Không mặc định phần thống kê được tính thành một môn. Không xác nhận lại số 85 bản ghi/73 tên môn nếu chưa đọc và kiểm tra `Diem.xlsx`.
- Tuần 1 cần tên đề tài, mục đích/mục tiêu, tổng quan và phương pháp; tuần 2 cần sơ đồ tổng quát, mô hình xử lý, ERD và workflow, theo thông tin bàn giao chờ đối chiếu nguồn gốc.
- Báo cáo định kỳ phải ngắn gọn, rõ nghĩa, có tiêu đề, họ tên và MSSV, định dạng thống nhất; tên file không dấu và kèm MSSV. Thiếu tên/MSSV thì để chỗ điền rõ ràng, không tự đoán.
- Phân biệt báo cáo định kỳ với luận văn hoàn chỉnh; không tự thêm kết quả, chương hoặc cam kết ngoài giai đoạn hiện tại.
- Khi xuất PDF/DOCX, mở kiểm tra bản xuất: dấu tiếng Việt, font, ngắt trang, bảng, hình, số trang và nội dung không bị cắt. Không coi xuất file thành công là đã kiểm tra trình bày.
- Chỉ trích nguồn đã đọc và hỗ trợ đúng nhận định. Khi tra phiên bản thư viện, API hoặc hướng dẫn công nghệ, ưu tiên tài liệu chính thức hiện hành.
- Mọi giải pháp chính phải giải thích được: lý do chọn, cách hoạt động, giới hạn và bằng chứng kiểm thử. Không thêm độ phức tạp mà nhóm không đủ khả năng trình bày khi bảo vệ.

## 10. Trello, tích hợp và demo

- Trạng thái Trello trong bàn giao có thể đã cũ. Đọc trạng thái thực tế trước khi sửa và chỉ sửa trong phạm vi người dùng giao.
- Sau mỗi phần việc hoàn thành, bắt buộc đối chiếu board Trello thực tế và tick checklist tương ứng trước khi báo hoàn tất, vì người dùng không tiện tự theo dõi. Chỉ tick mục có nội dung khớp và có bằng chứng đã hoàn thành; không suy diễn một đầu ra gần giống thành đã xong. Nếu không có checklist phù hợp hoặc cách ánh xạ còn mơ hồ, giữ nguyên Trello, ghi rõ trong `note.md` và báo người dùng thay vì tự tạo hoặc tick sai.
- Chỉ chuyển cả thẻ sang `Done` khi toàn bộ đầu ra của thẻ đã hoàn thành và được kiểm tra. Công việc hoàn thành một phần chỉ tick các checklist tương ứng; thẻ vẫn giữ ở `Doing` hoặc trạng thái thực tế.
- M2/M3 được ghi nhận có checklist trùng. Trước khi dọn, so sánh nội dung, trạng thái hoàn thành và xác định checklist cần giữ; không xóa theo tên giống nhau. Nếu vẫn không phân biệt được, hỏi người dùng.
- Dịch vụ lưu chậm: đọc lại trước khi thử ghi lần nữa để tránh tạo thẻ/checklist trùng. Không đánh dấu hoàn tất công việc chỉ vì đã tạo thẻ.
- Chuẩn bị demo có dữ liệu mẫu, tài khoản theo vai trò, quy trình cài đặt và các bước chạy kiểm thử. Có bản ổn định và phương án khôi phục trước buổi bảo vệ.
- Không nâng cấp dependency, đổi schema lớn hoặc đổi kiến trúc sát buổi demo khi không cần thiết. Nếu bắt buộc sửa, nêu tác động, giữ bản ổn định và kiểm tra lại luồng chính.

## 11. Quy trình cho mỗi nhiệm vụ và bàn giao

1. Xác định yêu cầu, nguồn, đầu ra và tiêu chí hoàn thành; nhận diện điểm chưa chốt liên quan.
2. Đọc hiện trạng, quy tắc và thay đổi đang có; xác định tác động đến module, dữ liệu và tài liệu.
3. Chuẩn bị điểm phục hồi tương xứng rủi ro; làm phần độc lập đã được phép trước khi hỏi vấn đề còn thiếu.
4. Thực hiện thay đổi nhỏ, giữ nhất quán nghiệp vụ và hợp đồng giữa các module.
5. Kiểm tra phần thay đổi và rủi ro liên quan; xem diff/bản xuất cuối cùng.
6. Đối chiếu Trello và tick đúng checklist cho phần đã hoàn thành theo quy tắc ở mục 10.
7. Cập nhật `note.md` theo quy tắc nhật ký dưới đây trước khi báo hoàn tất.
8. Bàn giao ngắn gọn: đã làm gì, tệp nào, kiểm tra nào đạt, phần nào chưa xác minh và quyết định nào còn chờ.

### Nhật ký công việc bắt buộc — `note.md`

- Mỗi lần giải quyết xong một vấn đề hoặc nhiệm vụ, phải ghi ngay những việc đã làm, đã thực hiện vào `note.md` tại thư mục gốc dự án, trước khi chuyển sang vấn đề tiếp theo hoặc trả lời hoàn tất. Áp dụng cả cho thay đổi mã nguồn, tài liệu, cấu hình, phân tích và sửa quy tắc.
- Nếu chưa có `note.md`, tạo file; nếu đã có, đọc nội dung liên quan và bổ sung mục mới ở cuối. Không ghi đè, xóa lịch sử hoặc ghi trùng cùng một công việc. Khi cần đính chính, thêm mục đính chính có tham chiếu mục cũ.
- Mỗi mục ghi: thời điểm ghi nhật ký đầy đủ ngày, giờ, phút, giây theo múi giờ Asia/Bangkok (UTC+07:00), với tiêu đề `## DD/MM/YYYY HH:mm:ss (UTC+07:00) — Nội dung`; vấn đề/mục tiêu; các việc thực tế đã thực hiện; tệp hoặc thành phần liên quan; kết quả và kiểm tra đã chạy; phần còn tồn đọng hoặc chưa xác minh nếu có.
- Lấy thời gian thực từ đồng hồ tại lúc ghi, chuyển đúng múi giờ; không tự đoán hoặc dùng lại giờ của mục trước. Các mục lịch sử chỉ có ngày phải giữ nguyên và ghi rõ chưa có giờ/phút/giây được lưu, không lấy thời gian sửa file làm thời gian hoàn thành công việc cũ.
- Chỉ ghi sự kiện có thật. Phân biệt hoàn thành, hoàn thành một phần và bị chặn; không đưa kế hoạch chưa làm vào danh sách đã thực hiện. Nếu công việc phải dừng giữa chừng, ghi tiến độ thực tế và bước còn lại để phiên sau tiếp tục.
- Viết ngắn gọn, rõ ràng bằng tiếng Việt; không ghi mật khẩu, token hoặc dữ liệu cá nhân nhạy cảm. Việc ghi nhật ký không cần tạo thêm một mục nhật ký riêng cho chính nó.
- Kiểm tra mục mới đã được lưu đúng. Nếu không ghi được file, báo rõ lỗi; không tuyên bố đã cập nhật nhật ký.

Sau quyết định quan trọng, ghi nguồn/ngày, lý do và phạm vi ảnh hưởng vào tài liệu dự án. Cập nhật bàn giao khi cần, không xóa dấu vết quyết định cũ và không khẳng định đã cập nhật nếu chưa ghi tệp thành công.

Nếu phát hiện sửa nhầm, mất dữ liệu hoặc kết quả bất thường: dừng thao tác ghi liên quan, giữ log/bằng chứng không chứa bí mật, báo rõ phạm vi đã biết và đề xuất khôi phục từ điểm an toàn. Không tiếp tục ghi để che hoặc sửa mò lỗi.

## 12. CodeGraph

This project has a CodeGraph MCP server (`codegraph_*` tools) configured. Use it for structural questions; use native search for literal text or a file already opened.

| Question | Tool |
|---|---|
| Where is X defined? | `codegraph_search` |
| What calls Y? | `codegraph_callers` |
| What does Y call? | `codegraph_callees` |
| What would break if Z changed? | `codegraph_impact` |
| Show a symbol's signature/source/docstring | `codegraph_node` |
| Get focused task/area context | `codegraph_context` |
| Read several related symbols together | `codegraph_explore` |
| List files under a path | `codegraph_files` |
| Check index health | `codegraph_status` |

- Answer architecture/trace questions directly with `codegraph_context`, then one `codegraph_explore` for relevant source; normally 2–3 calls suffice. Do not delegate exploration or repeat it with grep/read loops.
- Trust AST results; do not re-verify them with grep. Do not grep first to find symbols.
- Do not chain search + node merely to obtain context; use context. Do not loop node over many symbols; use explore.
- The watcher lags writes by about 500 ms; do not immediately re-query after editing.
- If `.codegraph/` is missing and the server reports not initialized, ask: “I notice this project doesn't have CodeGraph initialized. Want me to run `codegraph init -i` to build the index?” Do not initialize it silently.
