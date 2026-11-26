@extends('layout')
@section('title', $equipment ? 'Sửa thông tin thiết bị' : 'Thêm loại thiết bị')
@section('content')
<p><a href="{{ $equipment ? '/equipment/'.$equipment->id : '/equipment' }}">Quay lại</a></p>
<section class="panel narrow">
    <h1>@yield('title')</h1>
    <form class="fields" method="post" action="{{ $equipment ? '/equipment/'.$equipment->id : '/equipment' }}">
        @csrf
        @if($equipment)@method('PATCH')@endif
        <input type="hidden" name="token" value="{{ old('token', $token) }}">
        <label>Tên loại thiết bị<input name="name" maxlength="255" value="{{ old('name', $equipment->name ?? '') }}" required autofocus></label>
        <label>Nhóm<input name="category" maxlength="120" value="{{ old('category', $equipment->category ?? '') }}"></label>
        <label>Thông số<textarea name="specification" maxlength="5000">{{ old('specification', $equipment->specification ?? '') }}</textarea></label>
        @unless($equipment)
            <label>Phòng / vị trí<select name="location_id" required>
                <option value="">Chọn vị trí lưu giữ</option>
                @foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->room }} · {{ $location->name }}</option>@endforeach
            </select></label>
            <label>Số lượng tốt ban đầu<input type="number" min="0" max="4294967295" step="1" name="quantity" value="{{ old('quantity', 0) }}" required></label>
        @endunless
        <button class="primary">Lưu thiết bị</button>
    </form>
</section>
@endsection
