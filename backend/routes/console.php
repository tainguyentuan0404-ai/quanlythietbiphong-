<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

Artisan::command('equipment:create-staff {login} {name}', function () {
    $data = ['login' => $this->argument('login'), 'full_name' => $this->argument('name'), 'password' => $this->secret('Mật khẩu mới (ít nhất 12 ký tự)')];
    $validator = Validator::make($data, ['login' => 'required|string|max:64|unique:users,login', 'full_name' => 'required|string|max:255', 'password' => 'required|string|min:12|max:72']);
    if ($validator->fails() || $data['password'] !== $this->secret('Nhập lại mật khẩu')) {
        $this->error('Kiểm tra tên đăng nhập, họ tên, độ dài và xác nhận mật khẩu.');
        return 1;
    }
    DB::transaction(function () use ($data) {
        DB::table('roles')->insertOrIgnore(['code' => 'staff', 'name' => 'Người phụ trách']);
        DB::table('users')->insert([
            'login' => $data['login'], 'full_name' => $data['full_name'],
            'password_hash' => Hash::make($data['password']), 'is_active' => true,
            'role_id' => DB::table('roles')->where('code', 'staff')->value('id'),
        ]);
    });
    $this->info('Đã tạo tài khoản phụ trách.');
    return 0;
});
