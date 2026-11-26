<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

Artisan::command('equipment:create-staff {login} {name} {--password-stdin}', function () {
    if ($this->option('password-stdin')) {
        $password = rtrim((string) stream_get_contents(STDIN), "\r\n");
        $confirm = $password;
    } else {
        $password = (string) $this->secret('Mật khẩu mới (ít nhất 12 ký tự)');
        if ($password === '') {
            $this->error('Không đọc được mật khẩu từ cửa sổ này. Dùng tùy chọn --password-stdin.');
            return 1;
        }
        $confirm = (string) $this->secret('Nhập lại mật khẩu');
    }

    $data = [
        'login' => $this->argument('login'),
        'full_name' => $this->argument('name'),
        'password' => $password,
    ];
    $validator = Validator::make($data, [
        'login' => 'required|string|max:64|unique:users,login',
        'full_name' => 'required|string|max:255',
        'password' => 'required|string|min:12|max:72',
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }
        return 1;
    }

    if ($password !== $confirm) {
        $this->error('Mật khẩu nhập lại không khớp.');
        return 1;
    }

    DB::transaction(function () use ($data) {
        DB::table('roles')->insertOrIgnore(['code' => 'staff', 'name' => 'Người phụ trách']);
        DB::table('users')->insert([
            'login' => $data['login'],
            'full_name' => $data['full_name'],
            'password_hash' => Hash::make($data['password']),
            'is_active' => true,
            'role_id' => DB::table('roles')->where('code', 'staff')->value('id'),
        ]);
    });

    $this->info('Đã tạo tài khoản phụ trách.');
    return 0;
});
