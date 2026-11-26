<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('equipment_types')) {
            Schema::create('equipment_types', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->string('code', 64)->unique();
                $table->string('name', 255);
                $table->string('category', 120)->nullable();
                $table->text('specification')->nullable();
                $table->string('qr_code', 128)->unique();
                $table->boolean('is_active')->default(true);
            });
            DB::statement('ALTER TABLE equipment_types ADD CONSTRAINT chk_equipment_types_is_active CHECK (is_active IN (0, 1))');
        }

        if (!Schema::hasTable('stock_balances')) {
            Schema::create('stock_balances', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('equipment_type_id')->constrained('equipment_types');
                $table->foreignId('location_id')->constrained('locations');
                $table->string('physical_condition', 32);
                $table->string('use_state', 32);
                $table->unsignedInteger('quantity');
                $table->unique(['equipment_type_id', 'location_id', 'physical_condition', 'use_state'], 'stock_balances_unique_bucket');
            });
            DB::statement("ALTER TABLE stock_balances ADD CONSTRAINT chk_stock_balances_condition CHECK (physical_condition IN ('good','damaged','unknown'))");
            DB::statement("ALTER TABLE stock_balances ADD CONSTRAINT chk_stock_balances_use_state CHECK (use_state IN ('ready','blocked','maintenance','retired'))");
            DB::statement("ALTER TABLE stock_balances ADD CONSTRAINT chk_stock_balances_ready_good CHECK (use_state <> 'ready' OR physical_condition = 'good')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('equipment_types');
    }
};
