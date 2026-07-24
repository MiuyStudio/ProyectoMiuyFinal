<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Nuevo Ticket</title>
    <link rel="stylesheet" href="../css/mesa_ayuda.css">
</head>

<body>
    <?php
    require_once '../../conexion.php';
    $sql = "SELECT nombre_categoria FROM categorias WHERE id_categoria BETWEEN 1 AND 6";
    $resultado = $conn->query($sql);
    ?>
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

            <!-- formulario para cargar un ticket nuevo -->
            <div class="seccion">
                <h2>Crear Nuevo Ticket</h2>
                <form id="formularioNuevoTicket">

                    <div class="grupoFormulario">
                        <label for="tituloTicket">Título del problema *</label>
                        <input type="text" id="tituloTicket" placeholder="Ej: No puedo abrir el programa">
                    </div>

                    <div class="grupoFormulario">
                        <label for="tipoTicket">Tipo de ticket *</label>
                        <select id="tipoTicket">
                            <option value="Incidencia">Incidencia</option>
                            <option value="Solicitud de Servicio">Solicitud de Servicio</option>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="categoriaTicket">Categoría *</label>
                        <select id="categoriaTicket">
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                                <option value="<?php echo $fila['nombre_categoria']; ?>">
                                    <?php echo $fila['nombre_categoria']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="prioridadTicket">Prioridad *</label>
                        <select id="prioridadTicket">
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="descripcionTicket">Descripción detallada *</label>
                        <textarea id="descripcionTicket" rows="5"
                            placeholder="Describí el problema con el mayor detalle posible..."></textarea>
                    </div>

                    <div class="grupoFormulario">
                        <label for="equipoAfectado">Equipo afectado (opcional)</label>
                        <input type="text" id="equipoAfectado" placeholder="Ej: PC-LAB-03">
                    </div>

                    <div class="botonesFormulario">
                        <button type="button">Enviar Ticket</button>
                        <button type="button">Cancelar</button>
                    </div>

                </form>
            </div>

        </div>
    </div>

</body>

</html>