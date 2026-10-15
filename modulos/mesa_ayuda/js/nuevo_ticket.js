/**
 * Lógica de selección de equipos y modal de tickets activos para Nuevo Ticket
 */
let ticketActivoId = null;

function abrirModalEquipos() {
    document.getElementById('modalSeleccionarEquipo').classList.add('activo');
    const inputBuscar = document.getElementById('inputBuscarEquipo');
    inputBuscar.value = '';
    filtrarEquiposModal();
    setTimeout(() => inputBuscar.focus(), 100);
}

function cerrarModalEquipos() {
    document.getElementById('modalSeleccionarEquipo').classList.remove('activo');
}

function abrirModalAviso(idTicket, infoTexto) {
    ticketActivoId = idTicket;
    const infoDiv = document.getElementById('notifInfoDetalle');
    if (infoTexto) {
        infoDiv.textContent = infoTexto;
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
    document.getElementById('modalAvisoTicketActivo').classList.add('activo');
}

function cerrarModalAviso() {
    document.getElementById('modalAvisoTicketActivo').classList.remove('activo');
    ticketActivoId = null;
    limpiarEquipo();
}

function redirigirATicketActivo() {
    if (ticketActivoId) {
        window.location.href = 'ver_ticket.php?id=' + ticketActivoId;
    } else {
        cerrarModalAviso();
    }
}

function seleccionarEquipo(id, nombre, idTicketActivo, tituloTicketActivo) {
    cerrarModalEquipos();

    if (idTicketActivo && idTicketActivo > 0) {
        limpiarEquipo();
        const detalle = tituloTicketActivo ? `Ticket #${idTicketActivo} — "${tituloTicketActivo}"` : `Ticket #${idTicketActivo}`;
        abrirModalAviso(idTicketActivo, detalle);
        return;
    }

    // Si no tiene ticket activo, se asigna directamente
    document.getElementById('id_equipo').value = id;
    document.getElementById('nombre_equipo_seleccionado').value = nombre;
    document.getElementById('btnLimpiarEquipo').style.display = 'inline-block';
}

function limpiarEquipo() {
    document.getElementById('id_equipo').value = '';
    document.getElementById('nombre_equipo_seleccionado').value = '';
    document.getElementById('btnLimpiarEquipo').style.display = 'none';
}

function filtrarEquiposModal() {
    const texto = document.getElementById('inputBuscarEquipo').value.toLowerCase().trim();
    const filas = document.querySelectorAll('#tablaEquiposModal tbody .fila-equipo');
    let visibles = 0;

    filas.forEach(fila => {
        const nombre = (fila.getAttribute('data-nombre') || '').toLowerCase();
        const serie = (fila.getAttribute('data-serie') || '').toLowerCase();
        const categoria = (fila.getAttribute('data-categoria') || '').toLowerCase();

        if (nombre.includes(texto) || serie.includes(texto) || categoria.includes(texto)) {
            fila.style.display = '';
            visibles++;
        } else {
            fila.style.display = 'none';
        }
    });

    const sinCoincidencias = document.getElementById('sinCoincidencias');
    if (sinCoincidencias) {
        sinCoincidencias.style.display = (visibles === 0 && filas.length > 0) ? '' : 'none';
    }
}

// Cerrar al hacer clic fuera del contenido del modal
window.addEventListener('click', function (e) {
    const modalEquipo = document.getElementById('modalSeleccionarEquipo');
    if (e.target === modalEquipo) {
        cerrarModalEquipos();
    }
    const modalAviso = document.getElementById('modalAvisoTicketActivo');
    if (e.target === modalAviso) {
        cerrarModalAviso();
    }
});
