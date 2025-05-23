// SIEMPRE AGREGAR ESTA SECCION A CADA ARCHIVO QUE TENGA EL NAVBAR //
const contenedorFecha = document.getElementById('current-date');
const fechaData = new Date();
const formatoFecha = fechaData.toLocaleDateString('es-ES', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});
contenedorFecha.textContent = formatoFecha;

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
// ------------------------------------------------------------------- //
document.querySelector('#btnUsuarios').addEventListener('click', async () => {
    const { value: Contraseña } = await Swal.fire({
        title: 'Confirmar contraseña',
        input: 'password',
        inputLabel: 'Ingresa tu contraseña nuevamente',
        inputPlaceholder: 'Contraseña',
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) {
                return '¡Ingrese un valor valido!';
            }
        }
    });

    if (Contraseña) {
        fetch('../controllers/verificarPassword.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ password : Contraseña })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                window.location.href = '../views/usuarios.php';  
            }else{
                Swal.fire('Error', `Contraseña incorrecta o el acceso fue denegado`, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Ocurrio un error al intentar comprobar la contraseña', 'error');
            console.log(err);
        })
    }
});