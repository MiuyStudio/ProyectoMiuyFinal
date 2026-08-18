<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTU - Iniciar Sesión</title>
    <link rel="icon" type="image/png" href="../assets/utu.png">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php
    $error = isset($_GET['error']) ? $_GET['error'] : '';
    ?>

    <div class="contenedor">

        <div class="col-izq">
            <div class="tarjeta">
                <form action="procesar_login.php" method="POST">
                    <p class="titulo-login">Iniciar Sesión</p>

                    <?php if (!empty($error)): ?>
                        <p style="color: #d33; font-weight: bold; font-size: 13px; margin-bottom: 12px; text-align: center;">
                            <?php echo htmlspecialchars($error); ?>
                        </p>
                    <?php endif; ?>

                    <div class="campo">
                        <input name="ci" required placeholder="Cédula (sin puntos ni guiones)" type="text" autocomplete="username" />
                        <small style="font-size: 11px; color: #777; display: block; margin-top: 4px;">Ej: 55204710 (solo números, sin puntos ni guiones)</small>
                    </div>

                    <div class="campo">
                        <input name="contrasena" required placeholder="Contraseña" type="password" autocomplete="current-password" />
                    </div>

                    <button class="boton" type="submit">Ingresar</button>
                </form>
            </div>
        </div>

        <div class="col-der">
            <img src="./background/background.jpg" alt="Fondo UTU">
        </div>

    </div>

    <script src="./js/script.js"></script>
</body>

</html>