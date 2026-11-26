<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController
{
    public function create()
    {
        return view('login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate(['login' => 'required|string|max:64', 'password' => 'required|string|max:255']);
        if (!Auth::attempt($credentials + ['is_active' => true])) {
            return back()->withErrors(['login' => 'Tên đăng nhập hoặc mật khẩu chưa đúng, hoặc tài khoản đã bị khóa.'])->onlyInput('login');
        }
        $request->session()->regenerate();
        return redirect()->intended('/equipment');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
