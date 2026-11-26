@extends('layout')
@section('title', 'Đợt kiểm kê #' . $session->id)
@section('content')
@php
    $conditionLabels = ['good' => 'Tốt', 'damaged' => 'Hỏng', 'unknown' => 'Chưa rõ'];
    $resolutionLabels = ['pending' => 'Chưa đếm', 'matched' => 'Khớp hồ sơ', 'discrepancy' => 'Có chênh lệch'];
    $eventLabels = ['opening' => 'Nhập tồn đầu', 'handover' => 'Bàn giao mượn', 'return_good' => 'Trả tốt', 'return_damaged' => 'Trả hỏng', 'maintenance_start' => 'Bắt đầu sửa', 'maintenance_accept' => 'Nghiệm thu sửa'];
    $totalItems = $items->count();
    $countedItems = $items->whereNotNull('counted_quantity')->count();
    $discrepancyItems = $items->filter(fn ($i) => $i->difference !== null && $i->difference !== 0)->count();
    $canEdit = $session->status === 'draft' && auth()->user()->canManageEquipment();
@endphp

<p><a href="/inventory-sessions">Danh sách đợt kiểm kê</a></p>

<div class="heading">
    <div>
        <h1>Đợt kiểm kê #{{ $session->id }} · {{ $session->room }} / {{ $session->location }}</h1>
        <small>
            Người lập: {{ $session->creator ?? 'Hệ thống' }} ·
            Mở lúc: {{ \Illuminate\Support\Carbon::parse($session->started_at, 'UTC')->tz('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}
            @if($session->closed_at)
                · Đóng lúc: {{ \Illuminate\Support\Carbon::parse($session->closed_at, 'UTC')->tz('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}
            @endif
        </small>
    </div>
    <span class="tag {{ $session->status === 'closed' ? 'tag-closed' : 'tag-draft' }}">
        {{ $session->status === 'closed' ? 'Đã hoàn tất (chỉ xem)' : 'Đang kiểm kê' }}
    </span>
</div>

<section class="panel">
    <h2>Tra nhanh loại thiết bị trong đợt</h2>
    <p class="hint">Phạm vi đếm: số lượng hiện có tại {{ $session->location }} theo từng tình trạng (gồm sẵn sàng và bị khóa chờ xử lý). Hoàn tất kiểm kê chỉ lưu biên bản đối soát và không tự chỉnh tồn kho.</p>
    <form class="filters" method="get" action="/qr">
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        <label>Mã loại hoặc mã QR<input type="search" name="code" placeholder="Nhập hoặc quét mã loại" required></label>
        <button class="primary">Định vị dòng</button>
        @if($highlight)<a href="/inventory-sessions/{{ $session->id }}">Bỏ đánh dấu</a>@endif
    </form>
</section>

@if($movements->isNotEmpty())
<section class="panel warn-panel">
    <h2>Biến động kho tại vị trí trong thời gian kiểm kê ({{ $movements->count() }})</h2>
    <p class="hint">Các biến động dưới đây phát sinh sau thời điểm chụp số hồ sơ. Kiểm tra đối chiếu trước khi kết luận chênh lệch.</p>
    <ul class="history">
        @foreach($movements as $move)
            <li>
                <strong>{{ $eventLabels[$move->event_kind] ?? $move->event_kind }}</strong> ·
                {{ $move->equipment_name }} (<span class="code">{{ $move->equipment_code }}</span>) ·
                Số lượng: {{ $move->quantity }}
                <small>{{ $move->actor_name ?? 'Hệ thống' }} · {{ \Illuminate\Support\Carbon::parse($move->happened_at, 'UTC')->tz('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</small>
            </li>
        @endforeach
    </ul>
</section>
@endif

<section class="panel">
    <div class="heading">
        <h2>Chi tiết đối soát ({{ $countedItems }}/{{ $totalItems }} đã đếm · {{ $discrepancyItems }} dòng lệch)</h2>
        @if($canEdit)
            <form method="post" action="/inventory-sessions/{{ $session->id }}/close">
                @csrf
                <button class="primary">Hoàn tất đợt kiểm kê</button>
            </form>
        @endif
    </div>
    <div class="scroll">
        <table>
            <thead>
                <tr>
                    <th>Mã loại</th>
                    <th>Loại thiết bị</th>
                    <th>Tình trạng</th>
                    <th class="num">Hồ sơ</th>
                    <th>Thực đếm</th>
                    <th class="num">Chênh lệch</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="{{ $highlight === (int) $item->equipment_type_id ? 'row-highlight' : '' }}">
                        <td class="code">{{ $item->code }}</td>
                        <td>
                            <a href="/equipment/{{ $item->equipment_type_id }}">{{ $item->name }}</a>
                            @if($item->counted_at)
                                <small>Đếm bởi {{ $item->counter_name ?? 'Hệ thống' }} lúc {{ \Illuminate\Support\Carbon::parse($item->counted_at, 'UTC')->tz('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</small>
                            @endif
                        </td>
                        <td>{{ $conditionLabels[$item->physical_condition] ?? $item->physical_condition }}</td>
                        <td class="num">{{ $item->expected_quantity }}</td>
                        <td>
                            @if($canEdit)
                                <form class="inline-count" method="post" action="/inventory-sessions/{{ $session->id }}/items/{{ $item->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="revision" value="{{ $item->revision }}">
                                    <input type="number" name="counted_quantity" min="0" max="4294967295" step="1" value="{{ $item->counted_quantity }}" placeholder="Chưa đếm">
                                    <button>Lưu</button>
                                </form>
                            @else
                                {{ $item->counted_quantity === null ? 'Chưa đếm' : $item->counted_quantity }}
                            @endif
                        </td>
                        <td class="num">
                            @if($item->difference === null)
                                —
                            @elseif($item->difference > 0)
                                +{{ $item->difference }}
                            @else
                                {{ $item->difference }}
                            @endif
                        </td>
                        <td>
                            <span class="tag {{ $item->resolution_status === 'discrepancy' ? 'tag-warn' : ($item->resolution_status === 'matched' ? 'tag-ok' : 'tag-draft') }}">
                                {{ $resolutionLabels[$item->resolution_status] ?? $item->resolution_status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
