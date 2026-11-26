CREATE TABLE roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    login VARCHAR(64) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (role_id) REFERENCES roles (id),
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE locations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (room_id) REFERENCES rooms (id),
    UNIQUE (room_id, code),
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE equipment_types (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(120) NULL,
    specification TEXT NULL,
    qr_code VARCHAR(128) NOT NULL UNIQUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_balances (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    equipment_type_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    physical_condition VARCHAR(32) NOT NULL,
    use_state VARCHAR(32) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    FOREIGN KEY (equipment_type_id) REFERENCES equipment_types (id),
    FOREIGN KEY (location_id) REFERENCES locations (id),
    UNIQUE (equipment_type_id, location_id, physical_condition, use_state),
    CHECK (physical_condition IN ('good','damaged','unknown')),
    CHECK (use_state IN ('ready','blocked','maintenance','retired')),
    CHECK (use_state <> 'ready' OR physical_condition = 'good')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE borrow_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    borrower_id BIGINT UNSIGNED NOT NULL,
    pickup_at DATETIME NOT NULL,
    due_at DATETIME NOT NULL,
    purpose VARCHAR(32) NOT NULL,
    purpose_confirmed_at DATETIME NOT NULL,
    status VARCHAR(32) NOT NULL,
    submitted_at DATETIME NOT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    rejection_reason TEXT NULL,
    FOREIGN KEY (borrower_id) REFERENCES users (id),
    FOREIGN KEY (reviewed_by) REFERENCES users (id),
    INDEX (borrower_id, submitted_at),
    INDEX (status, due_at),
    CHECK (purpose IN ('teaching','practice')),
    CHECK (status IN ('submitted','approved','rejected','cancelled','handed_over')),
    CHECK (due_at > pickup_at),
    CHECK (status <> 'rejected' OR (rejection_reason IS NOT NULL AND CHAR_LENGTH(TRIM(rejection_reason)) > 0)),
    CHECK (status NOT IN ('approved','rejected','handed_over') OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE borrow_request_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    equipment_type_id BIGINT UNSIGNED NOT NULL,
    source_location_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    FOREIGN KEY (request_id) REFERENCES borrow_requests (id),
    FOREIGN KEY (equipment_type_id) REFERENCES equipment_types (id),
    FOREIGN KEY (source_location_id) REFERENCES locations (id),
    UNIQUE (request_id, equipment_type_id, source_location_id),
    CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE handovers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL UNIQUE,
    handed_by BIGINT UNSIGNED NOT NULL,
    received_by BIGINT UNSIGNED NOT NULL,
    handed_at DATETIME NOT NULL,
    status VARCHAR(32) NOT NULL,
    FOREIGN KEY (request_id) REFERENCES borrow_requests (id),
    FOREIGN KEY (handed_by) REFERENCES users (id),
    FOREIGN KEY (received_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE handover_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    handover_id BIGINT UNSIGNED NOT NULL,
    request_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    source_balance_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    condition_at_handover VARCHAR(255) NOT NULL,
    FOREIGN KEY (handover_id) REFERENCES handovers (id),
    FOREIGN KEY (request_item_id) REFERENCES borrow_request_items (id),
    FOREIGN KEY (source_balance_id) REFERENCES stock_balances (id),
    CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE return_records (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    handover_id BIGINT UNSIGNED NOT NULL UNIQUE,
    requested_by BIGINT UNSIGNED NOT NULL,
    requested_at DATETIME NOT NULL,
    received_by BIGINT UNSIGNED NULL,
    received_at DATETIME NULL,
    status VARCHAR(32) NOT NULL,
    FOREIGN KEY (handover_id) REFERENCES handovers (id),
    FOREIGN KEY (requested_by) REFERENCES users (id),
    FOREIGN KEY (received_by) REFERENCES users (id),
    CHECK (status IN ('requested','confirmed')),
    CHECK ((status = 'requested' AND received_by IS NULL AND received_at IS NULL) OR (status = 'confirmed' AND received_by IS NOT NULL AND received_at IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE return_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    return_record_id BIGINT UNSIGNED NOT NULL,
    handover_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    good_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    damaged_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    missing_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    destination_location_id BIGINT UNSIGNED NULL,
    FOREIGN KEY (return_record_id) REFERENCES return_records (id),
    FOREIGN KEY (handover_item_id) REFERENCES handover_items (id),
    FOREIGN KEY (destination_location_id) REFERENCES locations (id),
    CHECK (good_quantity + damaged_quantity + missing_quantity > 0),
    CHECK ((good_quantity + damaged_quantity = 0) OR destination_location_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE loss_cases (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    return_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    quantity INT UNSIGNED NOT NULL,
    status VARCHAR(32) NOT NULL,
    recovered_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    written_off_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    FOREIGN KEY (return_item_id) REFERENCES return_items (id),
    FOREIGN KEY (resolved_by) REFERENCES users (id),
    CHECK (quantity > 0),
    CHECK (recovered_quantity + written_off_quantity <= quantity),
    CHECK (status <> 'closed' OR (recovered_quantity + written_off_quantity = quantity AND resolved_at IS NOT NULL AND resolved_by IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE damage_reports (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    equipment_type_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    source_balance_id BIGINT UNSIGNED NULL,
    handover_item_id BIGINT UNSIGNED NULL,
    return_item_id BIGINT UNSIGNED NULL,
    reporter_id BIGINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(32) NOT NULL,
    reported_at DATETIME NOT NULL,
    FOREIGN KEY (equipment_type_id) REFERENCES equipment_types (id),
    FOREIGN KEY (source_balance_id) REFERENCES stock_balances (id),
    FOREIGN KEY (handover_item_id) REFERENCES handover_items (id),
    FOREIGN KEY (return_item_id) REFERENCES return_items (id),
    FOREIGN KEY (reporter_id) REFERENCES users (id),
    CHECK (quantity > 0),
    CHECK (source_balance_id IS NOT NULL OR handover_item_id IS NOT NULL OR return_item_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE maintenance_jobs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    damage_report_id BIGINT UNSIGNED NOT NULL,
    equipment_type_id BIGINT UNSIGNED NOT NULL,
    source_balance_id BIGINT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL,
    technician_id BIGINT UNSIGNED NULL,
    planned_at DATETIME NULL,
    started_at DATETIME NULL,
    accepted_at DATETIME NULL,
    status VARCHAR(32) NOT NULL,
    repaired_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    damaged_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    retired_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    cost_amount DECIMAL(12,2) NULL,
    FOREIGN KEY (damage_report_id) REFERENCES damage_reports (id),
    FOREIGN KEY (equipment_type_id) REFERENCES equipment_types (id),
    FOREIGN KEY (source_balance_id) REFERENCES stock_balances (id),
    FOREIGN KEY (technician_id) REFERENCES users (id),
    CHECK (quantity > 0),
    CHECK (repaired_quantity + damaged_quantity + retired_quantity <= quantity),
    CHECK (accepted_at IS NULL OR (started_at IS NOT NULL AND accepted_at >= started_at AND repaired_quantity + damaged_quantity + retired_quantity = quantity)),
    CHECK (cost_amount IS NULL OR cost_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inventory_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    location_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    started_at DATETIME NOT NULL,
    closed_at DATETIME NULL,
    status VARCHAR(32) NOT NULL,
    FOREIGN KEY (location_id) REFERENCES locations (id),
    FOREIGN KEY (created_by) REFERENCES users (id),
    INDEX (location_id, started_at),
    CHECK ((status = 'draft' AND closed_at IS NULL) OR (status = 'closed' AND closed_at IS NOT NULL AND closed_at >= started_at))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inventory_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    equipment_type_id BIGINT UNSIGNED NOT NULL,
    physical_condition VARCHAR(32) NOT NULL,
    expected_quantity INT UNSIGNED NOT NULL,
    counted_quantity INT UNSIGNED NULL,
    counted_by BIGINT UNSIGNED NULL,
    counted_at DATETIME NULL,
    resolution_status VARCHAR(255) NOT NULL,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    FOREIGN KEY (session_id) REFERENCES inventory_sessions (id),
    FOREIGN KEY (equipment_type_id) REFERENCES equipment_types (id),
    FOREIGN KEY (counted_by) REFERENCES users (id),
    FOREIGN KEY (resolved_by) REFERENCES users (id),
    UNIQUE (session_id, equipment_type_id, physical_condition),
    CHECK (physical_condition IN ('good','damaged','unknown')),
    CHECK ((counted_quantity IS NULL AND counted_at IS NULL AND counted_by IS NULL) OR (counted_quantity IS NOT NULL AND counted_at IS NOT NULL AND counted_by IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NULL,
    subject_type VARCHAR(32) NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(32) NOT NULL,
    details_json JSON NULL,
    happened_at DATETIME NOT NULL,
    FOREIGN KEY (actor_id) REFERENCES users (id),
    INDEX (subject_type, subject_id, happened_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    from_balance_id BIGINT UNSIGNED NULL,
    to_balance_id BIGINT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL,
    event_kind VARCHAR(32) NOT NULL,
    handover_item_id BIGINT UNSIGNED NULL,
    return_item_id BIGINT UNSIGNED NULL,
    damage_report_id BIGINT UNSIGNED NULL,
    maintenance_job_id BIGINT UNSIGNED NULL,
    inventory_item_id BIGINT UNSIGNED NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    idempotency_key VARCHAR(255) NOT NULL UNIQUE,
    happened_at DATETIME NOT NULL,
    FOREIGN KEY (from_balance_id) REFERENCES stock_balances (id),
    FOREIGN KEY (to_balance_id) REFERENCES stock_balances (id),
    FOREIGN KEY (handover_item_id) REFERENCES handover_items (id),
    FOREIGN KEY (return_item_id) REFERENCES return_items (id),
    FOREIGN KEY (damage_report_id) REFERENCES damage_reports (id),
    FOREIGN KEY (maintenance_job_id) REFERENCES maintenance_jobs (id),
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items (id),
    FOREIGN KEY (actor_id) REFERENCES users (id),
    INDEX (happened_at),
    CHECK (quantity > 0),
    CHECK (from_balance_id IS NOT NULL OR to_balance_id IS NOT NULL),
    CHECK (from_balance_id IS NULL OR to_balance_id IS NULL OR from_balance_id <> to_balance_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
