/**
 * Lógica de modal y búsqueda para Asignaciones
 */
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

function seleccionarEquipo(id, nombre) {
    document.getElementById('id_equipo').value = id;
    document.getElementById('nombre_equipo_seleccionado').value = nombre;
    document.getElementById('btnLimpiarEquipo').style.display = 'inline-block';
    cerrarModalEquipos();
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

// Cerrar al hacer clic fuera del modal
window.addEventListener('click', function (e) {
    const modal = document.getElementById('modalSeleccionarEquipo');
    if (e.target === modal) {
        cerrarModalEquipos();
    }
});
