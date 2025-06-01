let categoriasDataAll = [];

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

document.addEventListener('DOMContentLoaded', () => {
    obtenerCategorias();
})


function validarFormulario() {
    Swal.fire({
        title: '<h5 class="modal-title">Agregar Nueva Categoria</h5>',
        html: `
            <input type="hidden" id="categoryId">
            <div class="mb-3">
                <label for="categoriaNombre" class="form-label">Nombre de la Categoria</label>
                <input type="text" class="form-control shadow-none" id="categoriaNombre" required>
            </div>
            <div class="mb-3">
                <label for="categoriaDescripcion" class="form-label">Descripcion</label>
                <textarea class="form-control shadow-none" id="categoriaDescripcion" rows="3"></textarea>
            </div>
    `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle"></i> Confirmar',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary',
            popup: 'p-4'
        },
        preConfirm: () => {
            const datos = {
                nombre_categoria: document.getElementById('categoriaNombre').value.trim(),
                descripcion_categoria: document.getElementById('categoriaDescripcion').value.trim()
            };

            if (!datos.nombre_categoria || !datos.descripcion_categoria) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
            }

            if (datos.nombre_categoria.length < 3 || datos.nombre_categoria.length > 35) {
                Swal.showValidationMessage('El nombre de la categoria debe tener entre 3 y 35 caracteres');
                return false;
            }

            if (datos.descripcion_categoria.length < 5 || datos.descripcion_categoria.length > 90) {
                Swal.showValidationMessage('La descripcion debe tener entre 5 y 90 caracteres');
                return false;
            }

            return datos;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;
            console.log(datos);
            peticionAgregarCategoria(datos);
        }
    });
}

function peticionAgregarCategoria(data) {
    const formData = new FormData();
    formData.append('nombre_categoria', data.nombre_categoria);
    formData.append('descripcion_categoria', data.descripcion_categoria);

    fetch('../controllers/categoriasController.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
        .then(data => {
            console.log('respuesta del fetch a nueva categoria: ', data);
            if (data) {
                alertaEsquinaSuperior(data.icon, data.message);
                console.log('respuesta del fetch de agregar categoria: ', data.message);
                obtenerCategorias();
            }
        })
        .catch(err => {
            console.log("error en el fetch de crear categoria: ", err);
            alertaEsquinaSuperior("error", "Ocurrio un error al intentar crear la categoria");
        })
}

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

async function obtenerCategorias() {
    try {
        const response = await fetch('../controllers/categoriasController.php');

        if (!response.ok) {
            alertaEsquinaSuperior('error', 'Ocurrio un error al cargar los datos');
            mostrarCategorias([]);
        }
        const data = await response.json();
        console.log("categorias cargadas: ", data);
        categoriasDataAll = data;
        mostrarCategorias(data);
    } catch (error) {
        console.log(error);
        alertaEsquinaSuperior('error', 'Ocurrio un error al llamar los datos');
        mostrarCategorias([]);
    }
}

const esAdmin = document.body.getAttribute('data-admin') === '1';

function mostrarCategorias(categoriasData) {
    const tablaCategorias = document.querySelector('#tablaCategorias tbody');
    const mensajeSinCategoria = document.getElementById('mensajeSinCategoria');

    tablaCategorias.innerHTML = '';

    if (categoriasData.length === 0) {
        mensajeSinCategoria.classList.remove('d-none');
        tablaCategorias.classList.add('d-none');
    } else {
        mensajeSinCategoria.classList.add('d-none');
        tablaCategorias.classList.remove('d-none');

        categoriasData.forEach((categoria, indice) => {
            const row = tablaCategorias.insertRow();
            row.innerHTML = `
                <td>${indice + 1}</td>
                <td class="text-truncate" style="max-width: 200px; overflow: hidden;">${categoria.nombre_categoria}</td>
                <td class="text-truncate" style="max-width: 200px; overflow: hidden;">${categoria.descripcion_categoria}</td>
                ${esAdmin ? `
                <td class="text-center action-buttons">
                    <button class="btn btn-light" onclick="return editarCategoria(${categoria.id_categoria})">
                        <i class="bi bi-pencil-fill text-warning"></i>
                    </button>
                    <button class="btn btn-light" onclick="return eliminarCategoria(${categoria.id_categoria})">
                        <i class="bi bi-trash-fill text-danger"></i>
                    </button>
                </td>
                ` : ''}
            `;


        });
    }
}


const btnBuscarCategoria = document.getElementById('btnBuscar');

document.getElementById('btnBuscar').addEventListener('click', categoriasFiltro);
document.getElementById('buscarCategoria').addEventListener('change', categoriasFiltro);

function categoriasFiltro() {
    const filtroBusqueda = document.getElementById('buscarCategoria').value.trim();

    if (filtroBusqueda == "") {
        obtenerCategorias();
        return;
    }

    fetch('../controllers/categoriasController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ filtroBusqueda })
    }).then(res => res.json())
        .then(data => {
            console.log("categorias del filtro: ", data);
            mostrarCategorias(data);
        })
        .catch(err => console.log(err))
}

function editarCategoria(idCategoria) {
    const categoriaEncontrada = categoriasDataAll.find((data) => data.id_categoria === idCategoria);
    console.log(categoriaEncontrada);

    Swal.fire({
        title: '<h5 class="modal-title">Modificar categoria</h5>',
        html: `
            <input type="hidden" id="categoryId">
            <div class="mb-3">
                <label for="categoriaNombreModificar" class="form-label">Nombre de la Categoria</label>
                <input type="text" class="form-control shadow-none" id="categoriaNombreModificar" value="${categoriaEncontrada.nombre_categoria}" required>
            </div>
            <div class="mb-3">
                <label for="categoriaDescripcionModificar" class="form-label">Descripcion</label>
                <input class="form-control shadow-none" id="categoriaDescripcionModificar" rows="3" value="${categoriaEncontrada.descripcion_categoria}">
            </div>
    `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle"></i> Confirmar',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary',
            popup: 'p-4'
        },
        preConfirm: () => {
            const datos = {
                id_categoria: idCategoria,
                nombre_categoriaModificar: document.getElementById('categoriaNombreModificar').value.trim(),
                descripcion_categoriaModificar: document.getElementById('categoriaDescripcionModificar').value.trim()
            };

            if (!datos.nombre_categoriaModificar || !datos.descripcion_categoriaModificar) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
            }

            if (datos.nombre_categoriaModificar.length < 3 || datos.nombre_categoriaModificar.length > 35) {
                Swal.showValidationMessage('El nombre de la categoria debe tener entre 3 y 35 caracteres');
                return false;
            }

            if (datos.descripcion_categoriaModificar.length < 5 || datos.descripcion_categoriaModificar.length > 90) {
                Swal.showValidationMessage('La descripcion debe tener entre 5 y 90 caracteres');
                return false;
            }

            return datos;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;
            console.log('cambios: ', datos)
            peticionEditarCategoria(datos);
        }
    });
}

function peticionEditarCategoria(datos) {
    fetch('../controllers/categoriasController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    }).then(res => res.json())
        .then(data => {
            if (data) {
                alertaEsquinaSuperior(data.success, data.message);
                console.log('respuesta del fetch de editar categoria: ', data);
                obtenerCategorias();
            }
        })
        .catch(err => {
            console.log("error en el fetch de editar categoria: ", err);
            alertaEsquinaSuperior("error", "Ocurrio un error al intentar crear la categoria");
        })
}

function eliminarCategoria(idCategoria) {
    const categoriaEncontrada = categoriasDataAll.find((data) => data.id_categoria === idCategoria);

    Swal.fire({
        title: '¿Deseas eliminar esta categoria?',
        text: "¡No podras revertir esta accion!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../controllers/categoriasController.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idCategoria })
            })
                .then(res => res.json())
                .then(data => {
                    console.log("Respuesta de la eliminacion: ", data);

                    Swal.fire({
                        title: data.titulo,
                        text: data.message,
                        icon: data.success,
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Ok',
                    }).then(() => {
                        obtenerCategorias();
                    })
                })
                .catch(err => {
                    console.log('Error al fetch de eliminacion: ', err)
                    alertaEsquinaSuperior("error", "Ha ocurrido un error al intentar eliminar la categoria");
                })
        }
    });
}