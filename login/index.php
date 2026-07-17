<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    
    <div class="contenedor">
        
        <div class="col-izq">
            <div class="tarjeta">
                <form action="">
                    <p class="titulo-login">Iniciar Sesión</p>
                    
                    <div class="campo">
                        <input required="" placeholder="Usuario" type="text" />
                    </div>
                    
                    <div class="campo">
                        <input required="" placeholder="Contraseña" type="password" />
                    </div>
                    
                    <div class="opciones">
                        <label><input type="checkbox" />¿Recordarme?</label>
                        <a href="#">¿Olvidaste tu contraseña?</a>
                    </div>
                    
                    <button class="boton" type="submit">Ingresar</button>
                    
                    <div class="enlace-registro">
                        <p>¿No tienes una cuenta? <a href="#">Registrate</a></p>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-der">
            <!-- <p>Bienvenido</p> -->
             <img src="./background/background.jpg" alt="">
        </div>

    </div>

    <script src="./js/script.js"></script>
</body>
</html>