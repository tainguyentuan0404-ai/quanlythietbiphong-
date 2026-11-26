@extends('layout')
@section('title', 'Đăng nhập')
@section('content')
<section class="panel narrow">
    <h1>Đăng nhập</h1>
    <form class="fields" method="post" action="/login">
        @csrf
        <label>Tên đăng nhập<input name="login" value="{{ old('login') }}" autocomplete="username" required autofocus></label>
        <label>Mật khẩu<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="primary">Đăng nhập</button>
    </form>
</section>
@endsection
