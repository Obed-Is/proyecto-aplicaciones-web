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
        text: '¿Quieres cerrar sesion?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesion',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../controllers/logout.php', { method: 'POST' })
                .then(res => {
                    if (!res.ok) throw new Error('Error en la respuesta del servidor');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = '../views/login.php';
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atencion',
                            text: data.message,
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en fetch logout:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un problema al cerrar sesion',
                    });
                });
        }
    });
});
//aqui se llaman los datos de los usuarios recibidos del controllador
async function cargarUsuarios() {
    try {
        const response = await fetch('../controllers/usuariosController.php');

        if (!response.ok) {
            mensajeError('Ocurrio un error al cargar los datos');
            return [];
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.log(error);
        mensajeError('Ocurrio un error al llamar los datos');
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
            <td  class="responsiv-tabla">${usuario.usuario_nombre} ${usuario.usuario_apellido}</td>
            <td  class="responsiv-tabla">${usuario.correo_electronico}</td>
            <td class="responsiv-tabla">${usuario.nombreUsuario}</td>
            <td class="responsiv-tabla">${usuario.nombre_rol}</td>
            <td class="responsiv-tabla">${estadoTexto}</td>
            <td class="responsiv-tabla">${usuario.fecha_registro}</td>
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
//funcion para calcular la edad del usuario
function calcularEdadUsuario(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);

    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();

    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }

    return edad;
}
//esta funcion se ejecuta para ver la info de los usuarios
function cargarDataDetalles(idUsuario) {
    const usuarioEncontrado = usuariosData.find(data => data.usuario_id == idUsuario);
    const fechaIngresoUsuario = new Date(usuarioEncontrado.fecha_registro);
    const fechaActual = new Date();
    const tiempoEnEmpresa = Math.floor((fechaActual - fechaIngresoUsuario) / (1000 * 60 * 60 * 24));
    const edadActual = calcularEdadUsuario(usuarioEncontrado.fecha_nacimiento);
    const dataParaPdf = encodeURIComponent(JSON.stringify(usuarioEncontrado));

    console.log("detalles del usuario: ", usuarioEncontrado)

    if (usuarioEncontrado) {
        Swal.fire({
            title: `
                <div style="font-size: 1.8em; font-weight: 700; color: #2C3E50; display: flex; align-items: center; justify-content: center; gap: 12px;">
                    <i class="bi bi-person-circle"></i>Detalles del Usuario
                </div>`,
            html: `
                <div style="font-family: 'Inter', 'Segoe UI', Arial, sans-serif; padding: 20px; background-color: #ECF0F1; border-radius: 15px; box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; color: #34495E;">
                        <div style="background-color: #FFFFFF; padding: 20px; border-radius: 12px; box-shadow: 0 3px 18px rgba(0, 0, 0, 0.05);">
                            <h5 style="color: #3498DB; margin-bottom: 15px; font-weight: 700; border-bottom: 1px solid #EAECEF; padding-bottom: 10px; font-size: 1.1em;">Informacion Personal</h5>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Nombre:</strong> ${usuarioEncontrado.usuario_nombre}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Apellido:</strong> ${usuarioEncontrado.usuario_apellido}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Noº Documento:</strong> ${usuarioEncontrado.dui}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Fecha de Nacimiento:</strong> ${usuarioEncontrado.fecha_nacimiento}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Edad:</strong> ${edadActual} años</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Telefono:</strong> ${usuarioEncontrado.telefono}</p>
                            <p style="margin-bottom: 0; line-height: 1.5;"><strong>Direccion:</strong> ${usuarioEncontrado.direccion}</p>
                        </div>

                        <div style="background-color: #FFFFFF; padding: 20px; border-radius: 12px; box-shadow: 0 3px 18px rgba(0, 0, 0, 0.05);">
                            <h5 style="color: #3498DB; margin-bottom: 15px; font-weight: 700; border-bottom: 1px solid #EAECEF; padding-bottom: 10px; font-size: 1.1em;">Informacion de Cuenta</h5>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Correo:</strong> ${usuarioEncontrado.correo_electronico}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Usuario:</strong> ${usuarioEncontrado.nombreUsuario}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Rol:</strong> ${usuarioEncontrado.nombre_rol}</p>
                            <p style="margin-bottom: 0; line-height: 1.5;">
                                <strong>Estado:</strong>
                                <span style="color: ${usuarioEncontrado.estado == 1 ? '#27AE60' : '#E74C3C'}; font-weight: bold;">
                                ${usuarioEncontrado.estado == 1 ? 'Activo' : 'Inactivo'}
                                </span>
                            </p>
                        </div>

                        <div style="background-color: #FFFFFF; padding: 20px; border-radius: 12px; box-shadow: 0 3px 18px rgba(0, 0, 0, 0.05);">
                            <h5 style="color: #3498DB; margin-bottom: 15px; font-weight: 700; border-bottom: 1px solid #EAECEF; padding-bottom: 10px; font-size: 1.1em;">Detalles Laborales</h5>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Salario:</strong> ${usuarioEncontrado.salario}</p>
                            <p style="margin-bottom: 8px; line-height: 1.5;"><strong>Tipo de Contrato:</strong> ${usuarioEncontrado.tipo_contrato}</p>
                            <p style="margin-bottom: 0; line-height: 1.5;"><strong>Fecha de Ingreso:</strong> ${usuarioEncontrado.fecha_registro}</p>
                            <p style="margin-bottom: 0; line-height: 1.5;"><strong>Tiempo en la empresa:</strong> ${tiempoEnEmpresa} dias</p>
                        </div>

                        <div style="background-color: #FFFFFF; padding: 20px; border-radius: 12px; box-shadow: 0 3px 18px rgba(0, 0, 0, 0.05);">
                            <div class="d-flex gap-3 flex-wrap justify-content-start">
                                <button
                                onclick="window.open('../controllers/pdfUsuario.php?data=${dataParaPdf}')"
                                type="button" class="btn btn-outline-primary" title="Generar informe PDF">
                                    <i class="bi bi-file-earmark-pdf"> </i>Informacion del usuario
                                </button>

                                <button 
                                onclick="window.open('../controllers/pdfSesiones.php?id=${usuarioEncontrado.usuario_id}&nombre=${usuarioEncontrado.usuario_nombre} ${usuarioEncontrado.usuario_apellido}&correo=${usuarioEncontrado.correo_electronico}')" 
                                type="button" class="btn btn-outline-secondary" title="Generar informe de las sesiones del usuario">
                                    <i class="bi bi-file-earmark-pdf"></i> Informe de sesiones
                                </button>
                                
                                
                                <button type="button" class="btn btn-outline-danger" title="Eliminar usuario">
                                    <i class="bi bi-file-earmark-pdf"></i> Ventas del usuario
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            `,
            width: '800px',
            showCloseButton: true,
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'rounded-xl shadow-lg',
                confirmButton: 'btn btn-info'
            },
            backdrop: true
        });

    }
}
function pdfSesion(idUsuario, usuario_nombre) {
    fetch('../controllers/pdfSesiones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ idUsuario, usuario_nombre })
    }).then(res => res.text())
        .then(data => {
            console.log(data)
        })
        .catch(err => console.log(err))
}
//aqui se validan los datos para agregar un usuario nuevo
function validarFormulario() {
    Swal.fire({
        title: 'Nuevo Usuario',
        html: `
                <div style="text-align:left; font-size: 14px;">
                    <div class="d-flex gap-1">
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
                            <label for="dui" class="form-label">No° Documento</label>
                            <input type="text" class="form-control shadow-none" id="dui" placeholder="12345678-9" required
                            maxlength="35">
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo electronico</label>
                            <input type="email" class="form-control shadow-none" id="correo" placeholder="correo@gmail.com" required
                                maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Direccion</label>
                            <select class="form-select shadow-none" id="direccion" name="direccion" aria-label="select for level" required>
                                <option value="">Seleccione un departamento</option>
                                <option value="Ahuachapan">Ahuachapan</option>
                                <option value="Santa Ana">Santa Ana</option>
                                <option value="Sonsonate">Sonsonate</option>
                                <option value="Chalatenango">Chalatenango</option>
                                <option value="La Libertad">La Libertad</option>
                                <option value="San Salvador">San Salvador</option>
                                <option value="Cuscatlan">Cuscatlan</option>
                                <option value="La Paz">La Paz</option>
                                <option value="Cabañas">Cabañas</option>
                                <option value="San Vicente">San Vicente</option>
                                <option value="Usulutan">Usulutan</option>
                                <option value="San Miguel">San Miguel</option>
                                <option value="Morazan">Morazan</option>
                                <option value="La Union">La Union</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="municipio" class="form-label">Municipio</label>
                            <select class="form-select shadow-none" id="municipio" name="municipio" disabled>
                                <option value="">Seleccione un departamento primero</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="nombreUsuario" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control shadow-none" id="nombreUsuario" placeholder="juanRome12" required
                                maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="contraseña" class="form-label">Contraseña</label>
                            <input type="password" class="form-control shadow-none" id="contraseña" placeholder="Escribe aqui"
                                required maxlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control shadow-none" id="fecha_nacimiento" placeholder="Escribe aqui"
                                required maxlength="8">
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="salario" class="form-label">Salario</label>
                            <input type="number" class="form-control shadow-none" id="salario" placeholder="365" required
                                maxlength="15" step="0.01">
                        </div>
                       <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="number" class="form-control shadow-none" id="telefono" placeholder="21212828" required>
                        </div>
                         <div class="mb-3">
                            <label for="contrato" class="form-label">Tipo de contrato</label>
                            <select class="form-select shadow-none" id="contrato" name="contrato">
                                <option value="" disabled selected>Seleccione un contrato</option>
                                <option value="Indefinido">Indefinido</option>
                                <option value="Servicios profesionales">Servicios profesionales</option>
                                <option value="Temporal">Temporal</option>
                            </select>
                        </div>
                    </div>
                    <select class="form-select shadow-none" id="permiso" name="permiso" aria-label="select for level" required>
                        <option value="" selected disabled>Nivel de permiso</option>
                        <option value="administrador">Administrador</option>
                        <option value="empleado">Empleado</option>
                    </select>
                </div>
                        
        `,
        width: '700px',
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
                dui: document.getElementById('dui').value.trim(),
                correo_electronico: document.getElementById('correo').value.trim(),
                direccion: document.getElementById('direccion').value,
                municipio: document.getElementById('municipio').value,
                nombreUsuario: document.getElementById('nombreUsuario').value.trim(),
                contraseña: document.getElementById('contraseña').value.trim(),
                contrato: document.getElementById('contrato').value.trim(),
                fecha_nacimiento: document.getElementById('fecha_nacimiento').value,
                salario: document.getElementById('salario').value.trim(),
                telefono: document.getElementById('telefono').value.trim(),
                nombre_rol: document.getElementById('permiso').value,
                estado: 1
            };

            if (!datos.usuario_nombre || !datos.correo_electronico || !datos.usuario_apellido ||
                !datos.nombreUsuario || !datos.contraseña || !datos.nombre_rol ||
                !datos.dui || !datos.direccion || !datos.municipio || !datos.fecha_nacimiento ||
                !datos.salario || !datos.telefono || !datos.contrato) {
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

            const regexDUI = /^\d{8}-\d$/;
            if (!regexDUI.test(datos.dui)) {
                Swal.showValidationMessage('Ingrese un DUI valido (formato: 12345678-9)');
                return false;
            }

            if (datos.correo_electronico.length > 255 || datos.correo_electronico.length < 6) {
                Swal.showValidationMessage('El correo debe tener entre 6 y 255 caracteres');
                return false;
            }

            const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!regexCorreo.test(datos.correo_electronico)) {
                Swal.showValidationMessage('Ingrese un correo electronico valido');
                return false;
            }
            if (datos.direccion === "") {
                Swal.showValidationMessage('Seleccione un departamento valido');
                return false;
            }
            if (datos.municipio === "") {
                Swal.showValidationMessage('Seleccione un municipio valido');
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

            if (datos.contrato != 'Indefinido' && datos.contrato != 'Servicios profesionales' && datos.contrato != 'Temporal') {
                Swal.showValidationMessage('Seleccione un tipo de contrato valido');
                return false;
            }

            if (!datos.fecha_nacimiento) {
                Swal.showValidationMessage('Seleccione una fecha de nacimiento valida');
                return false;
            }

            const fechaNacimiento = new Date(datos.fecha_nacimiento);
            const hoy = new Date();
            const fechaMinima = new Date('1940-01-01');

            if (isNaN(fechaNacimiento.getTime())) {
                Swal.showValidationMessage('Seleccione una fecha de nacimiento valida');
                return false;
            }

            if (fechaNacimiento > hoy || fechaNacimiento < fechaMinima) {
                Swal.showValidationMessage('La fecha de nacimiento no es valida');
                return false;
            }


            const regexSalario = /^\d+(\.\d{1,2})?$/;
            if (!regexSalario.test(datos.salario) || datos.salario < 0) {
                Swal.showValidationMessage('El salario debe ser un nmero entero valido y con maximo 2 decimales');
                return false;
            }

            const regexTelefono = /^[26789]\d{7}$/;
            if (!regexTelefono.test(datos.telefono)) {
                Swal.showValidationMessage('Ingrese un telefono valido de El Salvador (8 digitos, empieza con 2, 6, 7, 8 o 9)');
                return false;
            }

            return datos;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;
            peticionAgregarUsuario(datos);
        }
    });

    setTimeout(() => {
        const departamentoSelect = document.getElementById('direccion');
        const municipioSelect = document.getElementById('municipio');

        const data = {
            "Ahuachapan": [
                "Ahuachapan", "Apaneca", "Atiquizaya", "Concepcion de Ataco",
                "El Refugio", "Guaymango", "Jujutla", "San Francisco Menendez",
                "San Lorenzo", "San Pedro Puxtla", "Tacuba", "Turin"
            ],
            "Santa Ana": [
                "Candelaria de la Frontera", "Chalchuapa", "Coatepeque", "El Congo",
                "El Porvenir", "Masahuat", "Metapan", "San Antonio Pajonal",
                "San Sebastian Salitrillo", "Santa Ana", "Santa Rosa Guachipilin",
                "Santiago de la Frontera", "Texistepeque"
            ],
            "Sonsonate": [
                "Acajutla", "Armenia", "Caluco", "Cuisnahuat", "Izalco",
                "Juaya", "Nahuizalco", "Nahulingo", "Salcoatitan", "San Antonio del Monte",
                "San Julian", "Santa Catarina Masahuat", "Santa Isabel Ishuatan", "Sonzacate", "Sonsonate"
            ],
            "Chalatenango": [
                "Agua Caliente", "Arcatao", "Azacualpa", "Cancasque",
                "Chalatenango", "Citala", "Comalapa", "Concepcion Quezaltepeque",
                "Dulce Nombre de Maria", "El Carrizal", "El Paraiso", "La Laguna",
                "La Palma", "La Reina", "Las Flores", "Las Vueltas", "Nombre de Jess",
                "Nueva Concepcion", "Nueva Trinidad", "Ojos de Agua", "Potonico",
                "San Antonio de la Cruz", "San Antonio Los Ranchos", "San Fernando",
                "San Francisco Lempa", "San Ignacio", "San Isidro Labrador",
                "San Jose Cancasque", "San Jose Las Flores", "San Luis del Carmen",
                "San Miguel de Mercedes", "San Rafael", "Santa Rita", "Tejutla"
            ],
            "La Libertad": [
                "Antiguo Cuscatlan", "Chiltiupan", "Ciudad Arce", "Colon",
                "Comasagua", "Huizcar", "Jayaque", "Jicalapa", "La Libertad",
                "Nuevo Cuscatlan", "Quezaltepeque", "Sacacoyo", "San Jose Villanueva",
                "San Juan Opico", "San Matias", "San Pablo Tacachico", "Santa Tecla",
                "Talnique", "Tamanique", "Teotepeque", "Tepecoyo", "Zaragoza"
            ],
            "San Salvador": [
                "Aguilares", "Apopa", "Ayutuxtepeque", "Cuscatancingo",
                "Delgado", "El Paisnal", "Guazapa", "Ilopango", "Mejicanos",
                "Nejapa", "Panchimalco", "Rosario de Mora", "San Marcos",
                "San Martin", "San Salvador", "Santiago Texacuangos", "Santo Tomas",
                "Soyapango", "Tonacatepeque"
            ],
            "Cuscatlan": [
                "Candelaria", "Cojutepeque", "El Carmen", "El Rosario",
                "Monte San Juan", "Oratorio de Concepcion", "San Bartolome Perulapia",
                "San Cristobal", "San Jose Guayabal", "San Pedro Perulapan",
                "San Rafael Cedros", "San Ramon", "Santa Cruz Analquito",
                "Santa Cruz Michapa", "Suchitoto", "Tenancingo"
            ],
            "La Paz": [
                "Cuyultitan", "El Rosario", "Jerusalen", "Mercedes La Ceiba",
                "Olocuilta", "Paraiso de Osorio", "San Antonio Masahuat",
                "San Emigdio", "San Francisco Chinameca", "San Juan Nonualco",
                "San Juan Talpa", "San Juan Tepezontes", "San Luis La Herradura",
                "San Luis Talpa", "San Miguel Tepezontes", "San Pedro Masahuat",
                "San Pedro Nonualco", "San Rafael Obrajuelo", "Santa Maria Ostuma",
                "Santiago Nonualco", "Tapalhuaca", "Zacatecoluca"
            ],
            "Cabañas": [
                "Cinquera", "Dolores", "Guacotecti", "Ilobasco",
                "Jutiapa", "San Isidro", "Sensuntepeque", "Tejutepeque", "Victoria"
            ],
            "San Vicente": [
                "Apastepeque", "Guadalupe", "San Cayetano Istepeque",
                "San Esteban Catarina", "San Ildefonso", "San Lorenzo",
                "San Sebastian", "San Vicente", "Santa Clara", "Santo Domingo",
                "Tecoluca", "Tepetitan", "Verapaz"
            ],
            "Usulutan": [
                "Alegria", "Berlin", "California", "Concepcion Batres",
                "El Triunfo", "Ereguayquin", "Estanzuelas", "Jiquilisco",
                "Jucuapa", "Jucuaran", "Mercedes Umaña", "Nueva Granada",
                "Ozatlan", "Puerto El Triunfo", "San Agustin", "San Buenaventura",
                "San Dionisio", "San Francisco Javier", "Santa Elena",
                "Santa Maria", "Santiago de Maria", "Tecapan", "Usulutan"
            ],
            "San Miguel": [
                "Carolina", "Chapeltique", "Chinameca", "Chirilagua",
                "Ciudad Barrios", "Comacaran", "El Transito", "Lolotique",
                "Moncagua", "Nueva Guadalupe", "Nuevo Eden de San Juan",
                "Quelepa", "San Antonio", "San Gerardo", "San Jorge",
                "San Luis de la Reina", "San Miguel", "San Rafael Oriente",
                "Sesori", "Uluazapa"
            ],
            "Morazan": [
                "Arambala", "Cacaopera", "Chilanga", "Corinto", "Delicias de Concepcion",
                "El Divisadero", "El Rosario", "Gualococti", "Guatajiagua",
                "Joateca", "Jocoaitique", "Jocoro", "Lolotiquillo", "Meanguera",
                "Osicala", "Perquin", "San Carlos", "San Fernando", "San Francisco Gotera",
                "San Isidro", "San Simon", "Sensembra", "Sociedad", "Torola",
                "Yamabal", "Yoloaiquin"
            ],
            "La Union": [
                "Anamoros", "Bolivar", "Concepcion de Oriente", "Conchagua",
                "El Carmen", "El Sauce", "Intipuca", "La Union", "Lislique",
                "Meanguera del Golfo", "Nueva Esparta", "Pasaquina", "Poloros",
                "San Alejo", "San Jose", "Santa Rosa de Lima", "Yayantique",
                "Yucuaiquin"
            ]
        };

        if (departamentoSelect && municipioSelect) {
            departamentoSelect.addEventListener('change', function () {
                const selectedDepartamento = this.value;
                municipioSelect.innerHTML = '<option value="" disabled>Seleccione un municipio</option>';
                municipioSelect.disabled = true;

                if (selectedDepartamento && data[selectedDepartamento]) {
                    data[selectedDepartamento].forEach(municipio => {
                        const option = document.createElement('option');
                        option.value = municipio;
                        option.textContent = municipio;
                        municipioSelect.appendChild(option);
                    });
                    municipioSelect.disabled = false;
                }
            });
        }
    }, 100);
}

//despues de validar los datos se llama esta funcion que hace la peticion para agregarlo
function peticionAgregarUsuario(datos) {
    const formData = new FormData();
    for (const clave in datos) {
        if (datos.hasOwnProperty(clave)) {
            formData.append(clave, datos[clave]);
        }
    }
    console.log(datos)
    fetch('../controllers/usuariosController.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            console.log("respuesta de la insercion: ", data);
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
        text: "¡No podras revertir esta accion!",
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
        alertaEsquinaSuperior("error", "Ocurrio un error al buscar la informacion del usuario");
        return;
    }

    let htmlUsuario = `
      <div style="text-align:left; font-size: 14px;">
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="nombreModificado" class="form-label">Nombre</label>
                            <input type="text" class="form-control shadow-none" id="nombreModificado" placeholder="Juan Romeo" required
                                maxlength="35" value="${usuarioEncontrado.usuario_nombre}">
                        </div>
                        <div class="mb-3">
                            <label for="apellidoModificado" class="form-label">Apellido</label>
                            <input type="text" class="form-control shadow-none" id="apellidoModificado" placeholder="Castro Lopez" required
                            maxlength="35" value="${usuarioEncontrado.usuario_apellido}">
                        </div>
                        <div class="mb-3">
                            <label for="duiModificado" class="form-label">No° Documento</label>
                            <input type="text" class="form-control shadow-none" name="duiModificado" id="duiModificado" placeholder="12345678-9" required
                            maxlength="35" value="${usuarioEncontrado.dui}">
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="correoModificado" class="form-label">Correo electronico</label>
                            <input type="email" class="form-control shadow-none" id="correoModificado" placeholder="correo@gmail.com" required
                                maxlength="255" value="${usuarioEncontrado.correo_electronico}">
                        </div>
                        <div class="mb-3">
                            <label for="direccionModificada" class="form-label">Direccion</label>
                            <select class="form-select shadow-none" id="direccionModificada" name="direccionModificada" aria-label="select for level" required>
                                <option value="">Seleccione un departamento</option>
                                <option value="Ahuachapan">Ahuachapan</option>
                                <option value="Santa Ana">Santa Ana</option>
                                <option value="Sonsonate">Sonsonate</option>
                                <option value="Chalatenango">Chalatenango</option>
                                <option value="La Libertad">La Libertad</option>
                                <option value="San Salvador">San Salvador</option>
                                <option value="Cuscatlan">Cuscatlan</option>
                                <option value="La Paz">La Paz</option>
                                <option value="Cabañas">Cabañas</option>
                                <option value="San Vicente">San Vicente</option>
                                <option value="Usulutan">Usulutan</option>
                                <option value="San Miguel">San Miguel</option>
                                <option value="Morazan">Morazan</option>
                                <option value="La Union">La Union</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="municipioModificado" class="form-label">Municipio</label>
                            <select class="form-select shadow-none" id="municipioModificado" name="municipioModificado" disabled>
                                <option value="">Seleccione un departamento primero</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="nombreUsuarioModificado" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control shadow-none" id="nombreUsuarioModificado" placeholder="juanRome12" required
                                maxlength="15" value="${usuarioEncontrado.nombreUsuario}">
                        </div>
                        <div class="mb-3">
                            <label for="contraseñaModificada" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control shadow-none" id="contraseñaModificada" placeholder="Escribe aqui"
                                required maxlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimientoModificada" class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control shadow-none" id="fecha_nacimientoModificada" placeholder="Escribe aqui"
                                required maxlength="8" value="${usuarioEncontrado.fecha_nacimiento}">
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="mb-3">
                            <label for="salarioModificado" class="form-label">Salario</label>
                            <input type="number" class="form-control shadow-none" id="salarioModificado" placeholder="365" required
                                maxlength="15" step="0.01" value="${usuarioEncontrado.salario}">
                        </div>
                       <div class="mb-3">
                            <label for="telefonoModificado" class="form-label">Telefono</label>
                            <input type="number" class="form-control shadow-none" id="telefonoModificado" placeholder="21212828" value="${usuarioEncontrado.telefono}" required>
                        </div>
                         <div class="mb-3">
                            <label for="contratoModificado" class="form-label">Tipo de contrato</label>
                            <select class="form-select shadow-none" id="contratoModificado" name="contratoModificado">
                                <option value="" disabled selected>Seleccione un contrato</option>
                                <option value="Indefinido">Indefinido</option>
                                <option value="Servicios profesionales">Servicios profesionales</option>
                                <option value="Temporal">Temporal</option>
                            </select>
                        </div>
                    </div>
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
        width: '800px',
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
                dui: document.getElementById('duiModificado').value.trim(),
                correo_electronico: document.getElementById('correoModificado').value.trim(),
                direccion: document.getElementById('municipioModificado').value + ", " + document.getElementById('direccionModificada').value,
                nombreUsuario: document.getElementById('nombreUsuarioModificado').value.trim(),
                contraseña: document.getElementById('contraseñaModificada').value.trim(),
                tipo_contrato: document.getElementById('contratoModificado').value.trim(),
                fecha_nacimiento: document.getElementById('fecha_nacimientoModificada').value,
                salario: document.getElementById('salarioModificado').value.trim(),
                telefono: document.getElementById('telefonoModificado').value.trim(),
                nombre_rol: document.getElementById('permisoModificado').value,
                estado: estadoInt
            };

            if (!datos.usuario_nombre || !datos.correo_electronico || !datos.usuario_apellido ||
                !datos.nombreUsuario || !datos.nombre_rol ||
                !datos.dui || !datos.direccion || !datos.fecha_nacimiento ||
                !datos.salario || !datos.telefono || !datos.tipo_contrato) {
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
            // Regex para el dui que serian 8 digitos + guion + 1 digito: ej 12345678-9
            const regexDUI = /^\d{8}-\d$/;
            if (!regexDUI.test(datos.dui)) {
                Swal.showValidationMessage('Ingrese un DUI valido (formato: 12345678-9)');
                return false;
            }

            if (datos.correo_electronico.length > 255 || datos.correo_electronico.length < 6) {
                Swal.showValidationMessage('El correo debe tener entre 6 y 255 caracteres');
                return false;
            }
            // Regex correo
            const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!regexCorreo.test(datos.correo_electronico)) {
                Swal.showValidationMessage('Ingrese un correo electronico valido');
                return false;
            }
            if (datos.direccion === "") {
                Swal.showValidationMessage('Seleccione un departamento valido');
                return false;
            }
            if (datos.municipio === "") {
                Swal.showValidationMessage('Seleccione un municipio valido');
                return false;
            }
            if (datos.nombreUsuario.length > 15 || datos.nombreUsuario.length < 4) {
                Swal.showValidationMessage('El nombre de usuario debe tener entre 4 y 15 caracteres');
                return false;
            }
            const regexTelefono = /^[26789]\d{7}$/;
            if (!regexTelefono.test(datos.telefono)) {
                Swal.showValidationMessage('Ingrese un telefono valido de El Salvador (8 digitos, empieza con 2, 6, 7, 8 o 9)');
                return false;
            }

            if (datos.tipo_contrato != 'Indefinido' && datos.tipo_contrato != 'Servicios profesionales' && datos.tipo_contrato != 'Temporal') {
                Swal.showValidationMessage('Seleccione un tipo de contrato valido');
                return false;
            }

            // Validar fecha de nacimiento (que sea fecha valida y no vacia)
            if (!datos.fecha_nacimiento) {
                Swal.showValidationMessage('Seleccione una fecha de nacimiento valida');
                return false;
            }
            // Validar fecha de nacimiento que sea fecha valida y no este en el futuro
            const fechaNacimiento = new Date(datos.fecha_nacimiento);
            const hoy = new Date();
            const fechaMinima = new Date('1940-01-01');

            // isNaN chequea que la fecha exista
            if (isNaN(fechaNacimiento.getTime())) {
                Swal.showValidationMessage('Seleccione una fecha de nacimiento valida');
                return false;
            }
            // Compara solo la parte de la fecha (sin hora)
            if (fechaNacimiento > hoy || fechaNacimiento < fechaMinima) {
                Swal.showValidationMessage('La fecha de nacimiento no es valida');
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

    setTimeout(() => {
        const departamentoSelect = document.getElementById('direccionModificada');
        const municipioSelect = document.getElementById('municipioModificado');
        const [municipio, departamento] = usuarioEncontrado.direccion.split(',').map(s => s.trim());
        const contratoSelect = document.getElementById('contratoModificado');
        contratoSelect.value = usuarioEncontrado.tipo_contrato;

        const dataDepartamento = {
            "Ahuachapan": [
                "Ahuachapan", "Apaneca", "Atiquizaya", "Concepcion de Ataco",
                "El Refugio", "Guaymango", "Jujutla", "San Francisco Menendez",
                "San Lorenzo", "San Pedro Puxtla", "Tacuba", "Turin"
            ],
            "Santa Ana": [
                "Candelaria de la Frontera", "Chalchuapa", "Coatepeque", "El Congo",
                "El Porvenir", "Masahuat", "Metapan", "San Antonio Pajonal",
                "San Sebastian Salitrillo", "Santa Ana", "Santa Rosa Guachipilin",
                "Santiago de la Frontera", "Texistepeque"
            ],
            "Sonsonate": [
                "Acajutla", "Armenia", "Caluco", "Cuisnahuat", "Izalco",
                "Juaya", "Nahuizalco", "Nahulingo", "Salcoatitan", "San Antonio del Monte",
                "San Julian", "Santa Catarina Masahuat", "Santa Isabel Ishuatan", "Sonzacate", "Sonsonate"
            ],
            "Chalatenango": [
                "Agua Caliente", "Arcatao", "Azacualpa", "Cancasque",
                "Chalatenango", "Citala", "Comalapa", "Concepcion Quezaltepeque",
                "Dulce Nombre de Maria", "El Carrizal", "El Paraiso", "La Laguna",
                "La Palma", "La Reina", "Las Flores", "Las Vueltas", "Nombre de Jess",
                "Nueva Concepcion", "Nueva Trinidad", "Ojos de Agua", "Potonico",
                "San Antonio de la Cruz", "San Antonio Los Ranchos", "San Fernando",
                "San Francisco Lempa", "San Ignacio", "San Isidro Labrador",
                "San Jose Cancasque", "San Jose Las Flores", "San Luis del Carmen",
                "San Miguel de Mercedes", "San Rafael", "Santa Rita", "Tejutla"
            ],
            "La Libertad": [
                "Antiguo Cuscatlan", "Chiltiupan", "Ciudad Arce", "Colon",
                "Comasagua", "Huizcar", "Jayaque", "Jicalapa", "La Libertad",
                "Nuevo Cuscatlan", "Quezaltepeque", "Sacacoyo", "San Jose Villanueva",
                "San Juan Opico", "San Matias", "San Pablo Tacachico", "Santa Tecla",
                "Talnique", "Tamanique", "Teotepeque", "Tepecoyo", "Zaragoza"
            ],
            "San Salvador": [
                "Aguilares", "Apopa", "Ayutuxtepeque", "Cuscatancingo",
                "Delgado", "El Paisnal", "Guazapa", "Ilopango", "Mejicanos",
                "Nejapa", "Panchimalco", "Rosario de Mora", "San Marcos",
                "San Martin", "San Salvador", "Santiago Texacuangos", "Santo Tomas",
                "Soyapango", "Tonacatepeque"
            ],
            "Cuscatlan": [
                "Candelaria", "Cojutepeque", "El Carmen", "El Rosario",
                "Monte San Juan", "Oratorio de Concepcion", "San Bartolome Perulapia",
                "San Cristobal", "San Jose Guayabal", "San Pedro Perulapan",
                "San Rafael Cedros", "San Ramon", "Santa Cruz Analquito",
                "Santa Cruz Michapa", "Suchitoto", "Tenancingo"
            ],
            "La Paz": [
                "Cuyultitan", "El Rosario", "Jerusalen", "Mercedes La Ceiba",
                "Olocuilta", "Paraiso de Osorio", "San Antonio Masahuat",
                "San Emigdio", "San Francisco Chinameca", "San Juan Nonualco",
                "San Juan Talpa", "San Juan Tepezontes", "San Luis La Herradura",
                "San Luis Talpa", "San Miguel Tepezontes", "San Pedro Masahuat",
                "San Pedro Nonualco", "San Rafael Obrajuelo", "Santa Maria Ostuma",
                "Santiago Nonualco", "Tapalhuaca", "Zacatecoluca"
            ],
            "Cabañas": [
                "Cinquera", "Dolores", "Guacotecti", "Ilobasco",
                "Jutiapa", "San Isidro", "Sensuntepeque", "Tejutepeque", "Victoria"
            ],
            "San Vicente": [
                "Apastepeque", "Guadalupe", "San Cayetano Istepeque",
                "San Esteban Catarina", "San Ildefonso", "San Lorenzo",
                "San Sebastian", "San Vicente", "Santa Clara", "Santo Domingo",
                "Tecoluca", "Tepetitan", "Verapaz"
            ],
            "Usulutan": [
                "Alegria", "Berlin", "California", "Concepcion Batres",
                "El Triunfo", "Ereguayquin", "Estanzuelas", "Jiquilisco",
                "Jucuapa", "Jucuaran", "Mercedes Umaña", "Nueva Granada",
                "Ozatlan", "Puerto El Triunfo", "San Agustin", "San Buenaventura",
                "San Dionisio", "San Francisco Javier", "Santa Elena",
                "Santa Maria", "Santiago de Maria", "Tecapan", "Usulutan"
            ],
            "San Miguel": [
                "Carolina", "Chapeltique", "Chinameca", "Chirilagua",
                "Ciudad Barrios", "Comacaran", "El Transito", "Lolotique",
                "Moncagua", "Nueva Guadalupe", "Nuevo Eden de San Juan",
                "Quelepa", "San Antonio", "San Gerardo", "San Jorge",
                "San Luis de la Reina", "San Miguel", "San Rafael Oriente",
                "Sesori", "Uluazapa"
            ],
            "Morazan": [
                "Arambala", "Cacaopera", "Chilanga", "Corinto", "Delicias de Concepcion",
                "El Divisadero", "El Rosario", "Gualococti", "Guatajiagua",
                "Joateca", "Jocoaitique", "Jocoro", "Lolotiquillo", "Meanguera",
                "Osicala", "Perquin", "San Carlos", "San Fernando", "San Francisco Gotera",
                "San Isidro", "San Simon", "Sensembra", "Sociedad", "Torola",
                "Yamabal", "Yoloaiquin"
            ],
            "La Union": [
                "Anamoros", "Bolivar", "Concepcion de Oriente", "Conchagua",
                "El Carmen", "El Sauce", "Intipuca", "La Union", "Lislique",
                "Meanguera del Golfo", "Nueva Esparta", "Pasaquina", "Poloros",
                "San Alejo", "San Jose", "Santa Rosa de Lima", "Yayantique",
                "Yucuaiquin"
            ]
        }

        if (departamento && municipio) {
            departamentoSelect.value = departamento;
            municipioSelect.disabled = false;

            dataDepartamento[departamento].forEach(muni => {
                const option = document.createElement('option');
                option.value = muni;
                option.textContent = muni;
                municipioSelect.appendChild(option);
            })
            municipioSelect.value = municipio;
        }

        departamentoSelect.addEventListener('change', function () {
            const selected = this.value;
            municipioSelect.innerHTML = '';

            if (dataDepartamento[selected]) {
                municipioSelect.disabled = false;
                dataDepartamento[selected].forEach(m => {
                    const option = document.createElement('option');
                    option.value = m;
                    option.textContent = m;
                    municipioSelect.appendChild(option);
                });
            } else {
                municipioSelect.disabled = true;
                municipioSelect.innerHTML = '<option value="">Seleccione un departamento primero</option>';
            }
        });

    }, 50);

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
        .catch(err => {
            console.log(err)
            alertaEsquinaSuperior('error', 'Ocurrio un error al intentar buscar el usuario');
        })
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
            <td  class="responsiv-tabla">${usuario.usuario_nombre} ${usuario.usuario_apellido}</td>
            <td  class="responsiv-tabla">${usuario.correo_electronico}</td>
            <td class="responsiv-tabla">${usuario.nombreUsuario}</td>
            <td class="responsiv-tabla">${usuario.nombre_rol}</td>
            <td class="responsiv-tabla">${estadoTexto}</td>
            <td class="responsiv-tabla">${usuario.fecha_registro}</td>
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