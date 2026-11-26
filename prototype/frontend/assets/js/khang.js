(() => {
  try {
    localStorage.removeItem('labdevice-khang-demo-v2');
  } catch (_) {}

  document.addEventListener('DOMContentLoaded', () => {
    const notice = document.getElementById('khangNotice');
    const announce = message => {
      if (!notice) return;
      notice.textContent = message;
      notice.hidden = false;
    };

    document.querySelectorAll('[data-khang-action]').forEach(button => {
      button.addEventListener('click', () => {
        announce('Trang HTML tĩnh này không dùng dữ liệu ảo. Thao tác thật được thực hiện trên backend Laravel.');
      });
    });
  });
})();
