/**
 * Control del modal para resolver/cambiar estado del ticket
 */
function abrirModalResolver(estadoInicial) {
    const modal = document.getElementById('modalResolverTicket');
    if (!modal) return;
    if (estadoInicial) {
        const selectEstado = document.getElementById('modal_estado');
        if (selectEstado) selectEstado.value = estadoInicial;
    }
    modal.classList.add('activo');
}

function cerrarModalResolver() {
    const modal = document.getElementById('modalResolverTicket');
    if (modal) modal.classList.remove('activo');
}

function cerrarModalFuera(e) {
    if (e.target && e.target.id === 'modalResolverTicket') {
        cerrarModalResolver();
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModalResolver();
    }
});
