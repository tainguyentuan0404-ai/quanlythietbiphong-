<?php

namespace App;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class EquipmentStock
{
    public static function byLocation(): Builder
    {
        $loans = DB::table('handover_items as hi')
            ->leftJoin('return_records as rr', function ($join) {
                $join->on('rr.handover_id', '=', 'hi.handover_id')->where('rr.status', 'confirmed');
            })
            ->whereNull('rr.id')
            ->groupBy('hi.source_balance_id')
            ->selectRaw('hi.source_balance_id, SUM(hi.quantity) AS outstanding');

        return DB::table('stock_balances as b')
            ->join('locations as l', 'l.id', '=', 'b.location_id')
            ->join('rooms as r', 'r.id', '=', 'l.room_id')
            ->join('equipment_types as e', 'e.id', '=', 'b.equipment_type_id')
            ->leftJoinSub($loans, 'loans', 'loans.source_balance_id', '=', 'b.id')
            ->groupBy('e.id', 'e.code', 'e.name', 'e.category', 'r.id', 'r.name', 'l.id', 'l.name')
            ->select('e.id', 'e.code', 'e.name', 'e.category', 'r.name as room', 'l.name as location')
            ->selectRaw("SUM(b.quantity) AS on_site,
                SUM(CASE WHEN b.physical_condition = 'good' AND b.use_state = 'ready' THEN b.quantity ELSE 0 END) AS ready,
                SUM(CASE WHEN b.use_state = 'maintenance' THEN b.quantity ELSE 0 END) AS maintenance,
                SUM(COALESCE(loans.outstanding, 0)) AS loaned")
            ->orderBy('e.name')->orderBy('r.name')->orderBy('l.name');
    }
}
