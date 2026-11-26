<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('borrow_requests')) {
            Schema::create('borrow_requests', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('borrower_id')->constrained('users');
                $table->dateTime('pickup_at');
                $table->dateTime('due_at');
                $table->string('purpose', 32);
                $table->dateTime('purpose_confirmed_at');
                $table->string('status', 32);
                $table->dateTime('submitted_at');
                $table->foreignId('reviewed_by')->nullable()->constrained('users');
                $table->dateTime('reviewed_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->index(['borrower_id', 'submitted_at']);
                $table->index(['status', 'due_at']);
            });
            DB::statement("ALTER TABLE borrow_requests ADD CONSTRAINT chk_borrow_requests_purpose CHECK (purpose IN ('teaching','practice'))");
            DB::statement("ALTER TABLE borrow_requests ADD CONSTRAINT chk_borrow_requests_status CHECK (status IN ('submitted','approved','rejected','cancelled','handed_over'))");
            DB::statement('ALTER TABLE borrow_requests ADD CONSTRAINT chk_borrow_requests_due CHECK (due_at > pickup_at)');
            DB::statement("ALTER TABLE borrow_requests ADD CONSTRAINT chk_borrow_requests_rejection CHECK (status <> 'rejected' OR (rejection_reason IS NOT NULL AND CHAR_LENGTH(TRIM(rejection_reason)) > 0))");
            DB::statement("ALTER TABLE borrow_requests ADD CONSTRAINT chk_borrow_requests_review CHECK (status NOT IN ('approved','rejected','handed_over') OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))");
        }

        if (!Schema::hasTable('borrow_request_items')) {
            Schema::create('borrow_request_items', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('request_id')->constrained('borrow_requests');
                $table->foreignId('equipment_type_id')->constrained('equipment_types');
                $table->foreignId('source_location_id')->constrained('locations');
                $table->unsignedInteger('quantity');
                $table->unique(['request_id', 'equipment_type_id', 'source_location_id'], 'borrow_request_items_unique_line');
            });
            DB::statement('ALTER TABLE borrow_request_items ADD CONSTRAINT chk_borrow_request_items_qty CHECK (quantity > 0)');
        }

        if (!Schema::hasTable('handovers')) {
            Schema::create('handovers', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('request_id')->unique()->constrained('borrow_requests');
                $table->foreignId('handed_by')->constrained('users');
                $table->foreignId('received_by')->constrained('users');
                $table->dateTime('handed_at');
                $table->string('status', 32);
            });
        }

        if (!Schema::hasTable('handover_items')) {
            Schema::create('handover_items', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('handover_id')->constrained('handovers');
                $table->foreignId('request_item_id')->unique()->constrained('borrow_request_items');
                $table->foreignId('source_balance_id')->constrained('stock_balances');
                $table->unsignedInteger('quantity');
                $table->string('condition_at_handover', 255);
            });
            DB::statement('ALTER TABLE handover_items ADD CONSTRAINT chk_handover_items_qty CHECK (quantity > 0)');
        }

        if (!Schema::hasTable('return_records')) {
            Schema::create('return_records', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('handover_id')->unique()->constrained('handovers');
                $table->foreignId('requested_by')->constrained('users');
                $table->dateTime('requested_at');
                $table->foreignId('received_by')->nullable()->constrained('users');
                $table->dateTime('received_at')->nullable();
                $table->string('status', 32);
            });
            DB::statement("ALTER TABLE return_records ADD CONSTRAINT chk_return_records_status CHECK (status IN ('requested','confirmed'))");
            DB::statement("ALTER TABLE return_records ADD CONSTRAINT chk_return_records_confirm CHECK ((status = 'requested' AND received_by IS NULL AND received_at IS NULL) OR (status = 'confirmed' AND received_by IS NOT NULL AND received_at IS NOT NULL))");
        }

        if (!Schema::hasTable('return_items')) {
            Schema::create('return_items', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('return_record_id')->constrained('return_records');
                $table->foreignId('handover_item_id')->unique()->constrained('handover_items');
                $table->unsignedInteger('good_quantity')->default(0);
                $table->unsignedInteger('damaged_quantity')->default(0);
                $table->unsignedInteger('missing_quantity')->default(0);
                $table->foreignId('destination_location_id')->nullable()->constrained('locations');
            });
            DB::statement('ALTER TABLE return_items ADD CONSTRAINT chk_return_items_total CHECK (good_quantity + damaged_quantity + missing_quantity > 0)');
            DB::statement('ALTER TABLE return_items ADD CONSTRAINT chk_return_items_dest CHECK ((good_quantity + damaged_quantity = 0) OR destination_location_id IS NOT NULL)');
        }

        if (!Schema::hasTable('loss_cases')) {
            Schema::create('loss_cases', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->id();
                $table->foreignId('return_item_id')->unique()->constrained('return_items');
                $table->unsignedInteger('quantity');
                $table->string('status', 32);
                $table->unsignedInteger('recovered_quantity')->default(0);
                $table->unsignedInteger('written_off_quantity')->default(0);
                $table->foreignId('resolved_by')->nullable()->constrained('users');
                $table->dateTime('resolved_at')->nullable();
            });
            DB::statement('ALTER TABLE loss_cases ADD CONSTRAINT chk_loss_cases_qty CHECK (quantity > 0)');
            DB::statement('ALTER TABLE loss_cases ADD CONSTRAINT chk_loss_cases_sum CHECK (recovered_quantity + written_off_quantity <= quantity)');
            DB::statement("ALTER TABLE loss_cases ADD CONSTRAINT chk_loss_cases_closed CHECK (status <> 'closed' OR (recovered_quantity + written_off_quantity = quantity AND resolved_at IS NOT NULL AND resolved_by IS NOT NULL))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loss_cases');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('return_records');
        Schema::dropIfExists('handover_items');
        Schema::dropIfExists('handovers');
        Schema::dropIfExists('borrow_request_items');
        Schema::dropIfExists('borrow_requests');
    }
};
