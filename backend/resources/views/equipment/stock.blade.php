<div class="scroll"><table>
    <thead><tr>
        @if($showType)<th>Mã loại</th><th>Thiết bị</th>@endif
        <th>Phòng / vị trí</th><th class="num">Tại vị trí</th><th class="num">Sẵn sàng</th><th class="num">Đang mượn</th><th class="num">Bảo trì</th>
    </tr></thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @if($showType)<td class="code">{{ $row->code }}</td><td><a href="/equipment/{{ $row->id }}">{{ $row->name }}</a><small>{{ $row->category }}</small></td>@endif
                <td>{{ $row->room }}<small>{{ $row->location }}</small></td>
                <td class="num">{{ $row->on_site }}</td><td class="num">{{ $row->ready }}</td><td class="num">{{ $row->loaned }}</td><td class="num">{{ $row->maintenance }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $showType ? 7 : 5 }}">Chưa có dữ liệu phù hợp.</td></tr>
        @endforelse
    </tbody>
</table></div>
