<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InventoryTest extends TestCase
{
    private User $staff;
    private int $roomId;
    private int $locationA;
    private int $locationB;
    private int $emptyLocation;
    private bool $inTransaction = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('lvtn_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        DB::beginTransaction();
        $this->inTransaction = true;
        $this->staff = $this->user('staff');
        $this->roomId = DB::table('rooms')->insertGetId(['code' => 'R-'.Str::ulid(), 'name' => 'Phòng kiểm kê']);
        $this->locationA = DB::table('locations')->insertGetId(['room_id' => $this->roomId, 'code' => 'TU-A', 'name' => 'Tủ A']);
        $this->locationB = DB::table('locations')->insertGetId(['room_id' => $this->roomId, 'code' => 'TU-B', 'name' => 'Tủ B']);
        $this->emptyLocation = DB::table('locations')->insertGetId(['room_id' => $this->roomId, 'code' => 'TU-RONG', 'name' => 'Tủ trống']);
    }

    protected function tearDown(): void
    {
        if ($this->inTransaction) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function user(string $role): User
    {
        DB::table('roles')->insertOrIgnore(['code' => $role, 'name' => $role]);
        $id = DB::table('users')->insertGetId([
            'login' => (string) Str::ulid(),
            'full_name' => 'Nhân viên kiểm kê',
            'password_hash' => Hash::make('mat-khau-chi-danh-cho-test'),
            'role_id' => DB::table('roles')->where('code', $role)->value('id'),
        ]);
        return User::findOrFail($id);
    }

    private function createTypeWithBalances(): int
    {
        $code = 'TB-'.strtoupper((string) Str::ulid());
        $typeId = DB::table('equipment_types')->insertGetId([
            'code' => $code,
            'name' => 'Micro kiểm kê',
            'category' => 'Âm thanh',
            'qr_code' => $code,
        ]);
        DB::table('stock_balances')->insert([
            ['equipment_type_id' => $typeId, 'location_id' => $this->locationA, 'physical_condition' => 'good', 'use_state' => 'ready', 'quantity' => 4],
            ['equipment_type_id' => $typeId, 'location_id' => $this->locationA, 'physical_condition' => 'damaged', 'use_state' => 'blocked', 'quantity' => 1],
            ['equipment_type_id' => $typeId, 'location_id' => $this->locationA, 'physical_condition' => 'damaged', 'use_state' => 'maintenance', 'quantity' => 2],
            ['equipment_type_id' => $typeId, 'location_id' => $this->locationB, 'physical_condition' => 'good', 'use_state' => 'ready', 'quantity' => 3],
        ]);
        return $typeId;
    }

    public function test_staff_can_create_count_zero_clear_close_and_keep_stock_unchanged(): void
    {
        $typeId = $this->createTypeWithBalances();

        $this->actingAs($this->staff)->get('/inventory-sessions')->assertOk()->assertSee('Tủ A');
        $response = $this->post('/inventory-sessions', ['location_id' => $this->locationA]);
        $sessionId = (int) DB::table('inventory_sessions')->where('location_id', $this->locationA)->value('id');
        self::assertGreaterThan(0, $sessionId);
        $response->assertRedirect('/inventory-sessions/'.$sessionId);

        $items = DB::table('inventory_items')->where('session_id', $sessionId)->orderBy('physical_condition')->get();
        self::assertCount(2, $items);
        $damagedItem = $items->firstWhere('physical_condition', 'damaged');
        $goodItem = $items->firstWhere('physical_condition', 'good');
        self::assertSame(1, (int) $damagedItem->expected_quantity);
        self::assertSame(4, (int) $goodItem->expected_quantity);

        $this->post('/inventory-sessions/'.$sessionId.'/close')->assertSessionHasErrors('session');

        $this->patch('/inventory-sessions/'.$sessionId.'/items/'.$damagedItem->id, ['counted_quantity' => 0])
            ->assertRedirect('/inventory-sessions/'.$sessionId);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $damagedItem->id,
            'counted_quantity' => 0,
            'resolution_status' => 'discrepancy',
        ]);

        $this->patch('/inventory-sessions/'.$sessionId.'/items/'.$goodItem->id, ['counted_quantity' => 3])
            ->assertRedirect('/inventory-sessions/'.$sessionId);
        $this->patch('/inventory-sessions/'.$sessionId.'/items/'.$goodItem->id, ['counted_quantity' => ''])
            ->assertRedirect('/inventory-sessions/'.$sessionId);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $goodItem->id,
            'counted_quantity' => null,
            'counted_by' => null,
            'counted_at' => null,
            'resolution_status' => 'pending',
        ]);

        $this->patch('/inventory-sessions/'.$sessionId.'/items/'.$goodItem->id, ['counted_quantity' => 3])
            ->assertRedirect('/inventory-sessions/'.$sessionId);

        $this->post('/inventory-sessions/'.$sessionId.'/close')->assertRedirect('/inventory-sessions/'.$sessionId);
        $this->post('/inventory-sessions/'.$sessionId.'/close')->assertRedirect('/inventory-sessions/'.$sessionId);

        self::assertSame('closed', DB::table('inventory_sessions')->where('id', $sessionId)->value('status'));
        self::assertSame(1, DB::table('activity_logs')
            ->where('subject_type', 'inventory_session')
            ->where('subject_id', $sessionId)
            ->where('action', 'close')
            ->count());

        $this->get('/inventory-sessions/'.$sessionId)->assertOk()->assertSee('-1')->assertSee('Đã hoàn tất');

        self::assertSame(4, (int) DB::table('stock_balances')
            ->where('equipment_type_id', $typeId)
            ->where('location_id', $this->locationA)
            ->where('physical_condition', 'good')
            ->where('use_state', 'ready')
            ->value('quantity'));
        self::assertSame(1, (int) DB::table('stock_balances')
            ->where('equipment_type_id', $typeId)
            ->where('location_id', $this->locationA)
            ->where('physical_condition', 'damaged')
            ->where('use_state', 'blocked')
            ->value('quantity'));
    }

    public function test_closed_session_cross_session_item_stale_revision_and_borrower_are_blocked(): void
    {
        $this->createTypeWithBalances();

        $this->actingAs($this->staff)->post('/inventory-sessions', ['location_id' => $this->locationA])->assertRedirect();
        $sessionA = (int) DB::table('inventory_sessions')->where('location_id', $this->locationA)->value('id');
        $this->post('/inventory-sessions', ['location_id' => $this->locationB])->assertRedirect();
        $sessionB = (int) DB::table('inventory_sessions')->where('location_id', $this->locationB)->value('id');

        $itemB = DB::table('inventory_items')->where('session_id', $sessionB)->first();
        $this->patch('/inventory-sessions/'.$sessionA.'/items/'.$itemB->id, ['counted_quantity' => 1])->assertNotFound();

        $showB = $this->get('/inventory-sessions/'.$sessionB)->assertOk();
        $initialRevision = $showB->viewData('items')->first()->revision;
        $this->patch('/inventory-sessions/'.$sessionB.'/items/'.$itemB->id, [
            'counted_quantity' => 3,
            'revision' => $initialRevision,
        ])->assertRedirect('/inventory-sessions/'.$sessionB);
        $this->patch('/inventory-sessions/'.$sessionB.'/items/'.$itemB->id, [
            'counted_quantity' => 2,
            'revision' => $initialRevision,
        ])->assertSessionHasErrors('revision');

        $this->post('/inventory-sessions/'.$sessionB.'/close')->assertRedirect('/inventory-sessions/'.$sessionB);
        $this->patch('/inventory-sessions/'.$sessionB.'/items/'.$itemB->id, ['counted_quantity' => 1])
            ->assertSessionHasErrors('counted_quantity');

        $borrower = $this->user('borrower');
        $this->actingAs($borrower)->get('/inventory-sessions')->assertOk();
        $this->get('/inventory-sessions/'.$sessionB)->assertOk();
        $this->post('/inventory-sessions', ['location_id' => $this->locationA])->assertForbidden();
        $this->patch('/inventory-sessions/'.$sessionA.'/items/'.$itemB->id, ['counted_quantity' => 1])->assertForbidden();
        $this->post('/inventory-sessions/'.$sessionA.'/close')->assertForbidden();
    }

    public function test_empty_location_movement_visibility_and_qr_lookup(): void
    {
        $typeId = $this->createTypeWithBalances();
        $type = DB::table('equipment_types')->find($typeId);

        $this->actingAs($this->staff)
            ->post('/inventory-sessions', ['location_id' => $this->emptyLocation])
            ->assertSessionHasErrors('location_id');
        self::assertSame(0, DB::table('inventory_sessions')->where('location_id', $this->emptyLocation)->count());

        $this->post('/inventory-sessions', ['location_id' => $this->locationA])->assertRedirect();
        $sessionId = (int) DB::table('inventory_sessions')->where('location_id', $this->locationA)->value('id');

        $balanceId = DB::table('stock_balances')
            ->where('equipment_type_id', $typeId)
            ->where('location_id', $this->locationA)
            ->where('use_state', 'ready')
            ->value('id');
        DB::table('stock_movements')->insert([
            'from_balance_id' => $balanceId,
            'quantity' => 1,
            'event_kind' => 'handover',
            'actor_id' => $this->staff->id,
            'idempotency_key' => 'test-move-'.Str::ulid(),
            'happened_at' => now('UTC')->addSecond(),
        ]);

        $this->get('/inventory-sessions/'.$sessionId)
            ->assertOk()
            ->assertSee('Biến động kho tại vị trí trong thời gian kiểm kê');

        $this->get('/qr?code='.$type->qr_code)->assertRedirect('/equipment/'.$typeId);
        $this->get('/qr?code='.$type->qr_code.'&session_id='.$sessionId)
            ->assertRedirect('/inventory-sessions/'.$sessionId.'?highlight='.$typeId);
        $this->assertDatabaseHas('inventory_items', [
            'session_id' => $sessionId,
            'equipment_type_id' => $typeId,
            'physical_condition' => 'good',
            'counted_quantity' => null,
        ]);

        $this->get('/qr?code=KHONG-TON-TAI')->assertSessionHasErrors('code');
    }
}
