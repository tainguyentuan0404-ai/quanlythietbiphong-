<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('damage_reports')) {
            Schema::create('damage_reports', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('equipment_type_id')->constrained('equipment_types');
                $table->unsignedInteger('quantity');
                $table->foreignId('source_balance_id')->nullable()->constrained('stock_balances');
                $table->foreignId('handover_item_id')->nullable()->constrained('handover_items');
                $table->foreignId('return_item_id')->nullable()->constrained('return_items');
                $table->foreignId('reporter_id')->constrained('users');
                $table->text('description');
                $table->string('status', 32);
                $table->dateTime('reported_at');
            });
            DB::statement('ALTER TABLE damage_reports ADD CONSTRAINT chk_damage_reports_qty CHECK (quantity > 0)');
            DB::statement('ALTER TABLE damage_reports ADD CONSTRAINT chk_damage_reports_origin CHECK (source_balance_id IS NOT NULL OR handover_item_id IS NOT NULL OR return_item_id IS NOT NULL)');
        }

        if (!Schema::hasTable('maintenance_jobs')) {
            Schema::create('maintenance_jobs', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('damage_report_id')->constrained('damage_reports');
                $table->foreignId('equipment_type_id')->constrained('equipment_types');
                $table->foreignId('source_balance_id')->nullable()->constrained('stock_balances');
                $table->unsignedInteger('quantity');
                $table->foreignId('technician_id')->nullable()->constrained('users');
                $table->dateTime('planned_at')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('accepted_at')->nullable();
                $table->string('status', 32);
                $table->unsignedInteger('repaired_quantity')->default(0);
                $table->unsignedInteger('damaged_quantity')->default(0);
                $table->unsignedInteger('retired_quantity')->default(0);
                $table->decimal('cost_amount', 12, 2)->nullable();
            });
            DB::statement('ALTER TABLE maintenance_jobs ADD CONSTRAINT chk_maintenance_jobs_qty CHECK (quantity > 0)');
            DB::statement('ALTER TABLE maintenance_jobs ADD CONSTRAINT chk_maintenance_jobs_sum CHECK (repaired_quantity + damaged_quantity + retired_quantity <= quantity)');
            DB::statement('ALTER TABLE maintenance_jobs ADD CONSTRAINT chk_maintenance_jobs_accept CHECK (accepted_at IS NULL OR (started_at IS NOT NULL AND accepted_at >= started_at AND repaired_quantity + damaged_quantity + retired_quantity = quantity))');
            DB::statement('ALTER TABLE maintenance_jobs ADD CONSTRAINT chk_maintenance_jobs_cost CHECK (cost_amount IS NULL OR cost_amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_jobs');
        Schema::dropIfExists('damage_reports');
    }
};
