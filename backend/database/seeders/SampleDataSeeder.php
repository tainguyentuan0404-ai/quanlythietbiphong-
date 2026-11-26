<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('roles')->insertOrIgnore([
                ['code' => 'staff', 'name' => 'Người phụ trách'],
                ['code' => 'borrower', 'name' => 'Người mượn'],
            ]);
            $staffRoleId = DB::table('roles')->where('code', 'staff')->value('id');

            DB::table('users')->insertOrIgnore([
                'role_id' => $staffRoleId,
                'login' => 'sample_opening',
                'full_name' => 'Nhập tồn minh họa',
                'password_hash' => '!',
                'is_active' => false,
            ]);
            $actorId = DB::table('users')->where('login', 'sample_opening')->value('id');

            DB::table('rooms')->insertOrIgnore([
                'code' => 'P-MAU',
                'name' => 'Phòng thực hành mẫu',
                'is_active' => true,
            ]);
            $roomId = DB::table('rooms')->where('code', 'P-MAU')->value('id');

            DB::table('locations')->insertOrIgnore([
                ['room_id' => $roomId, 'code' => 'TU-A', 'name' => 'Tủ A', 'is_active' => true],
                ['room_id' => $roomId, 'code' => 'TU-B', 'name' => 'Tủ B', 'is_active' => true],
            ]);
            $locationAId = DB::table('locations')->where('room_id', $roomId)->where('code', 'TU-A')->value('id');
            $locationBId = DB::table('locations')->where('room_id', $roomId)->where('code', 'TU-B')->value('id');

            DB::table('equipment_types')->insertOrIgnore([
                [
                    'code' => 'TB-L001',
                    'name' => 'Micro không dây cầm tay',
                    'category' => 'Thiết bị âm thanh',
                    'specification' => null,
                    'qr_code' => 'TB-L001',
                    'is_active' => true,
                ],
                [
                    'code' => 'TB-L002',
                    'name' => 'Đầu chuyển USB-C sang USB-A',
                    'category' => 'Phụ kiện kết nối',
                    'specification' => null,
                    'qr_code' => 'TB-L002',
                    'is_active' => true,
                ],
            ]);
            $micId = DB::table('equipment_types')->where('code', 'TB-L001')->value('id');
            $adapterId = DB::table('equipment_types')->where('code', 'TB-L002')->value('id');

            $openingBuckets = [
                ['equipment_type_id' => $micId, 'location_id' => $locationAId, 'quantity' => 7],
                ['equipment_type_id' => $micId, 'location_id' => $locationBId, 'quantity' => 3],
                ['equipment_type_id' => $adapterId, 'location_id' => $locationAId, 'quantity' => 5],
            ];

            $now = now('UTC');
            foreach ($openingBuckets as $bucket) {
                $existing = DB::table('stock_balances')
                    ->where('equipment_type_id', $bucket['equipment_type_id'])
                    ->where('location_id', $bucket['location_id'])
                    ->where('physical_condition', 'good')
                    ->where('use_state', 'ready')
                    ->first();

                if ($existing) {
                    $balanceId = $existing->id;
                } else {
                    $balanceId = DB::table('stock_balances')->insertGetId([
                        'equipment_type_id' => $bucket['equipment_type_id'],
                        'location_id' => $bucket['location_id'],
                        'physical_condition' => 'good',
                        'use_state' => 'ready',
                        'quantity' => $bucket['quantity'],
                    ]);
                }

                $idempotencyKey = 'sample-opening-'.$balanceId;
                $inserted = DB::table('stock_movements')->insertOrIgnore([
                    'to_balance_id' => $balanceId,
                    'quantity' => $bucket['quantity'],
                    'event_kind' => 'opening',
                    'actor_id' => $actorId,
                    'idempotency_key' => $idempotencyKey,
                    'happened_at' => $now,
                ]);

                if ($inserted > 0) {
                    DB::table('activity_logs')->insert([
                        'actor_id' => $actorId,
                        'subject_type' => 'stock_balance',
                        'subject_id' => $balanceId,
                        'action' => 'opening',
                        'happened_at' => $now,
                    ]);
                }
            }
        });
    }
}
