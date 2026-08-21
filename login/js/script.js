
function mostrarContrasenia() {
  var x = document.getElementById("contrasenia");
  if (!x) return;
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}

var btnMostrar = document.getElementById("mostrarContrasenia");
if (btnMostrar) {
  btnMostrar.addEventListener("click", mostrarContrasenia);
}