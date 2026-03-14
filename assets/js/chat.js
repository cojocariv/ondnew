(function () {
    var API = '/chat_api.php';
    var COOKIE_NAME = 'chat_conv_id';
    var COOKIE_DAYS = 365;

    function getConvId() {
        var m = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([^;]*)'));
        return m ? parseInt(m[1], 10) : 0;
    }
    function setConvId(id) {
        var d = new Date();
        d.setTime(d.getTime() + COOKIE_DAYS * 24 * 60 * 60 * 1000);
        document.cookie = COOKIE_NAME + '=' + id + ';path=/;max-age=' + (COOKIE_DAYS * 86400) + ';SameSite=Lax';
    }

    var panel = document.getElementById('chat-panel');
    var toggle = document.getElementById('chat-widget-toggle');
    var closeBtn = document.getElementById('chat-panel-close');
    var startForm = document.getElementById('chat-start-form');
    var threadWrap = document.getElementById('chat-thread-wrap');
    var messagesEl = document.getElementById('chat-messages');
    var nameInput = document.getElementById('chat-name');
    var emailInput = document.getElementById('chat-email');
    var startBtn = document.getElementById('chat-start-btn');
    var inputEl = document.getElementById('chat-input');
    var sendBtn = document.getElementById('chat-send-btn');
    var errorEl = document.getElementById('chat-error');
    var iconOpen = document.getElementById('chat-icon-open');
    var iconClose = document.getElementById('chat-icon-close');

    function showError(msg) {
        errorEl.textContent = msg || '';
        errorEl.classList.toggle('hidden', !msg);
    }
    function setOpen(open) {
        if (open) {
            panel.classList.remove('hidden');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }
    }
    function showThread() {
        startForm.classList.add('hidden');
        threadWrap.classList.remove('hidden');
        loadMessages();
    }
    function renderMessage(msg) {
        var isVisitor = msg.sender_type === 'visitor';
        var div = document.createElement('div');
        div.className = 'flex ' + (isVisitor ? 'justify-end' : 'justify-start');
        var bubble = document.createElement('div');
        bubble.className = 'max-w-[85%] rounded-2xl px-4 py-2 ' + (isVisitor ? 'bg-primary-blue text-white' : 'bg-slate-100 text-slate-800');
        bubble.textContent = msg.body;
        div.appendChild(bubble);
        return div;
    }
    function loadMessages() {
        var cid = getConvId();
        if (cid < 1) return;
        fetch(API + '?action=messages&conversation_id=' + cid)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showError('');
                if (data.ok && data.messages) {
                    messagesEl.innerHTML = '';
                    data.messages.forEach(function (m) {
                        messagesEl.appendChild(renderMessage(m));
                    });
                    messagesEl.scrollTop = messagesEl.scrollHeight;
                }
            })
            .catch(function () {
                showError('Nu s-au putut încărca mesajele.');
            });
    }
    function sendMessage() {
        var body = (inputEl && inputEl.value) ? inputEl.value.trim() : '';
        if (!body) return;
        var cid = getConvId();
        if (cid < 1) {
            showError('Pornește mai întâi conversația cu numele și emailul.');
            return;
        }
        var form = new FormData();
        form.append('action', 'send');
        form.append('conversation_id', cid);
        form.append('body', body);
        sendBtn.disabled = true;
        showError('');
        fetch(API, { method: 'POST', body: form })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                sendBtn.disabled = false;
                if (data.ok) {
                    inputEl.value = '';
                    messagesEl.appendChild(renderMessage({ sender_type: 'visitor', body: body }));
                    messagesEl.scrollTop = messagesEl.scrollHeight;
                } else {
                    showError(data.error || 'Eroare la trimitere.');
                }
            })
            .catch(function () {
                sendBtn.disabled = false;
                showError('Eroare la trimitere.');
            });
    }

    if (toggle) toggle.addEventListener('click', function () { setOpen(!panel.classList.contains('hidden')); });
    if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });
    if (startBtn) {
        startBtn.addEventListener('click', function () {
            var name = (nameInput && nameInput.value) ? nameInput.value.trim() : '';
            var email = (emailInput && emailInput.value) ? emailInput.value.trim() : '';
            if (!name || !email) {
                showError('Completează numele și emailul.');
                return;
            }
            showError('');
            var form = new FormData();
            form.append('action', 'start');
            form.append('name', name);
            form.append('email', email);
            startBtn.disabled = true;
            fetch(API, { method: 'POST', body: form })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    startBtn.disabled = false;
                    if (data.ok && data.conversation_id) {
                        setConvId(data.conversation_id);
                        showThread();
                    } else {
                        showError(data.error || 'Eroare.');
                    }
                })
                .catch(function () {
                    startBtn.disabled = false;
                    showError('Eroare la conectare.');
                });
        });
    }
    if (sendBtn) sendBtn.addEventListener('click', sendMessage);
    if (inputEl) {
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    var cid = getConvId();
    if (cid > 0) {
        startForm.classList.add('hidden');
        threadWrap.classList.remove('hidden');
        loadMessages();
    }
})();
