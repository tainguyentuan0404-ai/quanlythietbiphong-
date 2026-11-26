<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_sessions')) {
            Schema::create('inventory_sessions', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('location_id')->constrained('locations');
                $table->foreignId('created_by')->constrained('users');
                $table->dateTime('started_at');
                $table->dateTime('closed_at')->nullable();
                $table->string('status', 32);
                $table->index(['location_id', 'started_at']);
            });
            DB::statement("ALTER TABLE inventory_sessions ADD CONSTRAINT chk_inventory_sessions_status CHECK ((status = 'draft' AND closed_at IS NULL) OR (status = 'closed' AND closed_at IS NOT NULL AND closed_at >= started_at))");
        }

        if (!Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('session_id')->constrained('inventory_sessions');
                $table->foreignId('equipment_type_id')->constrained('equipment_types');
                $table->string('physical_condition', 32);
                $table->unsignedInteger('expected_quantity');
                $table->unsignedInteger('counted_quantity')->nullable();
                $table->foreignId('counted_by')->nullable()->constrained('users');
                $table->dateTime('counted_at')->nullable();
                $table->string('resolution_status', 255);
                $table->foreignId('resolved_by')->nullable()->constrained('users');
                $table->dateTime('resolved_at')->nullable();
                $table->unique(['session_id', 'equipment_type_id', 'physical_condition'], 'inventory_items_unique_line');
            });
            DB::statement("ALTER TABLE inventory_items ADD CONSTRAINT chk_inventory_items_condition CHECK (physical_condition IN ('good','damaged','unknown'))");
            DB::statement('ALTER TABLE inventory_items ADD CONSTRAINT chk_inventory_items_counted CHECK ((counted_quantity IS NULL AND counted_at IS NULL AND counted_by IS NULL) OR (counted_quantity IS NOT NULL AND counted_at IS NOT NULL AND counted_by IS NOT NULL))');
        }

        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('users');
                $table->string('subject_type', 32);
                $table->unsignedBigInteger('subject_id');
                $table->string('action', 32);
                $table->json('details_json')->nullable();
                $table->dateTime('happened_at');
                $table->index(['subject_type', 'subject_id', 'happened_at']);
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('from_balance_id')->nullable()->constrained('stock_balances');
                $table->foreignId('to_balance_id')->nullable()->constrained('stock_balances');
                $table->unsignedInteger('quantity');
                $table->string('event_kind', 32);
                $table->foreignId('handover_item_id')->nullable()->constrained('handover_items');
                $table->foreignId('return_item_id')->nullable()->constrained('return_items');
                $table->foreignId('damage_report_id')->nullable()->constrained('damage_reports');
                $table->foreignId('maintenance_job_id')->nullable()->constrained('maintenance_jobs');
                $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items');
                $table->foreignId('actor_id')->constrained('users');
                $table->string('idempotency_key', 255)->unique();
                $table->dateTime('happened_at');
                $table->index('happened_at');
            });
            DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_qty CHECK (quantity > 0)');
            DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_side CHECK (from_balance_id IS NOT NULL OR to_balance_id IS NOT NULL)');
            DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_distinct CHECK (from_balance_id IS NULL OR to_balance_id IS NULL OR from_balance_id <> to_balance_id)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_sessions');
    }
};
