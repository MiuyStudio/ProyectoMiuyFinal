/**
 * Modal y visualización de diagnósticos por equipo
 */
function abrirModalDiagnosticos(idEquipo, nombreEquipo) {
    document.getElementById('tituloModalDiagnostico').innerText = 'Diagnósticos: ' + nombreEquipo;
    const contenedor = document.getElementById('cuerpoModalDiagnostico');
    const datos = (typeof diagnosticosPorEquipo !== 'undefined') ? diagnosticosPorEquipo : {};
    const lista = datos[idEquipo] || [];

    if (lista.length === 0) {
        contenedor.innerHTML = '<p class="text-center" style="color: #777; padding: 20px 0;">No hay diagnósticos registrados para este equipo.</p>';
        document.getElementById('modalDiagnosticos').style.display = 'flex';
        return;
    }

    let filas = '';
    lista.forEach(d => {
        const ticket = d.id_ticket
            ? `<a href="ver_ticket.php?id=${d.id_ticket}&origen=diagnosticos">#${d.id_ticket}${d.titulo_ticket ? ' - ' + escapeHtml(d.titulo_ticket) : ''}</a>`
            : 'N/A';

        filas += `
            <tr>
                <td>${ticket}</td>
                <td>${escapeHtml(d.nombre_tecnico || 'N/A')}</td>
                <td>${escapeHtml(d.diagnostico || '—')}</td>
                <td>${escapeHtml(d.solucion_aplicada || '—')}</td>
                <td>${escapeHtml(d.fecha_intervencion || 'N/A')}</td>
            </tr>`;
    });

    contenedor.innerHTML = `
        <table class="tablaSimple">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Técnico</th>
                    <th>Diagnóstico</th>
                    <th>Solución Aplicada</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>${filas}</tbody>
        </table>`;

    document.getElementById('modalDiagnosticos').style.display = 'flex';
}

function cerrarModalDiagnosticos() {
    document.getElementById('modalDiagnosticos').style.display = 'none';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

window.addEventListener('click', function (e) {
    const modal = document.getElementById('modalDiagnosticos');
    if (e.target === modal) {
        cerrarModalDiagnosticos();
    }
});
