// Galería de miniaturas en el detalle del producto
document.querySelectorAll('.thumb').forEach(t => t.addEventListener('click', () => { document.getElementById('imgPrincipal').src = t.dataset.full; }));

// Checkout: mostrar/ocultar dirección según la modalidad y los campos de dirección nueva
const form = document.getElementById('formCheckout');
if (form) {
  const bloque = document.getElementById('bloqueDireccion'), sel = document.getElementById('selDir'), nueva = document.getElementById('dirNueva');
  const refrescar = () => {
    const dom = form.querySelector('input[name=modalidad]:checked')?.value === 'domicilio';
    bloque.style.display = dom ? '' : 'none';
    nueva.style.display = (dom && sel.value === 'nueva') ? '' : 'none';
  };
  form.querySelectorAll('input[name=modalidad]').forEach(r => r.addEventListener('change', refrescar));
  sel.addEventListener('change', refrescar); refrescar();
}

// Modo nocturno: botón accesible, recuerda la elección y respeta el tema del sistema si no hay elección
(function () {
  const btn = document.getElementById('btnTema'), raiz = document.documentElement;
  const aplicar = (t, guardar) => {
    raiz.setAttribute('data-bs-theme', t);
    if (guardar) { try { localStorage.setItem('mi-tema', t); } catch (e) {} }
    if (btn) { btn.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false'); btn.setAttribute('aria-label', t === 'dark' ? 'Desactivar modo nocturno' : 'Activar modo nocturno'); }
    const meta = document.querySelector('meta[name=theme-color]'); if (meta) meta.setAttribute('content', t === 'dark' ? '#171320' : '#F98BBF');
  };
  aplicar(raiz.getAttribute('data-bs-theme') || 'light', false);
  if (btn) btn.addEventListener('click', () => aplicar(raiz.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark', true));
  if (window.matchMedia) matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => { let g = null; try { g = localStorage.getItem('mi-tema'); } catch (x) {} if (!g) aplicar(e.matches ? 'dark' : 'light', false); });
})();

// Asistente virtual "Ilu": el texto del bot SIEMPRE se escapa antes de dar formato (negrita y enlaces internos), así no hay XSS.
(function () {
  const raiz = document.getElementById('chatbot'); if (!raiz) return;
  const $ = id => document.getElementById(id);
  const panel = $('chatPanel'), msgs = $('chatMsgs'), form = $('chatForm'), input = $('chatInput'), fab = $('chatAbrir'), sug = $('chatSug');
  const base = raiz.dataset.base || '';
  let ocupado = false, historial = [];
  try { historial = JSON.parse(sessionStorage.getItem('mi-chat') || '[]'); } catch (e) {}

  const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const formato = t => {
    let h = esc(t);
    h = h.replace(/\[([^\]]{1,80})\]\(([^)\s]{1,300})\)/g, (m, txt, u) => {
      if (u.startsWith('/') && !u.startsWith('//')) return '<a href="' + base + u + '">' + txt + '</a>';
      if (/^https:\/\/wa\.me\//.test(u)) return '<a href="' + u + '" target="_blank" rel="noopener">' + txt + '</a>';
      return txt;           // cualquier otro enlace se muestra solo como texto
    });
    h = h.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/(^|[\s(])\*([^*\n]+)\*/g, '$1<em>$2</em>').replace(/\n/g, '<br>');
    return h;
  };
  const pintar = (rol, texto) => {
    const d = document.createElement('div'); d.className = 'msg ' + (rol === 'user' ? 'msg-user' : 'msg-bot');
    if (rol === 'user') d.textContent = texto; else d.innerHTML = formato(texto);
    msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight; return d;
  };
  const guardar = () => { try { sessionStorage.setItem('mi-chat', JSON.stringify(historial.slice(-30))); } catch (e) {} };
  const bienvenida = () => pintar('bot', '¡Hola! 💖 Soy **Ilu**, la asistente de Mundo Ilusión. Pregúntame por productos, precios, pagos, entregas o promociones.');
  const abrir = v => {
    panel.hidden = !v; fab.setAttribute('aria-expanded', v ? 'true' : 'false'); fab.style.display = v ? 'none' : '';
    try { sessionStorage.setItem('mi-chat-abierto', v ? '1' : '0'); } catch (e) {}
    if (v) { input.focus(); msgs.scrollTop = msgs.scrollHeight; } else fab.focus();
  };
  if (historial.length) historial.forEach(h => pintar(h.r, h.t)); else bienvenida();
  fab.addEventListener('click', () => abrir(true)); $('chatCerrar').addEventListener('click', () => abrir(false));
  panel.addEventListener('keydown', e => { if (e.key === 'Escape') abrir(false); });
  try { if (sessionStorage.getItem('mi-chat-abierto') === '1') abrir(true); } catch (e) {}
  if (historial.length) sug.style.display = 'none';

  async function enviar(texto) {
    if (ocupado || !texto.trim()) return;
    ocupado = true; sug.style.display = 'none';
    pintar('user', texto); historial.push({ r: 'user', t: texto }); input.value = '';
    const esp = document.createElement('div'); esp.className = 'msg msg-bot'; esp.innerHTML = '<span class="msg-typing"><i></i><i></i><i></i></span>'; msgs.appendChild(esp); msgs.scrollTop = msgs.scrollHeight;
    try {
      const fd = new FormData(); fd.append('mensaje', texto); fd.append('_token', raiz.dataset.token);
      const r = await fetch(raiz.dataset.url, { method: 'POST', body: fd, credentials: 'same-origin' });
      const j = await r.json().catch(() => ({}));
      esp.remove();
      const resp = j.ok ? j.respuesta : (j.error || 'Ups, algo salió mal. Inténtalo de nuevo en un momento.');
      pintar('bot', resp); historial.push({ r: 'bot', t: resp });
    } catch (e) { esp.remove(); const t = 'No pude conectarme. Revisa tu internet e inténtalo de nuevo.'; pintar('bot', t); historial.push({ r: 'bot', t }); }
    guardar(); ocupado = false; input.focus();
  }
  form.addEventListener('submit', e => { e.preventDefault(); enviar(input.value); });
  sug.querySelectorAll('.chip-sug').forEach(b => b.addEventListener('click', () => enviar(b.textContent)));
  $('chatReiniciar').addEventListener('click', async () => {
    msgs.innerHTML = ''; historial = []; guardar(); sug.style.display = ''; bienvenida();
    try { const fd = new FormData(); fd.append('_token', raiz.dataset.token); await fetch(raiz.dataset.reset, { method: 'POST', body: fd, credentials: 'same-origin' }); } catch (e) {}
  });
})();
