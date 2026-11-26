<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController
{
    public function index(Request $request)
    {
        $sessions = DB::table('inventory_sessions as s')
            ->join('locations as l', 'l.id', '=', 's.location_id')
            ->join('rooms as r', 'r.id', '=', 'l.room_id')
            ->leftJoin('users as u', 'u.id', '=', 's.created_by')
            ->leftJoin('inventory_items as i', 'i.session_id', '=', 's.id')
            ->groupBy('s.id', 's.started_at', 's.closed_at', 's.status', 'r.name', 'l.name', 'u.full_name')
            ->orderByDesc('s.started_at')
            ->orderByDesc('s.id')
            ->select('s.id', 's.started_at', 's.closed_at', 's.status', 'r.name as room', 'l.name as location', 'u.full_name as creator')
            ->selectRaw('COUNT(i.id) AS total_items,
                SUM(CASE WHEN i.counted_quantity IS NOT NULL THEN 1 ELSE 0 END) AS counted_items,
                SUM(CASE WHEN i.counted_quantity IS NOT NULL AND i.counted_quantity <> i.expected_quantity THEN 1 ELSE 0 END) AS discrepancy_items')
            ->get();

        $locations = DB::table('locations as l')
            ->join('rooms as r', 'r.id', '=', 'l.room_id')
            ->where('l.is_active', true)
            ->where('r.is_active', true)
            ->orderBy('r.name')
            ->orderBy('l.name')
            ->get(['l.id', 'l.name', 'r.name as room']);

        return view('inventory.index', [
            'sessions' => $sessions,
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canManageEquipment(), 403);
        $data = $request->validate(['location_id' => 'required|integer|min:1']);

        $sessionId = DB::transaction(function () use ($request, $data) {
            $location = DB::table('locations as l')
                ->join('rooms as r', 'r.id', '=', 'l.room_id')
                ->where('l.id', $data['location_id'])
                ->where('l.is_active', true)
                ->where('r.is_active', true)
                ->lockForUpdate()
                ->first(['l.id']);

            if (!$location) {
                throw ValidationException::withMessages(['location_id' => 'Vị trí đã ngừng sử dụng hoặc không tồn tại.']);
            }

            $snapshot = DB::table('stock_balances')
                ->where('location_id', $location->id)
                ->whereIn('use_state', ['ready', 'blocked'])
                ->where('quantity', '>', 0)
                ->groupBy('equipment_type_id', 'physical_condition')
                ->orderBy('equipment_type_id')
                ->orderBy('physical_condition')
                ->select('equipment_type_id', 'physical_condition')
                ->selectRaw('SUM(quantity) AS expected_quantity')
                ->get();

            if ($snapshot->isEmpty()) {
                throw ValidationException::withMessages(['location_id' => 'Vị trí này hiện chưa có thiết bị nào trong phạm vi kiểm kê nên không thể lập đợt trống.']);
            }

            $now = now('UTC');
            $sessionId = DB::table('inventory_sessions')->insertGetId([
                'location_id' => $location->id,
                'created_by' => $request->user()->id,
                'started_at' => $now,
                'closed_at' => null,
                'status' => 'draft',
            ]);

            $items = [];
            $totalExpected = 0;
            foreach ($snapshot as $row) {
                $expected = (int) $row->expected_quantity;
                $totalExpected += $expected;
                $items[] = [
                    'session_id' => $sessionId,
                    'equipment_type_id' => $row->equipment_type_id,
                    'physical_condition' => $row->physical_condition,
                    'expected_quantity' => $expected,
                    'counted_quantity' => null,
                    'counted_by' => null,
                    'counted_at' => null,
                    'resolution_status' => 'pending',
                    'resolved_by' => null,
                    'resolved_at' => null,
                ];
            }
            DB::table('inventory_items')->insert($items);

            DB::table('activity_logs')->insert([
                'actor_id' => $request->user()->id,
                'subject_type' => 'inventory_session',
                'subject_id' => $sessionId,
                'action' => 'create',
                'details_json' => json_encode([
                    'location_id' => $location->id,
                    'item_count' => count($items),
                    'total_expected' => $totalExpected,
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'happened_at' => $now,
            ]);

            return $sessionId;
        });

        return redirect('/inventory-sessions/'.$sessionId)->with('status', 'Đã lập đợt kiểm kê và chụp số hồ sơ tại vị trí.');
    }

    public function show(Request $request, int $id)
    {
        $session = DB::table('inventory_sessions as s')
            ->join('locations as l', 'l.id', '=', 's.location_id')
            ->join('rooms as r', 'r.id', '=', 'l.room_id')
            ->leftJoin('users as u', 'u.id', '=', 's.created_by')
            ->where('s.id', $id)
            ->first(['s.*', 'r.name as room', 'l.name as location', 'u.full_name as creator']);

        abort_unless($session, 404);

        $items = DB::table('inventory_items as i')
            ->join('equipment_types as e', 'e.id', '=', 'i.equipment_type_id')
            ->leftJoin('users as u', 'u.id', '=', 'i.counted_by')
            ->where('i.session_id', $id)
            ->orderBy('e.name')
            ->orderBy('i.physical_condition')
            ->get([
                'i.*',
                'e.code',
                'e.name',
                'e.qr_code',
                'u.full_name as counter_name',
            ])
            ->map(function ($item) {
                $item->revision = $this->itemRevision($item);
                $item->difference = $item->counted_quantity === null
                    ? null
                    : (int) $item->counted_quantity - (int) $item->expected_quantity;
                return $item;
            });

        $locationBalanceIds = DB::table('stock_balances')
            ->where('location_id', $session->location_id)
            ->select('id');

        $movementsQuery = DB::table('stock_movements as m')
            ->leftJoin('stock_balances as bf', 'bf.id', '=', 'm.from_balance_id')
            ->leftJoin('stock_balances as bt', 'bt.id', '=', 'm.to_balance_id')
            ->leftJoin('equipment_types as ef', 'ef.id', '=', 'bf.equipment_type_id')
            ->leftJoin('equipment_types as et', 'et.id', '=', 'bt.equipment_type_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.actor_id')
            ->where(function ($query) use ($locationBalanceIds) {
                $query->whereIn('m.from_balance_id', $locationBalanceIds)
                    ->orWhereIn('m.to_balance_id', $locationBalanceIds);
            })
            ->where('m.happened_at', '>=', $session->started_at);

        if ($session->closed_at !== null) {
            $movementsQuery->where('m.happened_at', '<=', $session->closed_at);
        }

        $movements = $movementsQuery
            ->orderByDesc('m.happened_at')
            ->orderByDesc('m.id')
            ->get([
                'm.id',
                'm.event_kind',
                'm.quantity',
                'm.happened_at',
                'u.full_name as actor_name',
                DB::raw('COALESCE(et.name, ef.name) AS equipment_name'),
                DB::raw('COALESCE(et.code, ef.code) AS equipment_code'),
            ]);

        $highlight = $request->integer('highlight') ?: null;

        return view('inventory.show', [
            'session' => $session,
            'items' => $items,
            'movements' => $movements,
            'highlight' => $highlight,
        ]);
    }

    public function updateItem(Request $request, int $id, int $itemId)
    {
        abort_unless($request->user()->canManageEquipment(), 403);
        $data = $request->validate([
            'counted_quantity' => 'nullable|integer|min:0|max:4294967295',
            'revision' => 'nullable|string|size:64',
        ]);

        DB::transaction(function () use ($request, $data, $id, $itemId) {
            $session = DB::table('inventory_sessions')->where('id', $id)->lockForUpdate()->first();
            abort_unless($session, 404);

            if ($session->status !== 'draft') {
                throw ValidationException::withMessages(['counted_quantity' => 'Đợt kiểm kê đã hoàn tất nên không thể chỉnh sửa số thực đếm.']);
            }

            $item = DB::table('inventory_items')
                ->where('id', $itemId)
                ->where('session_id', $id)
                ->lockForUpdate()
                ->first();
            abort_unless($item, 404);

            if (isset($data['revision']) && !hash_equals($this->itemRevision($item), $data['revision'])) {
                throw ValidationException::withMessages(['revision' => 'Dòng kiểm kê này vừa được cập nhật ở phiên khác. Tải lại trang để xem bản mới nhất.']);
            }

            if ($data['counted_quantity'] === null) {
                DB::table('inventory_items')->where('id', $item->id)->update([
                    'counted_quantity' => null,
                    'counted_by' => null,
                    'counted_at' => null,
                    'resolution_status' => 'pending',
                ]);
                return;
            }

            $counted = (int) $data['counted_quantity'];
            $status = $counted === (int) $item->expected_quantity ? 'matched' : 'discrepancy';
            DB::table('inventory_items')->where('id', $item->id)->update([
                'counted_quantity' => $counted,
                'counted_by' => $request->user()->id,
                'counted_at' => now('UTC'),
                'resolution_status' => $status,
            ]);
        });

        return redirect('/inventory-sessions/'.$id)->with('status', 'Đã lưu số thực đếm.');
    }

    public function close(Request $request, int $id)
    {
        abort_unless($request->user()->canManageEquipment(), 403);

        $alreadyClosed = DB::transaction(function () use ($request, $id) {
            $session = DB::table('inventory_sessions')->where('id', $id)->lockForUpdate()->first();
            abort_unless($session, 404);

            if ($session->status === 'closed') {
                return true;
            }

            $items = DB::table('inventory_items')
                ->where('session_id', $id)
                ->lockForUpdate()
                ->get(['id', 'expected_quantity', 'counted_quantity']);

            $uncounted = $items->whereNull('counted_quantity')->count();
            if ($uncounted > 0) {
                throw ValidationException::withMessages([
                    'session' => 'Còn '.$uncounted.' dòng chưa nhập số thực đếm. Nhập 0 nếu không thấy chiếc nào trước khi hoàn tất.',
                ]);
            }

            $discrepancies = $items->filter(fn ($row) => (int) $row->counted_quantity !== (int) $row->expected_quantity)->count();
            $now = now('UTC');

            DB::table('inventory_sessions')->where('id', $id)->update([
                'status' => 'closed',
                'closed_at' => $now,
            ]);

            DB::table('activity_logs')->insert([
                'actor_id' => $request->user()->id,
                'subject_type' => 'inventory_session',
                'subject_id' => $id,
                'action' => 'close',
                'details_json' => json_encode([
                    'location_id' => $session->location_id,
                    'total_items' => $items->count(),
                    'discrepancy_items' => $discrepancies,
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'happened_at' => $now,
            ]);

            return false;
        });

        $message = $alreadyClosed
            ? 'Đợt kiểm kê này đã được hoàn tất trước đó.'
            : 'Đã hoàn tất đợt kiểm kê. Tồn kho không tự thay đổi.';

        return redirect('/inventory-sessions/'.$id)->with('status', $message);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:128',
            'session_id' => 'nullable|integer|min:1',
        ]);

        $needle = trim($data['code']);
        $equipment = DB::table('equipment_types')
            ->where('qr_code', $needle)
            ->orWhere('code', $needle)
            ->first(['id', 'code', 'name']);

        if (!$equipment) {
            return back()->withErrors(['code' => 'Không tìm thấy loại thiết bị khớp mã "'.$needle.'".'])->withInput();
        }

        if (!empty($data['session_id'])) {
            $sessionId = (int) $data['session_id'];
            $inSession = DB::table('inventory_items')
                ->where('session_id', $sessionId)
                ->where('equipment_type_id', $equipment->id)
                ->exists();

            if (!$inSession) {
                return redirect('/inventory-sessions/'.$sessionId)
                    ->withErrors(['code' => 'Loại "'.$equipment->name.'" ('.$equipment->code.') không nằm trong phạm vi đợt kiểm kê này.']);
            }

            return redirect('/inventory-sessions/'.$sessionId.'?highlight='.$equipment->id)
                ->with('status', 'Đã định vị loại "'.$equipment->name.'" ('.$equipment->code.').');
        }

        return redirect('/equipment/'.$equipment->id);
    }

    private function itemRevision(object $item): string
    {
        return hash('sha256', json_encode([
            $item->counted_quantity,
            $item->counted_by,
            $item->counted_at,
            $item->resolution_status,
        ], JSON_THROW_ON_ERROR));
    }
}
