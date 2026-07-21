<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Mis Tickets</title>
    <link rel="stylesheet" href="../css/mesa_ayuda.css">
</head>

<body>

    <!-- cabecera de la página -->
    <div class="encabezado">
        <h1>Mesa de Ayuda</h1>
        <span>Usuario: Samuel Fontes | <a href="#">Cerrar sesión</a></span>
    </div>

    <!-- layout principal -->
    <div class="contenedorPrincipal">

        <!-- menú de la izquierda -->
        <div class="barraLateral">
            <ul>
                <li><a href="mesa_ayuda.php">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php">Nuevo Ticket</a></li>
                <li><a href="todos_tickets.html">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.html">Equipos con atención</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <!-- tabla con los tickets del usuario -->
            <div class="seccion">
                <h2>Mis Tickets</h2>

                <div class="filtros">
                    <label>Estado:
                        <select id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="abierto">Abierto</option>
                            <option value="en_progreso">En Progreso</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </label>
                    <label>Prioridad:
                        <select id="filtroPrioridad">
                            <option value="">Todas</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </label>
                    <button type="button">Filtrar</button>
                </div>

                <table class="tablaTickets">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td>No puedo asdasdasd</td>
                            <td>Acceso</td>
                            <td>Alta</td>
                            <td><span class="estadoAbierto">Abierto</span></td>
                            <td>09/07/2026</td>
                            <td><button type="button">Ver</button></td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>Me pasó tal cosa...</td>
                            <td>Impresión</td>
                            <td>Media</td>
                            <td><span class="estadoEnProgreso">En Progreso</span></td>
                            <td>08/07/2026</td>
                            <td><button type="button">Ver</button></td>
                        </tr>
                        <tr>
                            <td>003</td>
                            <td>Quiero una laptop</td>
                            <td>Hardware</td>
                            <td>Baja</td>
                            <td><span class="estadoCerrado">Cerrado</span></td>
                            <td>05/07/2026</td>
                            <td><button type="button">Ver</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>

</html>