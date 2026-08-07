// Esperamos a que el HTML termine de cargar antes de ejecutar el script
document.addEventListener('DOMContentLoaded', () => {
    // Manejo de filtrado de modelos por marca (soporta formulario normal y modal)
    const inicializarFiltroMarcaModelo = (idMarca, idModelo) => {
        const selectMarca = document.getElementById(idMarca);
        const selectModelo = document.getElementById(idModelo);

        if (!selectMarca || !selectModelo) return;

        const opcionesModelos = Array.from(selectModelo.querySelectorAll('option'));

        const filtrarModelos = () => {
            const marcaId = selectMarca.value;

            if (!marcaId) {
                selectModelo.disabled = true;
                return;
            }

            selectModelo.disabled = false;

            opcionesModelos.forEach(opcion => {
                const marcaDelModelo = opcion.getAttribute('data-marca');

                if (!opcion.value) {
                    opcion.hidden = false;
                    opcion.disabled = false;
                    return;
                }

                if (marcaDelModelo === marcaId) {
                    opcion.hidden = false;
                    opcion.disabled = false;
                } else {
                    opcion.hidden = true;
                    opcion.disabled = true;
                }
            });
        };

        selectMarca.addEventListener('change', () => {
            selectModelo.value = '';
            filtrarModelos();
        });

        if (selectMarca.value) {
            filtrarModelos();
        }
    };

    // Inicializar para agregar_equipo.php y para el modal de inventario.php
    inicializarFiltroMarcaModelo('marcaEquipo', 'modeloEquipo');
    inicializarFiltroMarcaModelo('modal_marca', 'modal_modelo');
});

// Función para abrir el modal leyendo los datos desde el botón clicado
function abrirModalDesdeBoton(btn) {
    const equipo = {
        id_equipo: btn.getAttribute('data-id') || '',
        numero_serie: btn.getAttribute('data-serie') || '',
        nombre: btn.getAttribute('data-nombre') || '',
        id_categoria: btn.getAttribute('data-categoria') || '',
        id_marca: btn.getAttribute('data-marca') || '',
        id_modelo: btn.getAttribute('data-modelo') || '',
        estado: btn.getAttribute('data-estado') || ''
    };
    abrirModalEquipo(equipo);
}

// Función global para poblar y mostrar el modal
function abrirModalEquipo(equipo) {
    const modal = document.getElementById('modalEquipo');
    if (!modal) return;

    document.getElementById('modal_id_equipo').value = equipo.id_equipo || '';
    document.getElementById('modal_serie').value = equipo.numero_serie || '';
    document.getElementById('modal_nombre').value = equipo.nombre || '';
    document.getElementById('modal_categoria').value = equipo.id_categoria || '';
    document.getElementById('modal_marca').value = equipo.id_marca || '';
    document.getElementById('modal_estado').value = equipo.estado || '';

    // Disparar evento change en la marca para actualizar la lista desplegable de modelos
    const selectMarca = document.getElementById('modal_marca');
    const selectModelo = document.getElementById('modal_modelo');

    if (selectMarca && selectModelo) {
        const event = new Event('change');
        selectMarca.dispatchEvent(event);
        selectModelo.value = equipo.id_modelo || '';
    }

    modal.classList.add('activo');
}

// Función global para cerrar el modal
function cerrarModalEquipo() {
    const modal = document.getElementById('modalEquipo');
    if (modal) {
        modal.classList.remove('activo');
    }
}

// Cerrar modal al hacer clic en el fondo oscuro
window.addEventListener('click', (e) => {
    const modal = document.getElementById('modalEquipo');
    if (e.target === modal) {
        cerrarModalEquipo();
    }
});