
(() => {
  const KEY = 'labdevice-khang-demo-v2';
  const seed = [
    { id: 'TB-L001', name: 'Micro không dây cầm tay', category: 'Thiết bị âm thanh', room: 'PTN Kỹ thuật điện tử B203', location: 'Tủ thiết bị B203', total: 7, loaned: 2, maintenance: 1 },
    { id: 'TB-L002', name: 'Đầu chuyển USB-C sang USB-A', category: 'Phụ kiện kết nối', room: 'Xưởng thực tập điện tử B201', location: 'Ngăn phụ kiện B201', total: 9, loaned: 1, maintenance: 1 },
    { id: 'TB-L003', name: 'Chuột USB có dây', category: 'Phụ kiện máy tính', room: 'Phòng máy CNTT', location: 'Tủ phụ kiện phòng máy', total: 18, loaned: 2, maintenance: 0 },
    { id: 'TB-L004', name: 'Máy chiếu dự phòng', category: 'Thiết bị trình chiếu', room: 'PTN Kỹ thuật điện tử B203', location: 'Tủ thiết bị B203', total: 4, loaned: 1, maintenance: 1 }
  ];
  const priorSeed = [
    { id: 'TB-L001', name: 'Micro không dây cầm tay', category: 'Thiết bị âm thanh', room: 'Lab 301', location: 'Tủ bục giảng', total: 12, loaned: 2, maintenance: 1 },
    { id: 'TB-L002', name: 'Đầu chuyển USB-C sang USB-A', category: 'Phụ kiện kết nối', room: 'Xưởng Điện tử', location: 'Ngăn phụ kiện E-03', total: 8, loaned: 1, maintenance: 1 },
    { id: 'TB-L003', name: 'Bút trình chiếu không dây', category: 'Thiết bị trình chiếu', room: 'Lab IoT', location: 'Tủ bục giảng', total: 10, loaned: 2, maintenance: 0 },
    { id: 'TB-L004', name: 'Cáp HDMI 2 m', category: 'Phụ kiện kết nối', room: 'Lab 301', location: 'Ngăn phụ kiện 301', total: 8, loaned: 1, maintenance: 1 }
  ];
  const oldSeed = [
    { id: 'TB-L001', name: 'Máy trạm Dell Precision 3660', category: 'Máy tính để bàn', room: 'Lab 301', location: 'Tủ thiết bị A', total: 12, loaned: 2, maintenance: 1 },
    { id: 'TB-L002', name: 'Dao động ký Tektronix TBS1102B', category: 'Thiết bị đo', room: 'Xưởng Điện tử', location: 'Tủ E-03', total: 8, loaned: 1, maintenance: 1 },
    { id: 'TB-L003', name: 'Bộ kit STM32F4 Discovery', category: 'Bộ kit thực hành', room: 'Lab IoT', location: 'Tủ K-12', total: 10, loaned: 2, maintenance: 0 },
    { id: 'TB-L004', name: 'Máy chiếu Panasonic PT-LB386', category: 'Thiết bị trình chiếu', room: 'Lab 301', location: 'Kho phụ B', total: 8, loaned: 1, maintenance: 1 }
  ];
  const clone = value => JSON.parse(JSON.stringify(value));
  function load() {
    try {
      const saved = JSON.parse(localStorage.getItem(KEY));
      if (saved && Array.isArray(saved.equipment) && Array.isArray(saved.rounds) && Array.isArray(saved.events)) {
        if (saved.sampleVersion !== 5) {
          for (const previous of [...oldSeed, ...priorSeed]) {
            const item = saved.equipment.find(entry => entry.id === previous.id && entry.name === previous.name);
            const replacement = seed.find(entry => entry.id === previous.id);
            if (item) for (const field of ['name', 'category', 'room', 'location', 'total', 'loaned', 'maintenance']) {
              if (item[field] === previous[field]) item[field] = replacement[field];
            }
            for (const round of saved.rounds) {
              const counted = round.items?.find(entry => entry.id === previous.id && entry.name === previous.name);
              if (counted) counted.name = replacement.name;
            }
          }
          const testItem = saved.equipment.find(entry => entry.id === 'TB-L005' && entry.name === 'Bộ thử demo Khang đã sửa');
          if (testItem) {
            testItem.name = 'Micro cài áo không dây';
            if (testItem.category === 'Bộ kit thực hành') testItem.category = 'Thiết bị âm thanh';
            if (testItem.location === 'Tủ D-01') testItem.location = 'Tủ bục giảng';
            for (const round of saved.rounds) {
              const counted = round.items?.find(entry => entry.id === testItem.id && entry.name === 'Bộ thử demo Khang đã sửa');
              if (counted) counted.name = testItem.name;
            }
            for (const event of saved.events) if (event.itemId === testItem.id) {
              event.message = event.message.replaceAll('Bộ thử demo Khang đã sửa', testItem.name).replaceAll('Bộ thử demo Khang', testItem.name);
            }
          }
          const sampleMicro = saved.equipment.find(entry => entry.id === 'TB-L005' && entry.name === 'Micro cài áo không dây');
          if (sampleMicro) {
            if (sampleMicro.room === 'Lab 301') sampleMicro.room = seed[0].room;
            if (sampleMicro.location === 'Tủ bục giảng') sampleMicro.location = seed[0].location;
          }
          const roomNames = { 'Lab 301': seed[0].room, 'Xưởng Điện tử': seed[1].room, 'Lab IoT': seed[2].room };
          for (const round of saved.rounds) if (roomNames[round.room]) round.room = roomNames[round.room];
          saved.sampleVersion = 5;
          try { localStorage.setItem(KEY, JSON.stringify(saved)); }
          catch (_) {  }
        }
        return saved;
      }
    } catch (_) {  }
    return { equipment: clone(seed), rounds: [], events: [], activeRoundId: null, sampleVersion: 5 };
  }
  let state = load();
  function save() {
    try { localStorage.setItem(KEY, JSON.stringify(state)); return true; }
    catch (_) { announce('Trình duyệt không cho lưu dữ liệu cục bộ. Thay đổi chỉ tồn tại đến khi tải lại trang.'); return false; }
  }
  const byId = id => document.getElementById(id);
  const ready = item => item.total - item.loaned - item.maintenance;
  const text = (tag, value, className = '') => { const node = document.createElement(tag); node.textContent = String(value); if (className) node.className = className; return node; };
  function fillRooms(select) {
    const selected = select.value;
    select.replaceChildren();
    for (const room of new Set([...seed, ...state.equipment].map(item => item.room))) {
      const option = document.createElement('option'); option.value = room; option.textContent = room; select.appendChild(option);
    }
    if (selected) select.value = selected;
  }
  function announce(message) { const node = byId('khangNotice'); if (node) { node.textContent = message; node.hidden = false; } }
  function addEvent(kind, itemId, message) { state.events.unshift({ kind, itemId, message, at: new Date().toLocaleString('vi-VN') }); }
  function makeDialog() {
    const dialog = document.createElement('dialog'); dialog.className = 'khang-dialog'; dialog.id = 'khangDialog'; document.body.appendChild(dialog);
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    return dialog;
  }
  let dialog;
  function openForm(item = null) {
    const old = item || { name: '', category: '', room: seed[0].room, location: '', total: 0, loaned: 0, maintenance: 0 };
    dialog.replaceChildren();
    const form = document.createElement('form'); form.className = 'khang-form'; form.innerHTML = `<h2>${item ? 'Chỉnh sửa loại thiết bị' : 'Thêm loại thiết bị'}</h2><p>Dữ liệu mẫu được lưu trong trình duyệt này</p><label>Tên thiết bị<input name="name" required maxlength="100"></label><label>Nhóm thiết bị<input name="category" required maxlength="60"></label><label>Phòng<select name="room"></select></label><label>Vị trí lưu trữ<input name="location" required maxlength="80"></label><div class="form-numbers"><label>Tổng quản lý<input name="total" type="number" min="0" step="1" required></label><label>Đang mượn<input name="loaned" type="number" min="0" step="1" required></label><label>Bảo trì<input name="maintenance" type="number" min="0" step="1" required></label></div><p class="form-error" role="alert" hidden></p><div class="dialog-actions"><button type="button" class="btn btn-outline-secondary" data-close>Hủy</button><button type="submit" class="btn btn-primary">${item ? 'Lưu thay đổi' : 'Thêm loại'}</button></div>`;
    fillRooms(form.elements.namedItem('room'));
    for (const [key, value] of Object.entries(old)) if (form.elements.namedItem(key)) form.elements.namedItem(key).value = value;
    form.querySelector('[data-close]').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const data = Object.fromEntries(new FormData(form));
      const nums = ['total', 'loaned', 'maintenance'].map(key => Number(data[key]));
      const error = form.querySelector('.form-error');
      if (nums.some(n => !Number.isSafeInteger(n) || n < 0) || nums[1] + nums[2] > nums[0]) {
        error.textContent = 'Số lượng phải là số nguyên không âm; đang mượn + bảo trì không được vượt tổng.'; error.hidden = false; return;
      }
      if (item) Object.assign(item, { name: data.name.trim(), category: data.category.trim(), room: data.room, location: data.location.trim(), total: nums[0], loaned: nums[1], maintenance: nums[2] });
      else {
        const next = Math.max(0, ...state.equipment.map(e => Number(e.id.slice(4)) || 0)) + 1;
        item = { id: `TB-L${String(next).padStart(3, '0')}`, name: data.name.trim(), category: data.category.trim(), room: data.room, location: data.location.trim(), total: nums[0], loaned: nums[1], maintenance: nums[2] };
        state.equipment.push(item);
      }
      addEvent(item.id === old.id ? 'edit' : 'add', item.id, `${item.id} · ${item.name}`);
      const persisted = save(); dialog.close(); announce(persisted ? `Đã lưu ${item.id} vào dữ liệu mẫu trên trình duyệt.` : 'Trình duyệt không cho lưu dữ liệu cục bộ; thay đổi sẽ mất khi tải lại trang.');
      renderEquipment(); renderDetail(); renderInventory();
    });
    dialog.appendChild(form); dialog.showModal(); form.elements.namedItem('name').focus();
  }
  function openLookup(defaultId = '') {
    dialog.replaceChildren();
    const form = document.createElement('form'); form.className = 'khang-form';
    form.innerHTML = '<h2>Tra mã loại thiết bị</h2><p>Nhập mã in trên nhãn QR. Bản mẫu chưa dùng camera.</p><label>Mã loại<input name="code" required placeholder="Ví dụ: TB-L001"></label><p class="form-error" role="alert" hidden></p><div class="dialog-actions"><button type="button" class="btn btn-outline-secondary" data-close>Đóng</button><button type="submit" class="btn btn-primary">Mở thông tin</button></div>';
    form.elements.namedItem('code').value = defaultId;
    form.querySelector('[data-close]').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', event => {
      event.preventDefault(); const id = form.elements.namedItem('code').value.trim().toUpperCase();
      if (!state.equipment.some(item => item.id === id)) { const error = form.querySelector('.form-error'); error.textContent = 'Không tìm thấy mã loại trong dữ liệu mẫu.'; error.hidden = false; return; }
      location.href = `equipment-detail.html?id=${encodeURIComponent(id)}&v=week3-20261118b`;
    });
    dialog.appendChild(form); dialog.showModal(); form.elements.namedItem('code').focus();
  }
  function renderEquipment() {
    const tbody = byId('equipmentSearch')?.closest('.data-panel')?.querySelector('tbody'); if (!tbody) return;
    const roomFilter = byId('roomFilter'), selected = roomFilter.value;
    roomFilter.replaceChildren(new Option('Tất cả phòng', ''));
    for (const room of new Set(state.equipment.map(item => item.room))) roomFilter.add(new Option(room, room));
    roomFilter.value = selected;
    tbody.replaceChildren();
    for (const item of state.equipment) {
      const row = document.createElement('tr'); row.dataset.equipment = ''; row.dataset.room = item.room;
      const cells = [item.id, item.name, item.room, item.total, ready(item), item.loaned, item.maintenance];
      cells.forEach((value, index) => { const cell = text('td', value, index >= 3 ? 'num' : ''); if (index === 0) cell.classList.add('mono'); if (index === 1) cell.appendChild(text('small', item.category)); if (index === 2) cell.appendChild(text('small', item.location)); row.appendChild(cell); });
      const action = document.createElement('td'); const link = text('a', 'Xem chi tiết'); link.href = `equipment-detail.html?id=${encodeURIComponent(item.id)}&v=week3-20261118b`; action.appendChild(link); row.appendChild(action); tbody.appendChild(row);
    }
    const sum = key => state.equipment.reduce((n, item) => n + (key === 'ready' ? ready(item) : item[key]), 0);
    const values = [state.equipment.length, sum('total'), sum('ready'), sum('loaned') + sum('maintenance')];
    document.querySelectorAll('.kpi strong').forEach((node, i) => { node.textContent = values[i]; });
    filterEquipment();
  }
  function filterEquipment() {
    const search = byId('equipmentSearch'), room = byId('roomFilter'); if (!search || !room) return;
    const term = search.value.trim().toLocaleLowerCase('vi'); const rows = [...document.querySelectorAll('[data-equipment]')]; let shown = 0;
    rows.forEach(row => { const match = row.textContent.toLocaleLowerCase('vi').includes(term) && (!room.value || row.dataset.room === room.value); row.hidden = !match; if (match) shown++; });
    byId('equipmentCount').textContent = `${shown} / ${rows.length} loại`; byId('equipmentEmpty').hidden = shown !== 0;
  }
  function renderDetail() {
    if (!byId('detailContent')) return;
    const id = new URLSearchParams(location.search).get('id'); const item = state.equipment.find(e => e.id === id);
    byId('detailContent').hidden = !item; byId('detailNotFound').hidden = Boolean(item);
    if (!item) return;
    const values = { detailName: item.name, detailCode: `Mã loại ${id} · ${item.room}`, detailId: id, detailCategory: item.category, detailRoom: item.room, detailLocation: item.location, detailTotal: item.total, detailReady: ready(item), detailLoaned: item.loaned, detailMaintenance: item.maintenance };
    for (const [key, value] of Object.entries(values)) byId(key).textContent = String(value);
    const history = byId('detailHistory'); history.replaceChildren();
    const entries = state.events.filter(e => e.itemId === id);
    if (!entries.length) history.textContent = 'Chưa có biến động trong dữ liệu mẫu cục bộ.';
    else entries.forEach(entry => history.appendChild(text('p', `${entry.at} · ${entry.kind === 'inventory' ? 'Kiểm kê' : entry.kind === 'add' ? 'Thêm loại' : 'Chỉnh sửa'} · ${entry.message}`)));
  }
  function currentRound() { return state.rounds.find(round => round.id === state.activeRoundId); }
  function startRound(room) {
    const items = state.equipment.filter(item => item.room === room);
    const id = `KK-${Date.now()}`;
    state.rounds.unshift({ id, room, status: 'draft', at: new Date().toLocaleString('vi-VN'), items: items.map(item => ({ id: item.id, name: item.name, expected: ready(item), actual: null })) });
    state.activeRoundId = id; save(); renderInventory(); announce(`Đã tạo đợt kiểm kê mẫu ${id}.`);
  }
  function chooseRoom() {
    dialog.replaceChildren(); const form = document.createElement('form'); form.className = 'khang-form';
    form.innerHTML = '<h2>Tạo đợt kiểm kê mẫu</h2><p>Chọn phòng để đối chiếu số lượng sẵn sàng tại chỗ.</p><label>Phòng<select name="room"></select></label><div class="dialog-actions"><button type="button" class="btn btn-outline-secondary" data-close>Hủy</button><button type="submit" class="btn btn-primary">Bắt đầu</button></div>';
    fillRooms(form.elements.namedItem('room'));
    form.querySelector('[data-close]').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', event => { event.preventDefault(); const room = form.elements.namedItem('room').value; dialog.close(); startRound(room); });
    dialog.appendChild(form); dialog.showModal();
  }
  function renderInventory() {
    const tbody = document.querySelector('.inventory-table tbody'); if (!tbody) return;
    let round = currentRound();
    if (!round) { startRound(seed[0].room); return; }
    byId('inventory-title').textContent = `Đối chiếu · ${round.room}`;
    tbody.replaceChildren();
    for (const item of round.items) {
      const row = document.createElement('tr'); row.dataset.inventory = ''; row.dataset.itemId = item.id;
      row.appendChild(text('td', item.id, 'mono')); row.appendChild(text('td', item.name)); row.appendChild(text('td', item.expected, 'num'));
      const countCell = document.createElement('td'), label = text('label', `Số thực tế ${item.name}`, 'visually-hidden'), input = document.createElement('input');
      input.type = 'number'; input.min = '0'; input.step = '1'; input.inputMode = 'numeric'; input.className = 'count-input'; input.placeholder = 'Chưa đếm'; input.value = item.actual ?? ''; input.disabled = round.status === 'done'; input.setAttribute('aria-label', `Số thực tế ${item.name}`);
      countCell.append(label, input); row.append(countCell, text('td', '—', 'num difference'), text('td', 'Chưa đối chiếu', 'row-note')); tbody.appendChild(row);
    }
    byId('inventoryFinish').disabled = round.status === 'done'; byId('inventoryReset').disabled = round.status === 'done';
    updateInventory(); renderRoundHistory();
  }
  function updateInventory() {
    const round = currentRound(); if (!round) return;
    let counted = 0, differences = 0;
    document.querySelectorAll('[data-inventory]').forEach(row => {
      const item = round.items.find(i => i.id === row.dataset.itemId), input = row.querySelector('input');
      const valid = input.value !== '' && input.validity.valid && Number.isSafeInteger(Number(input.value)) && Number(input.value) >= 0;
      const diff = valid ? Number(input.value) - item.expected : null;
      row.querySelector('.difference').textContent = diff === null ? '—' : diff > 0 ? `+${diff}` : String(diff);
      row.querySelector('.row-note').textContent = diff === null ? 'Chưa đối chiếu' : diff === 0 ? 'Khớp' : 'Cần đối soát';
      row.classList.toggle('has-difference', diff !== null && diff !== 0);
      if (valid) { counted++; if (diff !== 0) differences++; }
    });
    byId('inventorySummary').textContent = counted ? `Đã đếm ${counted}/${round.items.length} loại · ${differences} loại có chênh lệch · ${round.status === 'done' ? 'đã lưu kết quả mẫu' : 'bản nháp cục bộ'}` : 'Chưa có loại nào được đếm';
  }
  function renderRoundHistory() {
    const host = byId('inventoryHistory'); host.replaceChildren();
    state.rounds.forEach(round => {
      const line = document.createElement('div'); line.className = 'round-line';
      line.appendChild(text('span', `${round.at} · ${round.room} · ${round.status === 'done' ? 'Đã đối soát' : 'Bản nháp'}`));
      const button = text('button', round.id === state.activeRoundId ? 'Đang xem' : 'Xem đợt', 'link-button'); button.type = 'button'; button.disabled = round.id === state.activeRoundId;
      button.addEventListener('click', () => { state.activeRoundId = round.id; save(); renderInventory(); }); line.appendChild(button); host.appendChild(line);
    });
  }
  document.addEventListener('DOMContentLoaded', () => {
    dialog = makeDialog(); renderEquipment(); renderDetail(); renderInventory();
    byId('equipmentSearch')?.addEventListener('input', filterEquipment);
    byId('roomFilter')?.addEventListener('change', filterEquipment);
    byId('equipmentReset')?.addEventListener('click', () => { byId('equipmentSearch').value = ''; byId('roomFilter').value = ''; filterEquipment(); });
    document.querySelectorAll('[data-khang-action]').forEach(button => button.addEventListener('click', () => {
      const action = button.dataset.khangAction;
      if (action === 'add') openForm();
      if (action === 'edit') openForm(state.equipment.find(e => e.id === new URLSearchParams(location.search).get('id')));
      if (action === 'scan' || action === 'qr') openLookup(action === 'qr' ? new URLSearchParams(location.search).get('id') : '');
      if (action === 'new-round') chooseRoom();
    }));
    document.querySelector('.inventory-table')?.addEventListener('input', event => {
      if (!event.target.matches('.count-input')) return;
      const round = currentRound(), item = round.items.find(i => i.id === event.target.closest('[data-inventory]').dataset.itemId);
      item.actual = event.target.value === '' ? null : event.target.validity.valid && Number.isSafeInteger(Number(event.target.value)) ? Number(event.target.value) : null;
      save(); updateInventory();
    });
    byId('inventoryReset')?.addEventListener('click', () => { const round = currentRound(); if (round.status !== 'draft') return; round.items.forEach(i => i.actual = null); save(); renderInventory(); announce('Đã xóa số đếm của bản nháp cục bộ.'); });
    byId('inventoryFinish')?.addEventListener('click', () => {
      const round = currentRound(); if (round.status !== 'draft') return;
      if (round.items.length === 0 || round.items.some(i => i.actual === null || !Number.isSafeInteger(i.actual) || i.actual < 0)) { announce('Hãy nhập số đếm hợp lệ cho tất cả loại thiết bị trước khi hoàn tất.'); return; }
      round.status = 'done'; round.completedAt = new Date().toLocaleString('vi-VN');
      round.items.forEach(i => addEvent('inventory', i.id, `Đếm ${i.actual}; dự kiến ${i.expected}; chênh lệch ${i.actual - i.expected}. Chưa điều chỉnh tồn.`));
      const persisted = save(); renderInventory(); announce(persisted ? 'Đã lưu kết quả đối soát vào dữ liệu mẫu trên trình duyệt. Số tồn không đổi.' : 'Trình duyệt không cho lưu kết quả đối soát; dữ liệu sẽ mất khi tải lại trang. Số tồn không đổi.');
    });
  });
})();
