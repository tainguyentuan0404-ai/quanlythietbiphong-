@extends('layout')
@section('title', 'Thiết bị phòng thực hành')
@section('content')
<div class="heading">
    <h1>Thiết bị phòng thực hành</h1>
    @if(auth()->user()->canManageEquipment())<a class="button primary" href="/equipment/create">Thêm loại thiết bị</a>@endif
</div>
<section class="panel">
    <h2>Danh sách loại thiết bị</h2>
    <form class="filters" method="get" action="/equipment">
        <label>Tìm thiết bị<input type="search" name="q" value="{{ request('q') }}" placeholder="Mã, tên hoặc mã QR"></label>
        <label>Phòng<select name="room">
            <option value="">Tất cả phòng</option>
            @foreach($rooms as $room)<option value="{{ $room->id }}" @selected(request('room') == $room->id)>{{ $room->name }}</option>@endforeach
        </select></label>
        <button class="primary">Tìm kiếm</button><a href="/equipment">Đặt lại</a>
    </form>
    @include('equipment.stock', ['showType' => true])
</section>
@endsection
