document.addEventListener('DOMContentLoaded', () => {
    mostrarUsuarios();
});

let usuariosData = [];

// COLOQUEN ESTO EN CADA ARCHIVO QUE TENGA EL NAV PARA CARGAR LA FECHA ACTUAL
const contenedorFecha = document.getElementById('current-date');
const fechaData = new Date();
const formatoFecha = fechaData.toLocaleDateString('es-ES', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});
contenedorFecha.textContent = formatoFecha;

//esto de aqui es para cerrar la sesion
document.getElementById('logout-btn').addEventListener('click', () => {
    Swal.fire({
        title: '¿Estas seguro?',
        text: '¿Quieres cerrar sesión?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../controllers/logout.php';
        }
    })
})
//aqui se llaman los datos de los usuarios recibidos del controllador
async function cargarUsuarios() {
    try {
        const response = await fetch('../controllers/usuariosController.php');

        if (!response.ok) {
            mensajeError('Ocurrió un error al cargar los datos');
            return [];
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.log(error);
        mensajeError('Ocurrió un error al llamar los datos');
        return [];
    }
}
//Esta funcion muestra los usuarios recibidos
async function mostrarUsuarios() {
    usuariosData = await cargarUsuarios();
    console.log("Usuarios en la base de datos: ", usuariosData);

    if (usuariosData.length <= 0) {
        mensajeError('No hay usuarios disponibles');
        return;
    } else {
        document.getElementById('mensajeSinUsuarios').style.display = 'none';
    }

    const tabla = document.getElementById('tablaUsuarios');
    tabla.innerHTML = '';

    usuariosData.forEach((usuario, index) => {
        const tr = document.createElement('tr');
        tr.classList.add('transicion-fila');

        let estadoTexto = '';

        if (usuario.estado != 1) {
            estadoTexto = '<span class="status-inactivo"><i class="bi bi-circle-fill text-danger me-1"></i> Inactivo</span>';
        } else {
            estadoTexto = '<span class="status-activo"><i class="bi bi-circle-fill text-success me-1"></i> Activo</span>';
        }

        tr.innerHTML = `
            <td >${usuario.usuario_nombre} ${usuario.usuario_apellido}</td>
            <td >${usuario.correo_electronico}</td>
            <td>${usuario.nombreUsuario}</td>
            <td>${usuario.nombre_rol}</td>
            <td>${estadoTexto}</td>
            <td>${usuario.fecha_registro}</td>
            <td class="iconos-acciones">
            <button class="btn btn-light my-1" title="Mostrar" onclick="cargarDataDetalles(${usuario.usuario_id})"><i class="bi bi-eye-fill text-primary"></i></button>
            <button class="btn btn-light my-1" title="Modificar" onclick="editarUsuario(${usuario.usuario_id})"><i class="bi bi-pencil-fill text-warning"></i></button>
            <button class="btn btn-light my-1" title="Eliminar" onclick="eliminarUsuario(${usuario.usuario_id})"><i class="bi bi-trash-fill text-danger"></i></button>
            </td>
        `;

        tabla.appendChild(tr);

        setTimeout(() => {
            tr.classList.add('mostrar');
        }, 80 * (index + 1));
    });
}
//si no hay ningun usuario se llama esta funcion que muestra un mensaje diciendo que no hay
function mensajeError(mensaje) {
    const messageElement = document.getElementById('mensajeSinUsuarios');
    messageElement.style.display = 'block';
    messageElement.innerText = mensaje;
}
//esta funcion se ejecuta para ver la info de los usuarios
function cargarDataDetalles(idUsuario) {
    const usuarioEncontrado = usuariosData.find(data => data.usuario_id == idUsuario);
    let estado = "Inactivo";
    if (usuarioEncontrado.estado == 1) {
        estado = "Activo";
    }
    console.log(usuarioEncontrado)
    if (usuarioEncontrado) {
        Swal.fire({
            title: `<div style="font-size: 1.5em; font-weight: bold; color: #4a4a4a;"><i class="bi bi-person-fill"></i> Detalles del Usuario</div>`,
            html: `
        <div style="padding: 20px; background-color: #f8f9fa; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="margin-bottom: 12px;">
                <strong style="color: #6c757d;">Nombre:</strong> ${usuarioEncontrado.usuario_nombre}
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #6c757d;">Correo:</strong> ${usuarioEncontrado.correo_electronico}
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #6c757d;">Apellido:</strong> ${usuarioEncontrado.usuario_apellido}
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #6c757d;">Usuario:</strong> ${usuarioEncontrado.nombreUsuario}
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #6c757d;">Rol:</strong> ${usuarioEncontrado.nombre_rol}
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #6c757d;">Estado:</strong> 
                <span style="color: ${estado === 'Activo' ? '#198754' : '#dc3545'}; font-weight: bold;">
                    ${estado}
                </span>
            </div>
        </div>
    `,
            icon: 'info',
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'rounded shadow',
                confirmButton: 'btn btn-outline-primary'
            },
            backdrop: true,
            width: '500px'
        });

    }
}
//aqui se validan los datos para agregar un usuario nuevo
function validarFormulario() {
    Swal.fire({
        title: 'Nuevo Usuario',
        html: `
                    <div style="text-align:left;">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control shadow-none" id="nombre" placeholder="Juan Romeo" required
                                maxlength="35">
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control shadow-none" id="apellido" placeholder="Castro Lopez" required
                            maxlength="35">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo electronico</label>
                            <input type="email" class="form-control shadow-none" id="correo" placeholder="correo@gmail.com" required
                                maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="nombreUsuario" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control shadow-none" id="nombreUsuario" placeholder="juanRome12" required
                                maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="contraseña" class="form-label">Contraseña...</label>
                            <input type="password" class="form-control shadow-none" id="contraseña" placeholder="Escribe aqui"
                                required maxlength="8">
                        </div>
                        <select class="form-select shadow-none" id="permiso" name="permiso" aria-label="select for level" required>
                            <option value="" selected disabled>Nivel de permiso</option>
                            <option value="administrador">Administrador</option>
                            <option value="empleado">Empleado</option>
                        </select>
                    </div>
                        
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle"></i> Confirmar',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary',
            popup: 'p-4'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const datos = {
                usuario_nombre: document.getElementById('nombre').value.trim(),
                usuario_apellido: document.getElementById('apellido').value.trim(),
                correo_electronico: document.getElementById('correo').value.trim(),
                nombreUsuario: document.getElementById('nombreUsuario').value.trim(),
                contraseña: document.getElementById('contraseña').value.trim(),
                nombre_rol: document.getElementById('permiso').value,
                estado: 1
            }

            if (!datos.usuario_nombre || !datos.correo_electronico || !datos.usuario_apellido || !datos.nombreUsuario || !datos.contraseña || !datos.nombre_rol) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
            }

            if (datos.usuario_nombre.length > 35 || datos.usuario_nombre.length < 3) {
                Swal.showValidationMessage('El nombre debe tener entre 3 y 35 caracteres');
                return false;
            }

            if (datos.correo_electronico.length > 255 || datos.correo_electronico.length < 6) {
                Swal.showValidationMessage('El nombre debe tener entre 6 y 255 caracteres');
                return false;
            }

            if (datos.usuario_apellido.length > 35 || datos.usuario_apellido.length < 3) {
                Swal.showValidationMessage('El apellido debe tener entre 3 y 35 caracteres');
                return false;
            }

            if (datos.nombreUsuario.length > 15 || datos.nombreUsuario.length < 4) {
                Swal.showValidationMessage('El nombre de usuario debe tener entre 4 y 15 caracteres');
                return false;
            }

            if (datos.contraseña.length > 8 || datos.contraseña.length < 4) {
                Swal.showValidationMessage('La contraseña debe tener entre 4 y 8 caracteres');
                return false;
            }

            const regex = '[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,5}';

            if (!datos.correo_electronico.match(regex)) {
                Swal.showValidationMessage('Ingrese un correo electronico valido');
                return false;
            }

            return datos;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;
            peticionAgregarUsuario(datos.usuario_nombre, datos.correo_electronico, datos.usuario_apellido, datos.nombreUsuario, datos.contraseña, datos.nombre_rol);
        }
    });
}

//despues de validar los datos se llama esta funcion que hace la peticion para agregarlo
function peticionAgregarUsuario(nombre, correo, apellido, nombreUsuario, contraseña, permiso) {
    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('correo', correo);
    formData.append('apellido', apellido);
    formData.append('nombreUsuario', nombreUsuario);
    formData.append('contraseña', contraseña);
    formData.append('permiso', permiso);

    fetch('../controllers/usuariosController.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            console.log(data.success);
            if (data.success) {
                alertaEsquinaSuperior("success", data.message);
                mostrarUsuarios();
            } else {
                console.log('error al fetch de agregar usuario: ', data.message);
                alertaEsquinaSuperior("error", data.message);
            }
        })
        .catch(err => {
            console.log("error al fetch de agregar usuario: ", err);
            alertaEsquinaSuperior("error", "Ocurrio un error al intentar agregar el usuario");
        });
}
//MUESTRA UNA ALERTA EN LA ESQUINA DE LA DERECHA
function alertaEsquinaSuperior(icono, mensaje) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: icono,
        title: mensaje
    });
}
//funcion para eliminar un usuario
function eliminarUsuario(idUsuario) {
    Swal.fire({
        title: '¿Deseas eliminar este usuario?',
        text: "¡No podras revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../controllers/usuariosController.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idUsuario })
            })
                .then(res => res.json())
                .then(data => {
                    console.log("Respuesta de la eliminacion: ", data);
                    alertaEsquinaSuperior(data.success, data.message);
                    mostrarUsuarios();
                })
                .catch(err => {
                    console.log('Error al fetch de eliminacion: ', err)
                    alertaEsquinaSuperior("error", "Ha ocurrido un error al intentar eliminar el usuario");
                })
            alertaEsquinaSuperior("success", "recibido la alerta");
        }
    });
}
//esta funcion es para editar un usuario, muestra el modal con los datos los valida y hace el fetch al controllador
function editarUsuario(idUsuario) {
    Swal.close();
    const usuarioEncontrado = usuariosData.find((usuario) => usuario.usuario_id === idUsuario);

    if (!usuarioEncontrado) {
        alertaEsquinaSuperior("error", "Ocurrió un error al buscar la información del usuario");
        return;
    }

    let htmlUsuario = `
      <div id="modalModificarUsuario" style="text-align:left;">
        <div class="mb-3">
            <label for="nombreModificado" class="form-label">Nombre</label>
            <input type="text" value="${usuarioEncontrado.usuario_nombre}" class="form-control shadow-none" id="nombreModificado" placeholder="Juan Romeo" required maxlength="35">
        </div>
        <div class="mb-3">
            <label for="apellidoModificado" class="form-label">Apellido</label>
            <input type="text" value="${usuarioEncontrado.usuario_apellido}" class="form-control shadow-none" id="apellidoModificado" placeholder="Castro Lopez" required maxlength="35">
        </div>
         <div class="mb-3">
            <label for="correo" class="form-label">Correo electronico</label>
            <input type="email" value="${usuarioEncontrado.correo_electronico}" class="form-control shadow-none" id="correo" placeholder="correo@gmail.com" required
                maxlength="255">
        </div>
        <div class="mb-3">
            <label for="nombreUsuarioModificado" class="form-label">Nombre de usuario</label>
            <input type="text" value="${usuarioEncontrado.nombreUsuario}" class="form-control shadow-none" id="nombreUsuarioModificado" placeholder="juanRome12" required maxlength="15">
        </div>
        <div class="mb-3 position-relative">
            <label for="contraseñaModificada" class="form-label">Nueva contraseña <small class="text-muted">(opcional)</small></label>
            <input type="password" id="contraseñaModificada" name="contraseñaModificada" class="form-control shadow-none" autocomplete="current-password" maxlength="8">
            <i class="bi pt-4 mt-1 bi-eye-fill show-password" onclick="viewPassForm()"></i>
        </div>
    `;

    htmlUsuario += `
        <div class="mt-3 position-relative">
            <select class="form-select shadow-none" id="permisoModificado" name="permisoModificado" aria-label="select for level" required>
                <option value="" disabled>Nivel de permiso</option>
                <option value="administrador" ${(usuarioEncontrado.nombre_rol == "administrador") ? "selected" : ""}>Administrador</option>
                <option value="empleado" ${(usuarioEncontrado.nombre_rol == "empleado") ? "selected" : ""}>Empleado</option>
            </select>
        </div>
    `;

    htmlUsuario += `
        <div class="mt-3 position-relative">
            <select class="form-select shadow-none" id="estadoModificado" name="estadoModificado" aria-label="select for level" required>
                <option value="" disabled>Estado del usuario</option>
                <option value="activo" ${usuarioEncontrado.estado == 1 ? "selected" : ""}>Activo</option>
                <option value="inactivo" ${usuarioEncontrado.estado == 0 ? "selected" : ""}>Inactivo</option>
            </select>
        </div>
    `;

    Swal.fire({
        title: 'Modificar usuario',
        html: htmlUsuario,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle"></i> Confirmar',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary',
            popup: 'p-4'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const estadoString = document.getElementById('estadoModificado').value;
            const estadoInt = (estadoString == "activo") ? 1 : 0;

            const datos = {
                usuario_nombre: document.getElementById('nombreModificado').value.trim(),
                usuario_apellido: document.getElementById('apellidoModificado').value.trim(),
                correo_electronico: document.getElementById('correo').value.trim(),
                nombreUsuario: document.getElementById('nombreUsuarioModificado').value.trim(),
                contraseña: document.getElementById('contraseñaModificada').value.trim(),
                nombre_rol: document.getElementById('permisoModificado').value,
                estado: estadoInt
            }
            // console.log("estado; ", datos)
            if (!datos.usuario_nombre || !datos.usuario_apellido || !datos.nombreUsuario || !datos.nombre_rol) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
            }

            if (datos.estado != 0 && datos.estado != 1) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
            }

            if (datos.usuario_nombre.length > 35 || datos.usuario_nombre.length < 3) {
                Swal.showValidationMessage('El nombre debe tener entre 3 y 35 caracteres');
                return false;
            }

            if (datos.usuario_apellido.length > 35 || datos.usuario_apellido.length < 3) {
                Swal.showValidationMessage('El apellido debe tener entre 3 y 35 caracteres');
                return false;
            }

            if (datos.correo_electronico.length > 255 || datos.correo_electronico.length < 6) {
                Swal.showValidationMessage('El nombre debe tener entre 6 y 255 caracteres');
                return false;
            }


            if (datos.nombreUsuario.length > 15 || datos.nombreUsuario.length < 4) {
                Swal.showValidationMessage('El nombre de usuario debe tener entre 4 y 15 caracteres');
                return false;
            }

            if (datos.contraseña != "") {
                if (datos.contraseña.length > 8 || datos.contraseña.length < 4) {
                    Swal.showValidationMessage('La contraseña debe tener entre 4 y 8 caracteres');
                    return false;
                }
            }

            const regex = '[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,5}';

            if (!datos.correo_electronico.match(regex)) {
                Swal.showValidationMessage('Ingrese un correo electronico valido');
                return false;
            }

            return datos;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;

            const cambios = {};
            for (const clave in datos) {
                if (clave === "contraseña") {
                    if (datos[clave] !== "") {
                        cambios[clave] = datos[clave];
                    }
                } else if (datos[clave] !== usuarioEncontrado[clave]) {
                    cambios[clave] = datos[clave];
                }
            }

            cambios.usuario_id = idUsuario; // siempre incluir el id

            if (Object.keys(cambios).length === 1) {
                alertaEsquinaSuperior("info", "No se modificaron campos para actualizar");
                return;
            }

            console.log("datos para modificar:", cambios);

            fetch("../controllers/usuariosController.php", {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cambios)
            }).then(res => res.json())
                .then(data => {
                    console.log("respuesta del fetch para actualizar: ", data);
                    alertaEsquinaSuperior(data.success, data.message);
                    mostrarUsuarios();
                })
                .catch(err => {
                    console.log("error al fetch de actualizar", err);
                    alertaEsquinaSuperior("error", "Ocurrio un error al actualizar el usuario");
                });
        }
    });
}
//esto es simplemente para cambiar si se mira la contraseña o no
function viewPassForm() {
    const password = document.getElementById('contraseñaModificada');
    const icon = document.querySelector('.show-password');
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
    }
}
// evento para buscar los usuarios por medio del filtro
document.getElementById('grupoBuscar').addEventListener('change', (e) => {
    let usuarioFiltro = document.getElementById('buscarUsuario').value;
    let rolFiltro = document.getElementById('buscarRol').value;
    let estadoFiltro = document.getElementById('buscarEstado').value;
    console.log("parametros para buscar el usuario: ", usuarioFiltro, rolFiltro, estadoFiltro);

    if (!usuarioFiltro && !rolFiltro && !estadoFiltro) {
        mostrarUsuarios();
        return;
    }

    buscarUsuarioFiltro(usuarioFiltro, rolFiltro, estadoFiltro);
});
// peticon al controllador para buscar el usuario
function buscarUsuarioFiltro(usuarioFiltro, rolFiltro, estadoFiltro) {
    const filtros = { usuarioFiltro, rolFiltro, estadoFiltro };

    fetch('../controllers/usuariosController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(filtros)
    }).then(res => res.json())
        .then(data => {
            console.log(data);
            mostrarUsuariosFiltro(data);
        })
        .catch(err => console.log(err))
}
// funcion para mostrar los usuarios encontrados en el filtro
function mostrarUsuariosFiltro(usuariosData) {

    const tabla = document.getElementById('tablaUsuarios');
    tabla.innerHTML = '';

    if (usuariosData == 0) {
        tabla.innerHTML = '';
        mensajeError('No se encontro el usuario');
        return;
    } else {
        document.getElementById('mensajeSinUsuarios').style.display = 'none';
    }

    usuariosData.forEach((usuario, index) => {
        const tr = document.createElement('tr');
        tr.classList.add('transicion-fila');

        let estadoTexto = '';

        if (usuario.estado != 1) {
            estadoTexto = '<span class="status-inactivo"><i class="bi bi-circle-fill text-danger me-1"></i> Inactivo</span>';
        } else {
            estadoTexto = '<span class="status-activo"><i class="bi bi-circle-fill text-success me-1"></i> Activo</span>';
        }

        tr.innerHTML = `
            <td >${usuario.usuario_nombre} ${usuario.usuario_apellido}</td>
            <td>${usuario.nombreUsuario}</td>
            <td>${usuario.nombre_rol}</td>
            <td>${estadoTexto}</td>
            <td>${usuario.fecha_registro}</td>
            <td class="iconos-acciones">
            <button class="btn btn-light my-1" title="Mostrar" onclick="cargarDataDetalles(${usuario.usuario_id})"><i class="bi bi-eye-fill text-primary"></i></button>
            <button class="btn btn-light my-1" title="Modificar" onclick="editarUsuario(${usuario.usuario_id})"><i class="bi bi-pencil-fill text-warning"></i></button>
            <button class="btn btn-light my-1" title="Eliminar" onclick="eliminarUsuario(${usuario.usuario_id})"><i class="bi bi-trash-fill text-danger"></i></button>
            </td>
        `;

        tabla.appendChild(tr);

        setTimeout(() => {
            tr.classList.add('mostrar');
        }, 80 * (index + 1));
    });
}