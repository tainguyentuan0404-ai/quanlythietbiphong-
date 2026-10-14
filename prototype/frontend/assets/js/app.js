/**
 * JAVASCRIPT DÙNG CHUNG - LABDEVICE HUB (CLEAN & MODERN)
 * Phục vụ tương tác mô phỏng trên giao diện (Frontend Prototype)
 */

document.addEventListener('DOMContentLoaded', function () {
  initSidebar();
  initToast();
  initMockForms();
  initTableSearchAndFilters();
  initMockActions();
});

/**
 * 1. Khởi tạo Sidebar (Thu gọn trên mobile và desktop)
 */
function initSidebar() {
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.getElementById('appSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      if (backdrop) backdrop.classList.toggle('show');
    });
  }

  if (backdrop && sidebar) {
    backdrop.addEventListener('click', function () {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    });
  }
}

/**
 * 2. Hiển thị Toast thông báo tương tác mô phỏng
 */
function showToast(title, message, type = 'success') {
  const toastEl = document.getElementById('mockToast');
  if (!toastEl) return;

  const titleEl = document.getElementById('mockToastTitle');
  const bodyEl = document.getElementById('mockToastBody');
  const iconEl = document.getElementById('mockToastIcon');

  if (titleEl) titleEl.textContent = title;
  if (bodyEl) bodyEl.textContent = message;

  if (iconEl) {
    iconEl.className = 'me-2';
    if (type === 'success') {
      iconEl.className += ' bi bi-check-circle-fill text-success';
    } else if (type === 'warning') {
      iconEl.className += ' bi bi-exclamation-triangle-fill text-warning';
    } else if (type === 'danger') {
      iconEl.className += ' bi bi-x-circle-fill text-danger';
    } else {
      iconEl.className += ' bi bi-info-circle-fill text-primary';
    }
  }

  const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
  toast.show();
}

/**
 * 3. Xử lý gửi Form mô phỏng
 */
function initMockForms() {
  const forms = document.querySelectorAll('form[data-mock-form]');

  forms.forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
      }

      const formName = form.getAttribute('data-mock-form') || 'biểu mẫu';
      const actionType = form.getAttribute('data-mock-action') || 'Lưu dữ liệu';

      // Ẩn modal nếu form nằm trong modal
      const modalEl = form.closest('.modal');
      if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
          modalInstance.hide();
        }
      }

      showToast(
        'Chưa lưu dữ liệu',
        `Thông tin ${formName} hợp lệ. Chức năng "${actionType}" chưa được kết nối.`,
        'info'
      );

      form.reset();
      form.classList.remove('was-validated');
    });
  });
}

/**
 * 4. Tìm kiếm và lọc dữ liệu trên bảng (Hỗ trợ URL search parameters)
 */
function initTableSearchAndFilters() {
  const searchInput = document.getElementById('tableSearchInput');
  const filterSelects = document.querySelectorAll('.table-filter-select');
  const resetBtn = document.getElementById('resetFilterBtn');
  const table = document.querySelector('.custom-table');

  if (!table) return;

  const rows = table.querySelectorAll('tbody tr[data-search-row]');
  const countBadge = document.getElementById('itemCountBadge');
  const emptyStateRow = document.getElementById('emptyStateRow');

  function applyFilters() {
    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
    let visibleCount = 0;

    // Lấy giá trị của từng filter select
    const activeFilters = {};
    filterSelects.forEach(sel => {
      const field = sel.getAttribute('data-filter-field');
      const val = sel.value;
      if (field && val && val !== 'all') {
        activeFilters[field] = val.toLowerCase();
      }
    });

    rows.forEach(row => {
      let matchesKeyword = true;
      let matchesFilters = true;

      // Kiểm tra từ khóa
      if (keyword) {
        const searchableText = row.getAttribute('data-search-row').toLowerCase();
        matchesKeyword = searchableText.includes(keyword);
      }

      // Kiểm tra bộ lọc thuộc tính
      for (const [field, requiredVal] of Object.entries(activeFilters)) {
        const rowVal = (row.getAttribute(`data-filter-${field}`) || '').toLowerCase();
        if (rowVal !== requiredVal) {
          matchesFilters = false;
          break;
        }
      }

      if (matchesKeyword && matchesFilters) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Cập nhật số lượng
    if (countBadge) {
      countBadge.textContent = `${visibleCount} / ${rows.length}`;
    }

    // Hiển thị trạng thái rỗng nếu visibleCount = 0
    if (emptyStateRow) {
      emptyStateRow.style.display = visibleCount === 0 ? '' : 'none';
    }
  }

  // Đọc tham số URL khi tải trang (ví dụ ?status=pending hoặc ?status=overdue)
  const urlParams = new URLSearchParams(window.location.search);
  const statusParam = urlParams.get('status');
  if (statusParam) {
    filterSelects.forEach(sel => {
      if (sel.getAttribute('data-filter-field') === 'status') {
        const hasOption = Array.from(sel.options).some(opt => opt.value.toLowerCase() === statusParam.toLowerCase());
        if (hasOption) {
          sel.value = statusParam;
        }
      }
    });
  }

  // Áp dụng bộ lọc ngay lúc khởi động
  applyFilters();

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  filterSelects.forEach(sel => {
    sel.addEventListener('change', applyFilters);
  });

  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      if (searchInput) searchInput.value = '';
      filterSelects.forEach(sel => sel.value = 'all');
      applyFilters();
      showToast('Đặt lại bộ lọc', 'Đã khôi phục toàn bộ danh sách hiển thị ban đầu.', 'info');
    });
  }
}

/**
 * 5. Các nút thao tác nhanh mẫu (Xem chi tiết, Khóa, Duyệt, v.v.)
 */
function initMockActions() {
  document.querySelectorAll('[data-mock-action-btn]').forEach(btn => {
    btn.addEventListener('click', function () {
      const action = this.getAttribute('data-mock-action-btn');
      const targetId = this.getAttribute('data-mock-id') || 'bản ghi';

      if (action === 'delete' || action === 'lock') {
        if (confirm(`Bạn có chắc muốn ${action === 'delete' ? 'xóa' : 'khóa'} ${targetId} không?`)) {
          showToast('Chưa cập nhật', `Chức năng này chưa được kết nối. ${targetId} chưa thay đổi.`, 'info');
        }
      } else if (action === 'approve') {
        showToast('Chưa phê duyệt', `Chức năng phê duyệt chưa được kết nối. Phiếu ${targetId} chưa thay đổi.`, 'info');
      } else if (action === 'return') {
        showToast('Chưa xác nhận trả', `Chức năng nhận trả chưa được kết nối. Phiếu ${targetId} chưa thay đổi.`, 'info');
      } else if (action === 'scan-qr') {
        showToast('Chưa quét mã QR', 'Camera chưa được kết nối. Chưa có kết quả kiểm kê mới.', 'info');
      }
    });
  });
}
