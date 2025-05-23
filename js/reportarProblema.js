// PARA MOSTRAR LA FECHA ACCTUAL, AÑADIR ESTO A TODOS LOS ARCHVIOS QUE TENGAN EL NAV
const contenedorFecha = document.getElementById('current-date');
const fechaData = new Date();
const formatoFecha = fechaData.toLocaleDateString('es-ES', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});
contenedorFecha.textContent = formatoFecha;
// BOTON PARA CERRAR LA SESION, IGUAL AÑADIR A TODOS LOS QUE TENGAN EL NAV
document.getElementById('logout-btn').addEventListener('click', () => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Quieres cerrar sesion?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, cerrar sesion',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../controllers/logout.php';
        }
    })
})
//MOSTRAR MENSAJE DE RESPUESTA AL ENVIO DEL CORREO
document.addEventListener('DOMContentLoaded', () => {
    respuestaEnvioEmail()
});
// SE MUESTRA MIENTRAS SE ENVIA EL CORREO
document.querySelector("form").addEventListener("submit", function () {
    document.querySelector(".btn-send").disabled = true;
    document.querySelector(".btn-send").textContent = "Enviando...";

    Swal.fire({
        icon: 'warning',
        title: "Enviando correo",
        html: "Espera un momentoo mientras se envia tu mensaje",
        timer: 10000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        },
    })
});
// PARA RECIBIR LA RESPUESTA Y MOSTRAR EL MSJ
function respuestaEnvioEmail() {
    const urlActual = window.location.search;
    const urlParams = new URLSearchParams(urlActual);

    const asuntoParametro = urlParams.get('asunto');
    const mensajeParametro = urlParams.get('mensaje');
    const archivoParametro = urlParams.get('archivo');
    const respuestaEnvio = urlParams.get('envio');

    if (asuntoParametro) {
        alertaEsquinaSuperior('error', asuntoParametro);
    }

    if (mensajeParametro) {
        alertaEsquinaSuperior('error', mensajeParametro);
    }

    if (archivoParametro) {
        alertaEsquinaSuperior('error', archivoParametro);
    }

    if (respuestaEnvio) {
        const icon = urlParams.get('icon');
        alertaEsquinaSuperior(icon, respuestaEnvio);
    }

    window.history.replaceState(null, null, window.location.pathname);
}
//PARA EL TAMAÑO DEL ARCHIVO RECIBIDO
const MAX_SIZE = 10 * 1024 * 1024; // 10 MB SERIAN LO MAXIMO

document.getElementById('adjunto').addEventListener('change', function () {
    if (this.files.length > 0 && this.files[0].size > MAX_SIZE) {
        alertaEsquinaSuperior("error", "El archivo no debe superar los 10 MB");
        this.value = '';
    }
});

function alertaEsquinaSuperior(icono, mensaje) {
    Swal.fire({
        icon: icono,
        title: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
