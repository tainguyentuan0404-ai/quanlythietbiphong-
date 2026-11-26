@extends('layout')
@section('title', 'Kiểm kê thiết bị')
@section('content')
<div class="heading">
    <h1>Kiểm kê thiết bị</h1>
</div>

<section class="panel">
    <h2>Tra nhanh bằng mã loại / mã QR</h2>
    <form class="filters" method="get" action="/qr">
        <label>Mã loại hoặc mã QR<input type="search" name="code" value="{{ old('code') }}" placeholder="Ví dụ: TB-L001" required></label>
        <button class="primary">Tra cứu</button>
    </form>
</section>

@if(auth()->user()->canManageEquipment())
<section class="panel">
    <h2>Lập đợt kiểm kê theo vị trí</h2>
    <p class="hint">Đợt kiểm kê chụp số lượng hiện có tại tủ theo từng tình trạng (gồm sẵn sàng và bị khóa chờ xử lý; không gồm thiết bị đang cho mượn, đang bảo trì hoặc đã thanh lý).</p>
    <form class="filters" method="post" action="/inventory-sessions">
        @csrf
        <label>Phòng / vị trí<select name="location_id" required>
            <option value="">Chọn vị trí kiểm kê</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->room }} · {{ $location->name }}</option>
            @endforeach
        </select></label>
        <button class="primary">Tạo đợt kiểm kê</button>
    </form>
</section>
@endif

<section class="panel">
    <h2>Danh sách đợt kiểm kê</h2>
    <div class="scroll">
        <table>
            <thead>
                <tr>
                    <th>Đợt</th>
                    <th>Phòng / vị trí</th>
                    <th>Người lập</th>
                    <th>Bắt đầu</th>
                    <th>Trạng thái</th>
                    <th class="num">Đã đếm</th>
                    <th class="num">Dòng lệch</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td class="code">#{{ $session->id }}</td>
                        <td>{{ $session->room }}<small>{{ $session->location }}</small></td>
                        <td>{{ $session->creator ?? 'Hệ thống' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($session->started_at, 'UTC')->tz('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="tag {{ $session->status === 'closed' ? 'tag-closed' : 'tag-draft' }}">
                                {{ $session->status === 'closed' ? 'Đã hoàn tất' : 'Đang kiểm kê' }}
                            </span>
                        </td>
                        <td class="num">{{ (int) $session->counted_items }} / {{ (int) $session->total_items }}</td>
                        <td class="num">{{ (int) $session->discrepancy_items }}</td>
                        <td><a href="/inventory-sessions/{{ $session->id }}">{{ $session->status === 'closed' ? 'Xem lại' : 'Nhập số đếm' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">Chưa có đợt kiểm kê nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
