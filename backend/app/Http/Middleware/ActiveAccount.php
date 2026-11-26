<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->withErrors(['login' => 'Tài khoản đã bị khóa.']);
        }
        return $next($request);
    }
}
