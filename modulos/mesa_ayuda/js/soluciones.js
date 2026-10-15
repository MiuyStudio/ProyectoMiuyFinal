/**
 * Modal y visualización de soluciones aplicadas por equipo
 */
function abrirModal(idEquipo, nombreEquipo) {
    document.getElementById('tituloModal').innerText = 'Soluciones Aplicadas: ' + nombreEquipo;
    const contenedor = document.getElementById('cuerpoModal');
    const datos = (typeof solucionesPorEquipo !== 'undefined') ? solucionesPorEquipo : {};
    const lista = datos[idEquipo] || [];

    if (lista.length === 0) {
        contenedor.innerHTML = '<p class="text-center" style="color: #777; padding: 20px 0;">No hay soluciones registradas para este equipo.</p>';
        document.getElementById('modalSoluciones').style.display = 'flex';
        return;
    }

    let filas = '';
    lista.forEach(d => {
        const ticket = d.id_ticket
            ? `<a href="ver_ticket.php?id=${d.id_ticket}&origen=soluciones">#${d.id_ticket}${d.titulo_ticket ? ' - ' + escapeHtml(d.titulo_ticket) : ''}</a>`
            : 'N/A';

        filas += `
            <tr>
                <td>${ticket}</td>
                <td>${escapeHtml(d.nombre_tecnico || 'N/A')}</td>
                <td>${escapeHtml(d.diagnostico || '—')}</td>
                <td><strong>${escapeHtml(d.solucion_aplicada || '')}</strong></td>
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

    document.getElementById('modalSoluciones').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalSoluciones').style.display = 'none';
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
    const modal = document.getElementById('modalSoluciones');
    if (e.target === modal) {
        cerrarModal();
    }
});
