<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · LabDevice Hub</title>
    <link rel="stylesheet" href="/equipment.css">
</head>
<body>
    @auth
    <aside class="sidebar">
        <a class="brand" href="/equipment">LabDevice Hub</a>
        <p class="subtitle">Quản lý phòng thực hành</p>
        <nav aria-label="Điều hướng">
            <p class="nav-label">Nghiệp vụ</p>
            <details open>
                <summary>Thiết bị &amp; Kiểm kê</summary>
                <a class="{{ request()->is('equipment*') ? 'active' : '' }}" href="/equipment">Thiết bị</a>
                <a class="{{ request()->is('inventory*') ? 'active' : '' }}" href="/inventory-sessions">Kiểm kê</a>
            </details>
        </nav>
    </aside>
    @endauth
    <div class="workspace">
    <header>
        @guest<a class="brand" href="/login">LabDevice Hub</a>@endguest
        @auth<div class="account"><span>{{ auth()->user()->full_name }}</span><form method="post" action="/logout">@csrf<button>Đăng xuất</button></form></div>@endauth
    </header>
    <main>
        @if(session('status'))<p role="status" class="message">{{ session('status') }}</p>@endif
        @if($errors->any())
            <div role="alert" class="errors"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
    </div>
</body>
</html>
