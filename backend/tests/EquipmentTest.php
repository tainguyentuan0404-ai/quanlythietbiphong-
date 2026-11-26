<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EquipmentTest extends TestCase
{
    private User $staff;
    private int $location;
    private bool $inTransaction = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('lvtn_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        DB::beginTransaction();
        $this->inTransaction = true;
        $this->staff = $this->user('staff');
        $room = DB::table('rooms')->insertGetId(['code' => (string) Str::ulid(), 'name' => 'Phòng thử nghiệm']);
        $this->location = DB::table('locations')->insertGetId(['room_id' => $room, 'code' => 'A', 'name' => 'Tủ thử nghiệm']);
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
            'login' => (string) Str::ulid(), 'full_name' => 'Người kiểm thử',
            'password_hash' => Hash::make('mat-khau-chi-danh-cho-test'),
            'role_id' => DB::table('roles')->where('code', $role)->value('id'),
        ]);
        return User::findOrFail($id);
    }

    private function input(): array
    {
        return ['token' => (string) Str::ulid(), 'name' => 'Micro bộ môn', 'category' => 'Âm thanh', 'location_id' => $this->location, 'quantity' => 7];
    }

    public function test_staff_can_login_create_search_and_edit_without_changing_stock(): void
    {
        $this->get('/equipment')->assertRedirect('/login');
        $this->post('/login', ['login' => $this->staff->login, 'password' => 'mat-khau-chi-danh-cho-test'])->assertRedirect('/equipment');
        $this->get('/equipment/create')->assertOk()->assertSee('Tủ thử nghiệm');
        $data = $this->input();
        $this->post('/equipment', $data)->assertRedirect();
        $id = DB::table('equipment_types')->where('code', 'TB-'.$data['token'])->value('id');
        self::assertNotNull($id);
        $this->post('/equipment', $data)->assertRedirect('/equipment');
        $this->assertDatabaseHas('stock_balances', ['equipment_type_id' => $id, 'location_id' => $this->location, 'quantity' => 7]);
        self::assertSame(1, DB::table('stock_movements')->where('idempotency_key', 'opening-TB-'.$data['token'])->count());
        $this->get('/equipment?q='.urlencode($data['name']))->assertSee($data['name'])->assertSee('Tủ thử nghiệm');
        $this->get('/equipment?q=no-such-type')->assertDontSee($data['name']);
        $this->get('/equipment?room=0')->assertSessionHasErrors('room');
        $this->get('/equipment/'.$id)->assertOk()->assertSee('Người kiểm thử');
        $edit = $this->get('/equipment/'.$id.'/edit')->assertOk();
        $changes = ['token' => $edit->viewData('token'), 'name' => 'Micro đã sửa', 'quantity' => 500];
        $this->patch('/equipment/'.$id, $changes)->assertRedirect('/equipment/'.$id);
        $this->patch('/equipment/'.$id, $changes)->assertSessionHasErrors('token');
        $this->assertDatabaseHas('equipment_types', ['id' => $id, 'name' => 'Micro đã sửa', 'qr_code' => 'TB-'.$data['token']]);
        $this->assertDatabaseHas('stock_balances', ['equipment_type_id' => $id, 'quantity' => 7]);
    }

    public function test_borrower_and_disabled_user_cannot_write(): void
    {
        $borrower = $this->user('borrower');
        $this->actingAs($borrower)->get('/equipment')->assertOk();
        $this->post('/equipment', $this->input())->assertForbidden();
        $borrower->is_active = false;
        $borrower->save();
        $this->get('/equipment')->assertRedirect('/login');
        $this->post('/login', ['login' => $borrower->login, 'password' => 'mat-khau-chi-danh-cho-test'])->assertSessionHasErrors('login');
    }

    public function test_invalid_quantity_or_inactive_location_creates_no_type(): void
    {
        $data = $this->input();
        $this->actingAs($this->staff)->post('/equipment', array_replace($data, ['quantity' => -1]))->assertSessionHasErrors('quantity');
        DB::table('locations')->where('id', $this->location)->update(['is_active' => false]);
        $this->post('/equipment', $data)->assertSessionHasErrors('location_id');
        $this->assertDatabaseMissing('equipment_types', ['code' => 'TB-'.$data['token']]);
    }
}
