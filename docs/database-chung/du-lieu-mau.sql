SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO roles (code, name) VALUES ('staff', 'Người phụ trách');
INSERT INTO users (role_id, login, full_name, password_hash, is_active)
VALUES (LAST_INSERT_ID(), 'sample_opening', 'Nhập tồn minh họa', '!', FALSE);
SET @actor = LAST_INSERT_ID();

INSERT INTO rooms (code, name) VALUES ('P-MAU', 'Phòng thực hành mẫu');
SET @room = LAST_INSERT_ID();
INSERT INTO locations (room_id, code, name) VALUES (@room, 'TU-A', 'Tủ A');
SET @a = LAST_INSERT_ID();
INSERT INTO locations (room_id, code, name) VALUES (@room, 'TU-B', 'Tủ B');
SET @b = LAST_INSERT_ID();

INSERT INTO equipment_types (code, name, category, qr_code)
VALUES ('TB-L001', 'Micro không dây cầm tay', 'Thiết bị âm thanh', 'TB-L001');
SET @mic = LAST_INSERT_ID();
INSERT INTO equipment_types (code, name, category, qr_code)
VALUES ('TB-L002', 'Đầu chuyển USB-C sang USB-A', 'Phụ kiện kết nối', 'TB-L002');
SET @adapter = LAST_INSERT_ID();

INSERT INTO stock_balances (equipment_type_id, location_id, physical_condition, use_state, quantity)
VALUES (@mic, @a, 'good', 'ready', 7), (@mic, @b, 'good', 'ready', 3), (@adapter, @a, 'good', 'ready', 5);

INSERT INTO stock_movements (to_balance_id, quantity, event_kind, actor_id, idempotency_key, happened_at)
SELECT id, quantity, 'opening', @actor, CONCAT('sample-opening-', id), UTC_TIMESTAMP()
FROM stock_balances
WHERE physical_condition = 'good' AND use_state = 'ready'
  AND ((equipment_type_id = @mic AND location_id IN (@a, @b)) OR (equipment_type_id = @adapter AND location_id = @a));

INSERT INTO activity_logs (actor_id, subject_type, subject_id, action, happened_at)
SELECT @actor, 'stock_balance', to_balance_id, 'opening', happened_at
FROM stock_movements WHERE actor_id = @actor AND event_kind = 'opening';
COMMIT;
