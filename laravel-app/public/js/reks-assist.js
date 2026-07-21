(function () {
  const messagesEl = document.getElementById('chatMessages');
  const inputEl = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');

  let history = [];

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : null;
  }

  const WONDER_PARK_AVATAR_IMG = `<img src="/images/wonderpark2logo.png" alt="Wonder Park">`;

  function appendMessage(text, sender) {
    const row = document.createElement('div');
    row.className = 'chat-row ' + (sender === 'user' ? 'user' : 'bot');

    if (sender !== 'user') {
      const avatar = document.createElement('span');
      avatar.className = 'chat-avatar';
      avatar.innerHTML = WONDER_PARK_AVATAR_IMG;
      row.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'bubble ' + (sender === 'user' ? 'user' : 'bot');
    bubble.textContent = text;
    row.appendChild(bubble);

    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function appendTyping() {
    const row = document.createElement('div');
    row.className = 'chat-row bot';
    row.id = 'typingIndicator';

    const avatar = document.createElement('span');
    avatar.className = 'chat-avatar';
    avatar.innerHTML = WONDER_PARK_AVATAR_IMG;
    row.appendChild(avatar);

    const bubble = document.createElement('div');
    bubble.className = 'bubble bot';
    bubble.textContent = 'Typing...';
    row.appendChild(bubble);

    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function removeTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
  }

  async function sendMessage(text) {
    if (!text || !text.trim()) return;

    appendMessage(text, 'user');
    inputEl.value = '';
    appendTyping();

    try {
      const res = await fetch('/api/chat/message', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken() || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ message: text, history: history }),
      });

      const data = await res.json();
      removeTyping();
      appendMessage(data.reply, 'bot');

      history.push({ role: 'user', content: text });
      history.push({ role: 'assistant', content: data.reply });
    } catch (err) {
      removeTyping();
      appendMessage("Naku, di ako makaconnect sa server ngayon. Subukan ulit mamaya.", 'bot');
      console.error('Wonder Park chat error:', err);
    }
  }

  sendBtn?.addEventListener('click', () => sendMessage(inputEl.value));
  inputEl?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') sendMessage(inputEl.value);
  });

  document.querySelectorAll('.quick[data-msg]').forEach((el) => {
    el.addEventListener('click', () => sendMessage(el.dataset.msg));
  });
})();
