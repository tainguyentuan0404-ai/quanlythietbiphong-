<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->string('code', 64)->unique();
                $table->string('name', 255);
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('role_id')->constrained('roles');
                $table->string('login', 64)->unique();
                $table->string('full_name', 255);
                $table->string('password_hash', 255);
                $table->boolean('is_active')->default(true);
            });
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_is_active CHECK (is_active IN (0, 1))');
        }

        if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->string('code', 64)->unique();
                $table->string('name', 255);
                $table->boolean('is_active')->default(true);
            });
            DB::statement('ALTER TABLE rooms ADD CONSTRAINT chk_rooms_is_active CHECK (is_active IN (0, 1))');
        }

        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('room_id')->constrained('rooms');
                $table->string('code', 64);
                $table->string('name', 255);
                $table->boolean('is_active')->default(true);
                $table->unique(['room_id', 'code']);
            });
            DB::statement('ALTER TABLE locations ADD CONSTRAINT chk_locations_is_active CHECK (is_active IN (0, 1))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
