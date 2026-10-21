<script>
/* Aplica el tema guardado (o el del sistema) antes de pintar la página: evita el parpadeo blanco. */
(function(){var t=null;try{t=localStorage.getItem('mi-tema');}catch(e){}
if(t!=='dark'&&t!=='light'){t=(window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light';}
document.documentElement.setAttribute('data-bs-theme',t);})();
</script>
