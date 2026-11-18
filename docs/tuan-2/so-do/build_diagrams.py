"""Generate the week-2 editable HTML diagrams from a small declarative model."""

from html import escape
from pathlib import Path

OUT = Path(__file__).resolve().parent
PAPER = "#fafafa"
INK = "#111111"
MUTED = "#444444"
SUPERSEDED_ERD = {"erd-muon-tra", "erd-bao-tri-kiem-ke", "erd-kiem-ke"}


def rect(x, y, w, h, title, sub="", kind="normal"):
    stroke = INK if kind != "quiet" else "#777777"
    fill = "#eeeeee" if kind == "focal" else "#ffffff"
    title_lines = title.split("|")
    t = "".join(
        f'<tspan x="{x + 16}" dy="{0 if i == 0 else 18}">{escape(line)}</tspan>'
        for i, line in enumerate(title_lines)
    )
    subtext = f'<text x="{x + 16}" y="{y + h - 13}" class="sub">{escape(sub)}</text>' if sub else ""
    return (
        f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="{fill}" stroke="{stroke}"/>'
        f'<text x="{x + 16}" y="{y + 29}" class="label">{t}</text>{subtext}'
    )


def flow_node(x, y, w, h, title, kind="process"):
    """Standard flowchart symbols: terminal, process, and decision."""
    if kind == "decision":
        shape = f'<polygon points="{x+w/2},{y} {x+w},{y+h/2} {x+w/2},{y+h} {x},{y+h/2}" fill="#ffffff" stroke="{INK}" stroke-width="2"/>'
    else:
        radius = h / 2 if kind == "terminal" else 7
        fill = "#eeeeee" if kind == "terminal" else "#ffffff"
        shape = f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{radius}" fill="{fill}" stroke="{INK}" stroke-width="2"/>'
    parts = title.split("|")
    text_y = y + h / 2 - (len(parts) - 1) * 9 + 5
    labels = "".join(
        f'<tspan x="{x+w/2}" dy="{0 if i == 0 else 18}">{escape(part)}</tspan>'
        for i, part in enumerate(parts)
    )
    return f'{shape}<text x="{x+w/2}" y="{text_y}" text-anchor="middle" class="flow-label">{labels}</text>'


def entity(x, y, title, fields, w=240):
    """A table-like ER entity with explicit PK/FK rows."""
    h = 52 + 22 * len(fields)
    rows = "".join(
        f'<text x="{x + 12}" y="{y + 68 + i * 22}" class="er-row">{escape(field)}</text>'
        for i, field in enumerate(fields)
    )
    return (
        f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="#ffffff" stroke="{INK}" stroke-width="1.5"/>'
        f'<rect x="{x}" y="{y}" width="{w}" height="44" fill="#eeeeee" stroke="{INK}" stroke-width="1.5"/>'
        f'<text x="{x + 12}" y="{y + 28}" class="er-head">{escape(title)}</text>{rows}'
    )


def line(path, label="", lx=None, ly=None, dashed=False, arrow=True, cardinality=None):
    dash = ' stroke-dasharray="5,4"' if dashed else ""
    marker = ' marker-end="url(#arrow)"' if arrow else ""
    if cardinality:
        start, end = cardinality.split("-")
        marker = f' marker-start="url(#er-{start})" marker-end="url(#er-{end})"'
    mark = f'<path d="{path}" fill="none" stroke="{MUTED}" stroke-width="1.5"{dash}{marker}/>'
    if label:
        width = max(34, len(label) * 7 + 14)
        mark += (
            f'<rect x="{lx - width / 2:.1f}" y="{ly - 13}" width="{width}" height="19" fill="{PAPER}"/>'
            f'<text x="{lx}" y="{ly}" text-anchor="middle" class="edge">{escape(label)}</text>'
        )
    return mark


def figure(slug, heading, description, edges, nodes, footer, width=960, height=600):
    if slug in SUPERSEDED_ERD:
        return
    svg = f'''<svg viewBox="0 0 {width} {height}" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="{slug}-title {slug}-desc">
<title id="{slug}-title">{escape(heading)}</title>
<desc id="{slug}-desc">{escape(description)}</desc>
<defs><marker id="arrow" markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto"><polygon points="0 0, 9 3.5, 0 7" fill="{MUTED}"/></marker>
<marker id="er-one" markerWidth="14" markerHeight="14" refX="2" refY="7" orient="auto-start-reverse" markerUnits="userSpaceOnUse"><path d="M 2 2 V 12" fill="none" stroke="{MUTED}" stroke-width="1.5"/></marker>
<marker id="er-many" markerWidth="14" markerHeight="14" refX="12" refY="7" orient="auto-start-reverse" markerUnits="userSpaceOnUse"><path d="M 2 7 L 12 2 M 2 7 H 12 M 2 7 L 12 12" fill="none" stroke="{MUTED}" stroke-width="1.5"/></marker></defs>
<rect width="{width}" height="{height}" fill="{PAPER}"/>
{''.join(edges)}
{''.join(nodes)}
<text x="40" y="{height-30}" class="foot">{escape(footer)}</text>
</svg>'''
    html = f'''<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{escape(heading)}</title><style>
*{{box-sizing:border-box}}body{{background:{PAPER};color:{INK};margin:0;padding:32px;font-family:Arial,'Segoe UI',sans-serif}}
main{{max-width:{width+40}px;margin:auto}}h1{{font-family:Georgia,serif;font-size:28px;font-weight:400;margin:0 0 16px}}
.diagram-container{{width:100%;overflow-x:auto}}svg{{width:100%;min-width:{width}px;display:block}}
.label{{font:600 15px Arial,'Segoe UI',sans-serif;fill:{INK}}}.sub{{font:11px Arial,'Segoe UI',sans-serif;fill:{MUTED}}}
.edge{{font:12px Arial,'Segoe UI',sans-serif;fill:{INK}}}.foot{{font:11px Arial,'Segoe UI',sans-serif;fill:{MUTED}}}
.er-head{{font:600 17px Arial,'Segoe UI',sans-serif;fill:{INK}}}.er-row{{font:13px Arial,'Segoe UI',sans-serif;fill:{INK}}}
.flow-label{{font:600 15px Arial,'Segoe UI',sans-serif;fill:{INK}}}
@media print{{.diagram-container{{overflow-x:visible}}svg{{min-width:0}}}}
</style></head><body><main><h1>{escape(heading)}</h1><div class="diagram-container">{svg}</div></main></body></html>'''
    (OUT / f"{slug}.html").write_text(html, encoding="utf-8")


figure(
    "tong-quat", "Sơ đồ tổng quát hệ thống",
    "Các tác nhân dùng hệ thống web; phân hệ thiết bị, mượn trả, bảo trì và kiểm kê cùng lưu dữ liệu và lịch sử.",
    [
        line("M 216,220 H 304"), line("M 216,340 H 304"),
        line("M 504,220 H 568"), line("M 504,340 H 568"),
        line("M 768,220 H 820 V 280 H 804"),
        line("M 768,340 H 820 V 300 H 804"),
        line("M 404,252 V 308"),
        line("M 668,252 V 308"),
    ],
    [
        rect(40,188,176,64,"Sinh viên /|giảng viên","yêu cầu, tra cứu"),
        rect(40,308,176,64,"Người phụ trách /|kỹ thuật viên","xử lý, kiểm kê"),
        rect(304,188,200,64,"Web + phân quyền","kiểm tra từng thao tác","focal"),
        rect(304,308,200,64,"Thiết bị / vị trí","tài sản, QR, lịch sử"),
        rect(568,188,200,64,"Mượn / trả","duyệt, giao, nhận lại"),
        rect(568,308,200,64,"Báo hỏng / bảo trì","xử lý, nghiệm thu"),
        rect(804,260,116,64,"Dữ liệu","ghi, báo cáo"),
    ],
    "Sơ đồ chức năng khái niệm · không biểu diễn các dịch vụ triển khai độc lập",
)

figure(
    "erd-muon-tra", "ERD dạng bảng: mượn và trả thiết bị",
    "Mỗi thực thể có thuộc tính, PK, FK và quan hệ giữa người dùng, yêu cầu, thiết bị, bàn giao và trả.",
    [
        line("M 280,180 H 360", "1 : N", 320, 168, cardinality="one-many"),
        line("M 600,180 H 680", "1 : N", 640, 168, cardinality="one-many"),
        line("M 800,260 V 360", "N : 1", 846, 310, cardinality="many-one"),
        line("M 740,260 V 308 H 480 V 360", "1 : 0..1", 608, 297, arrow=False),
        line("M 280,430 H 360", "0..1 : 1", 320, 418, arrow=False),
    ],
    [
        entity(40,120,"Người dùng",["PK  user_id","ho_ten","dinh_danh_dang_nhap","trang_thai"]),
        entity(360,120,"Yêu cầu mượn",["PK  request_id","FK  requester_id","gio_nhan_du_kien","gio_tra_du_kien"]),
        entity(680,120,"Chi tiết yêu cầu",["PK  request_item_id","FK  request_id","FK  equipment_id","trang_thai_dong"]),
        entity(40,360,"Xác nhận trả",["PK  return_id","FK  handover_id","FK  receiver_id","thoi_diem_tra"]),
        entity(360,360,"Bàn giao",["PK  handover_id","FK  request_item_id","FK  giver_id","thoi_diem_giao"]),
        entity(680,360,"Thiết bị",["PK  equipment_id","ma_tai_san (UQ)","tinh_trang_vat_ly","kha_nang_su_dung"]),
    ],
    "PK: khóa chính · FK: khóa ngoại · UQ: duy nhất · bội số là dự kiến, chờ nhóm xác nhận",
)

figure(
    "erd-bao-tri-kiem-ke", "ERD dạng bảng: danh mục và bảo trì",
    "Phòng và loại thiết bị liên kết tài sản; tài sản có báo hỏng và sự kiện lịch sử; báo hỏng có thể dẫn tới bảo trì.",
    [
        line("M 280,180 H 360", "1 : N", 320, 168, cardinality="one-many"),
        line("M 600,180 H 680", "N : 1", 640, 168, cardinality="many-one"),
        line("M 430,260 V 296 H 160 V 360", "1 : N", 295, 284, cardinality="one-many"),
        line("M 480,260 V 360", "1 : N", 522, 324, cardinality="one-many"),
        line("M 280,430 H 360", "1 : N", 320, 418, cardinality="one-many"),
        line("M 540,260 V 305 H 800 V 360", "1 : N", 664, 294, cardinality="one-many"),
    ],
    [
        entity(40,120,"Phòng",["PK  room_id","ma_phong (UQ)","ten_phong","trang_thai"]),
        entity(360,120,"Thiết bị",["PK  equipment_id","FK  room_id","FK  category_id","ma_tai_san (UQ)"]),
        entity(680,120,"Loại thiết bị",["PK  category_id","ten_loai","mo_ta","trang_thai"]),
        entity(40,360,"Báo hỏng",["PK  damage_id","FK  equipment_id","FK  reporter_id","thoi_diem_bao"]),
        entity(360,360,"Lần bảo trì",["PK  maintenance_id","FK  equipment_id","FK  damage_id (có thể rỗng)","FK  technician_id","ket_qua_nghiem_thu"]),
        entity(680,360,"Lịch sử thiết bị",["PK  event_id","FK  equipment_id","FK  actor_id","hanh_dong","thoi_diem"]),
    ],
    "Bội số theo hướng đường nối · FK damage_id tùy chọn để hỗ trợ bảo trì định kỳ",
)

figure(
    "erd-kiem-ke", "ERD dạng bảng: kiểm kê thiết bị",
    "Người dùng tạo đợt kiểm kê; mỗi đợt có các dòng quan sát tham chiếu tài sản riêng lẻ.",
    [
        line("M 280,180 H 360", "1 : N", 320, 168, cardinality="one-many"),
        line("M 480,282 V 360", "1 : N", 522, 320, cardinality="one-many"),
        line("M 800,260 V 310 H 600 V 430", "1 : N", 704, 299, cardinality="one-many"),
    ],
    [
        entity(40,120,"Người dùng",["PK  user_id","ho_ten","dinh_danh_dang_nhap","trang_thai"]),
        entity(360,120,"Đợt kiểm kê",["PK  session_id","FK  creator_id","phạm_vi","thoi_diem_mo","trang_thai_dot"]),
        entity(680,120,"Thiết bị",["PK  equipment_id","ma_tai_san (UQ)","FK  room_id","tinh_trang_vat_ly"]),
        entity(360,360,"Dòng kiểm kê",["PK  inventory_item_id","FK  session_id","FK  equipment_id","vi_tri_quan_sat","hien_trang_quan_sat"]),
    ],
    "Dòng kiểm kê ghi quan sát để đối soát, không tự cập nhật hồ sơ thiết bị",
)

figure(
    "quy-trinh-muon-tra", "Workflow mượn thiết bị",
    "Ký hiệu theo mẫu trường: bắt đầu/kết thúc, bước xử lý, quyết định và các nhánh có/không.",
    [
        line("M 170,108 H 220"), line("M 380,108 H 430"),
        line("M 590,108 H 640", "Có", 615, 97),
        line("M 800,108 H 820"),
        line("M 980,108 H 1020", "Có", 1000, 97),
        line("M 510,154 V 250", "Không", 555, 207),
        line("M 900,154 V 250", "Không", 944, 207),
        line("M 510,316 V 500"), line("M 900,316 V 500"),
        line("M 1100,141 V 500"),
    ],
    [
        flow_node(30,80,140,56,"Bắt đầu","terminal"),
        flow_node(220,75,160,66,"Tạo yêu cầu|mượn số lượng"),
        flow_node(430,62,160,92,"Đủ điều kiện|và khả dụng?","decision"),
        flow_node(640,75,160,66,"Duyệt và|giữ số lượng"),
        flow_node(820,62,160,92,"Đến nhận|đúng hạn?","decision"),
        flow_node(1020,75,160,66,"Bàn giao|thực tế"),
        flow_node(430,250,160,66,"Từ chối,|ghi lý do"),
        flow_node(820,250,160,66,"Hết quyền|giữ chỗ"),
        flow_node(430,500,160,56,"Kết thúc: từ chối","terminal"),
        flow_node(820,500,160,56,"Kết thúc: hết hạn","terminal"),
        flow_node(1020,500,160,56,"Đã bàn giao","terminal"),
    ],
    "Duyệt khác bàn giao · quá 5 giờ từ giờ nhận dự kiến thì hết quyền giữ chỗ (quy tắc dự kiến)",
    width=1200, height=640,
)

figure(
    "quy-trinh-tra", "Workflow trả thiết bị",
    "Yêu cầu trả được kiểm tra theo lượt bàn giao; xác nhận nhận lại chỉ sau đối chiếu thực tế.",
    [
        line("M 170,108 H 220"), line("M 380,108 H 430"),
        line("M 590,108 H 640"),
        line("M 800,108 H 850", "Có", 825, 97),
        line("M 720,154 V 250", "Không", 762, 205),
        line("M 720,316 V 500"),
        line("M 930,154 V 250", "Có", 967, 205),
        line("M 1010,108 H 1130 V 350", "Không", 1064, 97),
        line("M 1010,283 H 1110 V 350"),
        line("M 1110,416 V 500"),
    ],
    [
        flow_node(30,80,140,56,"Bắt đầu","terminal"),
        flow_node(220,75,160,66,"Gửi yêu cầu|trả"),
        flow_node(430,75,160,66,"Đối chiếu số lượng|và hạn trả"),
        flow_node(640,62,160,92,"Trả đủ|đã bàn giao?","decision"),
        flow_node(850,62,160,92,"Thiết bị|hỏng?","decision"),
        flow_node(640,250,160,66,"Ghi nhận thiếu /|mất, chờ xử lý"),
        flow_node(850,250,160,66,"Lập báo hỏng|kèm tình trạng"),
        flow_node(1030,350,160,66,"Xác nhận|đã nhận lại"),
        flow_node(640,500,160,56,"Chưa hoàn tất trả","terminal"),
        flow_node(1030,500,160,56,"Hoàn tất lượt mượn","terminal"),
    ],
    "Gửi yêu cầu trả khác xác nhận đã nhận · thiếu/mất phải xử lý theo quy định đã chốt",
    width=1200, height=640,
)

figure(
    "quy-trinh-bao-tri", "Workflow báo hỏng và bảo trì",
    "Tiếp nhận báo hỏng, quyết định bảo trì, sửa chữa và nghiệm thu.",
    [
        line("M 170,108 H 220"), line("M 380,108 H 430"),
        line("M 590,108 H 640"),
        line("M 800,108 H 850", "Có", 824, 97),
        line("M 720,154 V 250", "Không", 766, 205),
        line("M 720,316 V 500"),
        line("M 1010,108 H 1030"),
        line("M 1110,141 V 240"),
        line("M 1030,286 H 930 V 390", "Không đạt", 962, 274),
        line("M 1110,332 V 390", "Đạt", 1140, 366),
        line("M 930,456 V 500"), line("M 1110,456 V 500"),
    ],
    [
        flow_node(30,80,140,56,"Bắt đầu","terminal"),
        flow_node(220,75,160,66,"Báo số lượng|bị hỏng"),
        flow_node(430,75,160,66,"Tiếp nhận,|đánh giá"),
        flow_node(640,62,160,92,"Cần|bảo trì?","decision"),
        flow_node(850,75,160,66,"Ngăn bàn giao,|phân công"),
        flow_node(1030,75,160,66,"Sửa chữa,|ghi kết quả"),
        flow_node(640,250,160,66,"Theo dõi /|đóng báo hỏng"),
        flow_node(1030,240,160,92,"Nghiệm thu|đạt?","decision"),
        flow_node(850,390,160,66,"Xử lý tiếp /|ngừng sử dụng"),
        flow_node(1030,390,160,66,"Bàn giao lại,|xác nhận sẵn sàng"),
        flow_node(640,500,160,56,"Kết thúc theo dõi","terminal"),
        flow_node(850,500,160,56,"Chưa sẵn sàng","terminal"),
        flow_node(1030,500,160,56,"Sẵn sàng","terminal"),
    ],
    "Báo hỏng khi đang mượn cần quy tắc đối soát riêng · mọi nhánh đều lưu lịch sử",
    width=1200, height=640,
)

figure(
    "quy-trinh-kiem-ke", "Workflow kiểm kê thiết bị",
    "Kiểm kê xác định tài sản, kiểm tra quyền, ghi nhận quan sát và đối chiếu chênh lệch.",
    [
        line("M 170,108 H 220"), line("M 380,108 H 430"),
        line("M 590,108 H 640"),
        line("M 800,108 H 850", "Có", 824, 97),
        line("M 720,154 V 250", "Không", 766, 205),
        line("M 720,316 V 500"),
        line("M 1010,108 H 1030"),
        line("M 1110,141 V 250"),
        line("M 1030,296 H 930 V 390", "Không", 974, 284),
        line("M 1110,342 V 390", "Có", 1140, 367),
        line("M 930,456 V 500"), line("M 1110,456 V 500"),
    ],
    [
        flow_node(30,80,140,56,"Bắt đầu","terminal"),
        flow_node(220,75,160,66,"Tạo đợt|kiểm kê"),
        flow_node(430,75,160,66,"Quét QR /|chọn loại"),
        flow_node(640,62,160,92,"Có quyền|ghi nhận?","decision"),
        flow_node(850,75,160,66,"Lưu số đếm|và tình trạng"),
        flow_node(1030,75,160,66,"Đối chiếu|với hồ sơ"),
        flow_node(640,250,160,66,"Từ chối|thao tác"),
        flow_node(1030,250,160,92,"Có|chênh lệch?","decision"),
        flow_node(850,390,160,66,"Chốt đợt,|lưu lịch sử"),
        flow_node(1030,390,160,66,"Ghi chênh lệch,|chờ xử lý"),
        flow_node(640,500,160,56,"Kết thúc từ chối","terminal"),
        flow_node(850,500,160,56,"Hoàn tất kiểm kê","terminal"),
        flow_node(1030,500,160,56,"Chờ đối soát","terminal"),
    ],
    "Quét QR chỉ nhận diện tài sản · ghi chênh lệch không tự sửa dữ liệu chuẩn",
    width=1200, height=640,
)

print("Generated:")
for file in sorted(OUT.glob("*.html")):
    if file.stem not in SUPERSEDED_ERD and not file.stem.startswith("erd-so-luong-"):
        print(file.name)
