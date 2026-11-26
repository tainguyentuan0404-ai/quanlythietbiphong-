@extends('layout')
@section('title', $equipment->name)
@section('content')
<p><a href="/equipment">Danh sách thiết bị</a></p>
<div class="heading"><div><h1>{{ $equipment->name }}</h1><span class="code">{{ $equipment->code }}</span></div>
    @if(auth()->user()->canManageEquipment())<a class="button" href="/equipment/{{ $equipment->id }}/edit">Sửa thông tin</a>@endif
</div>
<section class="panel"><h2>Thông tin loại</h2>
    <p>Nhóm: {{ $equipment->category ?: 'Chưa ghi' }}</p><p>Thông số: {{ $equipment->specification ?: 'Chưa ghi' }}</p>
    <p>Mã QR: {{ $equipment->qr_code }}</p>
</section>
<section class="panel"><h2>Số lượng theo vị trí</h2>@include('equipment.stock', ['showType' => false])</section>
<section class="panel"><h2>Lịch sử thay đổi</h2>
    <ul class="history">
        @forelse($history as $event)
            <li><strong>{{ ['opening' => 'Nhập tồn đầu', 'create' => 'Thêm loại thiết bị', 'update' => 'Sửa thông tin'][$event->action] ?? $event->action }}</strong>
                @if($event->action === 'create') · {{ data_get(json_decode($event->details_json ?? '{}'), 'quantity', 0) }} chiếc @endif
                <small>{{ $event->full_name ?? 'Hệ thống' }} · {{ \Illuminate\Support\Carbon::parse($event->happened_at, 'UTC')->tz('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</small>
            </li>
        @empty<li>Chưa có lịch sử cho loại này.</li>@endforelse
    </ul>
</section>
@endsection
