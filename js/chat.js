(function () {
  const API = {
    fetch: 'fetch.php',
    send: 'send.php',
  };

  const els = {
    messages: document.getElementById('messages'),
    form: document.getElementById('sendForm'),
    text: document.getElementById('text'),
    nick: document.getElementById('nick'),
    status: document.getElementById('netStatus'),
    badge: document.getElementById('profileBadge'),
  };

  // Никнейм в localStorage
  const LS_KEY = 'portal_nick';
  els.nick.value = localStorage.getItem(LS_KEY) || '';
  updateBadge();

  els.nick.addEventListener('input', () => {
    localStorage.setItem(LS_KEY, els.nick.value.trim());
    updateBadge();
  });

  function updateBadge() {
    const n = (els.nick.value.trim() || 'Гость');
    els.badge.textContent = (n[0] || 'Г').toUpperCase();
  }

  // Отправка сообщения
  els.form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = els.text.value.trim();
    if (!text) return;

    const form = new FormData();
    form.append('username', els.nick.value.trim() || 'Гость');
    form.append('text', text);

    try {
      els.status.textContent = 'отправка...';
      const r = await fetch(API.send, { method: 'POST', body: form });
      const data = await r.json();
      if (data.ok) {
        els.text.value = '';
      } else {
        alert(data.error || 'Не удалось отправить');
      }
    } catch {
      alert('Сеть недоступна');
    } finally {
      els.status.textContent = 'онлайн';
    }
  });

  // Enter — отправка, Shift+Enter — перенос
  els.text.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      els.form.requestSubmit();
    }
  });

  // Получение новых сообщений (длинный опрос)
  let lastId = 0;
  async function loop() {
    try {
      const r = await fetch(`${API.fetch}?after=${lastId}`);
      const data = await r.json();

      data.messages.forEach(addMsg);
      if (data.last_id) lastId = data.last_id;

      els.status.textContent = 'онлайн';
    } catch {
      els.status.textContent = 'офлайн, переподключение...';
    } finally {
      setTimeout(loop, 1200); // 1.2 c между запросами
    }
  }
  loop();

  // Добавить сообщение в DOM (без innerHTML — безопасно)
  function addMsg(m) {
    const wrap = document.createElement('div');
    wrap.className = 'msg';

    const meta = document.createElement('div');
    meta.className = 'meta';
    const dt = new Date(m.created_at * 1000);
    const hh = String(dt.getHours()).padStart(2, '0');
    const mm = String(dt.getMinutes()).padStart(2, '0');
    meta.textContent = `${m.username} • ${hh}:${mm}`;

    const txt = document.createElement('div');
    txt.className = 'txt';
    txt.textContent = m.text; // безопасно, без HTML

    wrap.appendChild(meta);
    wrap.appendChild(txt);
    els.messages.appendChild(wrap);

    // автопрокрутка
    els.messages.scrollTop = els.messages.scrollHeight;
  }
})();
