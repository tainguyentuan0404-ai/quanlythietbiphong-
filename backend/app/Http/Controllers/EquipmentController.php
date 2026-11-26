<?php

namespace App\Http\Controllers;

use App\EquipmentStock;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EquipmentController
{
    private const FIELDS = [
        'name' => 'required|string|max:255',
        'category' => 'nullable|string|max:120',
        'specification' => 'nullable|string|max:5000',
    ];

    public function index(Request $request)
    {
        $filter = $request->validate(['q' => 'nullable|string|max:255', 'room' => 'nullable|integer|min:1']);
        $rows = EquipmentStock::byLocation();
        if (isset($filter['q'])) {
            $rows->where(function ($query) use ($filter) {
                $query->whereLike('e.name', '%'.$filter['q'].'%')->orWhereLike('e.code', '%'.$filter['q'].'%')
                    ->orWhere('e.qr_code', $filter['q']);
            });
        }
        if (isset($filter['room'])) {
            $rows->where('r.id', $filter['room']);
        }
        return view('equipment.index', [
            'rows' => $rows->get(), 'rooms' => DB::table('rooms')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canManageEquipment(), 403);
        $locations = DB::table('locations as l')->join('rooms as r', 'r.id', '=', 'l.room_id')
            ->where('l.is_active', true)->where('r.is_active', true)
            ->orderBy('r.name')->orderBy('l.name')->get(['l.id', 'l.name', 'r.name as room']);
        return view('equipment.form', ['equipment' => null, 'token' => (string) Str::ulid(), 'locations' => $locations]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canManageEquipment(), 403);
        $data = $request->validate(self::FIELDS + [
            'token' => 'required|ulid', 'location_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:0|max:4294967295',
        ]);
        $code = 'TB-'.strtoupper($data['token']);
        try {
            $id = DB::transaction(function () use ($request, $data, $code) {
                $location = DB::table('locations as l')->join('rooms as r', 'r.id', '=', 'l.room_id')
                    ->where('l.id', $data['location_id'])->where('l.is_active', true)->where('r.is_active', true)
                    ->lockForUpdate()->first(['l.id']);
                if (!$location) {
                    throw ValidationException::withMessages(['location_id' => 'Vị trí đã ngừng sử dụng hoặc không tồn tại.']);
                }
                $id = DB::table('equipment_types')->insertGetId($request->only(array_keys(self::FIELDS)) + ['code' => $code, 'qr_code' => $code]);
                $balanceId = DB::table('stock_balances')->insertGetId([
                    'equipment_type_id' => $id, 'location_id' => $location->id,
                    'physical_condition' => 'good', 'use_state' => 'ready', 'quantity' => $data['quantity'],
                ]);
                if ($data['quantity'] > 0) {
                    DB::table('stock_movements')->insert([
                        'to_balance_id' => $balanceId, 'quantity' => $data['quantity'], 'event_kind' => 'opening',
                        'actor_id' => $request->user()->id, 'idempotency_key' => 'opening-'.$code, 'happened_at' => now('UTC'),
                    ]);
                }
                $this->log($id, $request->user()->id, 'create', ['location_id' => $location->id, 'quantity' => (int) $data['quantity']]);
                return $id;
            });
        } catch (UniqueConstraintViolationException $e) {
            if (!DB::table('equipment_types')->where('code', $code)->exists()) {
                throw $e;
            }
            return redirect('/equipment')->with('status', 'Yêu cầu này đã được ghi trước đó.');
        }
        return redirect('/equipment/'.$id)->with('status', 'Đã thêm loại thiết bị.');
    }

    public function show(int $id)
    {
        $equipment = DB::table('equipment_types')->find($id);
        abort_unless($equipment, 404);
        $balanceIds = DB::table('stock_balances')->where('equipment_type_id', $id)->select('id');
        $history = DB::table('activity_logs as a')->leftJoin('users as u', 'u.id', '=', 'a.actor_id')
            ->where(function ($query) use ($id, $balanceIds) {
                $query->where(fn ($q) => $q->where('a.subject_type', 'equipment_type')->where('a.subject_id', $id))
                    ->orWhere(fn ($q) => $q->where('a.subject_type', 'stock_balance')->whereIn('a.subject_id', $balanceIds));
            })->orderByDesc('a.happened_at')->orderByDesc('a.id')->get(['a.action', 'a.happened_at', 'u.full_name', 'a.details_json']);
        return view('equipment.show', [
            'equipment' => $equipment, 'rows' => EquipmentStock::byLocation()->where('e.id', $id)->get(), 'history' => $history,
        ]);
    }

    public function edit(Request $request, int $id)
    {
        abort_unless($request->user()->canManageEquipment(), 403);
        $equipment = DB::table('equipment_types')->find($id);
        abort_unless($equipment, 404);
        return view('equipment.form', ['equipment' => $equipment, 'token' => $this->revision($equipment)]);
    }

    public function update(Request $request, int $id)
    {
        abort_unless($request->user()->canManageEquipment(), 403);
        $data = $request->validate(self::FIELDS + ['token' => 'required|string|size:64']);
        DB::transaction(function () use ($request, $data, $id) {
            $equipment = DB::table('equipment_types')->where('id', $id)->lockForUpdate()->first();
            abort_unless($equipment, 404);
            if (!hash_equals($this->revision($equipment), $data['token'])) {
                throw ValidationException::withMessages(['token' => 'Thông tin đã thay đổi. Mở lại biểu mẫu để xem bản mới nhất.']);
            }
            $changes = $request->only(array_keys(self::FIELDS));
            $old = array_intersect_key((array) $equipment, $changes);
            if ($changes != $old) {
                DB::table('equipment_types')->where('id', $id)->update($changes);
                $this->log($id, $request->user()->id, 'update', ['before' => $old, 'after' => $changes]);
            }
        });
        return redirect('/equipment/'.$id)->with('status', 'Đã lưu thông tin thiết bị.');
    }

    private function revision(object $equipment): string
    {
        return hash('sha256', json_encode([$equipment->name, $equipment->category, $equipment->specification], JSON_THROW_ON_ERROR));
    }

    private function log(int $id, int $actorId, string $action, array $details): void
    {
        DB::table('activity_logs')->insert([
            'actor_id' => $actorId, 'subject_type' => 'equipment_type', 'subject_id' => $id,
            'action' => $action, 'details_json' => json_encode($details, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'happened_at' => now('UTC'),
        ]);
    }
}
