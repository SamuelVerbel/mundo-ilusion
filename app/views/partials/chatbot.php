<?php // Burbuja de chat de "Ilu". Todo el comportamiento está en public/js/app.js (bloque "Asistente virtual"). ?>
<div id="chatbot" data-url="<?= e(url('chatbot/mensaje')) ?>" data-reset="<?= e(url('chatbot/reiniciar')) ?>" data-token="<?= e(Csrf::token()) ?>" data-base="<?= e(BASE_URL) ?>" data-ia="<?= Chatbot::conIA() ? '1' : '0' ?>">
  <button type="button" class="chat-fab" id="chatAbrir" aria-label="Abrir chat con Ilu, la asistente virtual" aria-expanded="false" aria-controls="chatPanel">
    <i class="bi bi-chat-heart-fill" aria-hidden="true"></i><span class="chat-fab-txt">Pregúntale a Ilu</span>
  </button>
  <section class="chat-panel" id="chatPanel" role="dialog" aria-label="Chat con Ilu, asistente virtual" hidden>
    <header class="chat-head">
      <img src="<?= asset('img/logo.png') ?>" alt="" class="chat-avatar">
      <div class="flex-grow-1"><strong>Ilu</strong><small class="d-block opacity-75">Asistente virtual de Mundo Ilusión</small></div>
      <button type="button" class="chat-ib" id="chatReiniciar" aria-label="Reiniciar conversación" title="Reiniciar"><i class="bi bi-arrow-counterclockwise"></i></button>
      <button type="button" class="chat-ib" id="chatCerrar" aria-label="Cerrar chat"><i class="bi bi-x-lg"></i></button>
    </header>
    <div class="chat-msgs" id="chatMsgs" role="log" aria-live="polite" tabindex="0"></div>
    <div class="chat-sug" id="chatSug">
      <button type="button" class="chip-sug">¿Cómo hago un pedido?</button>
      <button type="button" class="chip-sug">¿Qué promociones hay?</button>
      <button type="button" class="chip-sug">¿Cuánto cuesta un desayuno sorpresa?</button>
      <button type="button" class="chip-sug">¿Cómo puedo pagar?</button>
    </div>
    <form class="chat-form" id="chatForm" autocomplete="off">
      <label for="chatInput" class="visually-hidden">Escribe tu pregunta</label>
      <input id="chatInput" class="form-control" maxlength="500" placeholder="Escribe tu pregunta…" required>
      <button class="btn btn-mi chat-send" aria-label="Enviar"><i class="bi bi-send-fill" aria-hidden="true"></i></button>
    </form>
    <p class="chat-aviso">Ilu es un asistente automático. No compartas contraseñas ni datos de tarjeta.</p>
  </section>
</div>
