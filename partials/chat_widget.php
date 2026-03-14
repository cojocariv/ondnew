<!-- Mini-chat widget -->
<div id="chat-widget" class="fixed bottom-6 right-6 z-50 font-sans">
    <button type="button" id="chat-widget-toggle" aria-label="Deschide chat" class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-blue text-white shadow-lg shadow-primary-blue/40 hover:scale-105 transition-transform focus:outline-none focus:ring-2 focus:ring-primary-blue focus:ring-offset-2">
        <svg id="chat-icon-open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <svg id="chat-icon-close" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <div id="chat-panel" class="hidden absolute bottom-16 right-0 w-[360px] max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden flex flex-col" style="height: 420px;">
        <div class="bg-primary-blue text-white px-4 py-3 flex items-center justify-between shrink-0">
            <span class="font-semibold">Chat</span>
            <button type="button" id="chat-panel-close" class="p-1 rounded hover:bg-white/20" aria-label="Închide">×</button>
        </div>
        <div id="chat-start-form" class="p-4 border-b border-slate-100 shrink-0">
            <p class="text-sm text-slate-600 mb-3">Spune-ne cum te cheamă și cum putem răspunde.</p>
            <input type="text" id="chat-name" placeholder="Nume" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm mb-2 focus:border-primary-blue focus:ring-1 focus:ring-primary-blue" maxlength="150">
            <input type="email" id="chat-email" placeholder="Email" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm mb-3 focus:border-primary-blue focus:ring-1 focus:ring-primary-blue" maxlength="150">
            <button type="button" id="chat-start-btn" class="w-full rounded-xl bg-primary-blue text-white py-2.5 text-sm font-semibold hover:opacity-95">Pornește conversația</button>
        </div>
        <div id="chat-thread-wrap" class="hidden flex-1 flex flex-col min-h-0">
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 text-sm"></div>
            <div class="p-3 border-t border-slate-100 shrink-0">
                <div class="flex gap-2">
                    <textarea id="chat-input" placeholder="Scrie mesajul..." rows="2" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm resize-none focus:border-primary-blue focus:ring-1 focus:ring-primary-blue" maxlength="4000"></textarea>
                    <button type="button" id="chat-send-btn" class="self-end rounded-xl bg-primary-blue text-white px-4 py-2 text-sm font-semibold hover:opacity-95 shrink-0">Trimite</button>
                </div>
            </div>
        </div>
        <div id="chat-error" class="hidden px-4 py-2 bg-red-50 text-red-700 text-sm shrink-0"></div>
    </div>
</div>
<script src="assets/js/chat.js"></script>
