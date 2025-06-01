// SIEMPRE AGREGAR ESTA SECCION A CADA ARCHIVO QUE TENGA EL NAVBAR //
const contenedorFecha = document.getElementById('current-date');
const fechaData = new Date();
const formatoFecha = fechaData.toLocaleDateString('es-ES', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});
contenedorFecha.textContent = formatoFecha;

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
// ------------------------------------------------------------------- //

let proveedoresData = [];
const esAdmin = document.body.getAttribute('data-admin') === '1';

document.addEventListener('DOMContentLoaded', () => {
    mostrarProveedores();
});

async function cargarProveedores() {
    try {
        const response = await fetch('../controllers/proveedoresController.php');
        if (!response.ok) {
            mensajeError('Ocurrió un error al cargar los datos');
            return [];
        }
        const data = await response.json();
        return data;
    } catch (error) {
        mensajeError('Ocurrió un error al llamar los datos');
        return [];
    }
}

async function mostrarProveedores() {
    proveedoresData = await cargarProveedores();
    if (!proveedoresData || proveedoresData.length === 0) {
        mensajeError('No hay proveedores disponibles');
        return;
    } else {
        document.getElementById('mensajeSinProveedores').style.display = 'none';
    }
    const tabla = document.getElementById('tablaProveedores');
    tabla.innerHTML = '';
    proveedoresData.forEach((proveedor, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${proveedor.nombre}</td>
            <td>${proveedor.telefono}</td>
            <td>${proveedor.correo}</td>
            <td>${proveedor.direccion}</td>
            ${esAdmin ? `
            <td>
                <button class="btn btn-sm btn-primary editar-btn" data-id="${proveedor.id}"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger eliminar-btn" data-id="${proveedor.id}"><i class="bi bi-trash"></i></button>
            </td>
            ` : ''}
        `;
        tabla.appendChild(tr);
    });
    if (esAdmin) asociarEventos();

    // Botones de exportar PDF/Excel (solo si es admin)
    if (esAdmin) {
        const btnPDF = document.getElementById('btnExportarProveedoresPDF');
        const btnExcel = document.getElementById('btnExportarProveedoresExcel');
        if (btnPDF) btnPDF.onclick = function () { exportarProveedores('pdf'); };
        if (btnExcel) btnExcel.onclick = function () { exportarProveedores('excel'); };
    }
}

function mensajeError(mensaje) {
    const messageElement = document.getElementById('mensajeSinProveedores');
    messageElement.style.display = 'block';
    messageElement.innerText = mensaje;
}

document.getElementById('formAgregarProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const datos = {
        nombre: form.nombre.value.trim(),
        telefono: form.telefono.value.trim(),
        correo: form.correo.value.trim(),
        direccion: form.direccion.value.trim()
    };
    if (!datos.nombre || !datos.telefono || !datos.correo || !datos.direccion) {
        alertaEsquinaSuperior('error', 'Completa todos los campos');
        return;
    }
    fetch('../controllers/proveedoresController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        alertaEsquinaSuperior(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            form.reset();
            mostrarProveedores();
        }
    })
    .catch(() => alertaEsquinaSuperior('error', 'Error al agregar proveedor'));
});

function asociarEventos() {
    document.querySelectorAll('.editar-btn').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            const proveedor = proveedoresData.find(p => p.id == id);
            if (!proveedor) return;
            document.getElementById('edit-id').value = proveedor.id;
            document.getElementById('edit-nombre').value = proveedor.nombre;
            document.getElementById('edit-telefono').value = proveedor.telefono;
            document.getElementById('edit-correo').value = proveedor.correo;
            document.getElementById('edit-direccion').value = proveedor.direccion;
            document.getElementById('modalEditarProveedor').style.display = 'block';
        }
    });
    document.querySelectorAll('.eliminar-btn').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            Swal.fire({
                title: '¿Deseas eliminar este proveedor?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../controllers/proveedoresController.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        alertaEsquinaSuperior(data.success ? 'success' : 'error', data.message);
                        mostrarProveedores();
                    })
                    .catch(() => alertaEsquinaSuperior('error', 'Error al eliminar proveedor'));
                }
            });
        }
    });
}

document.getElementById('cerrarModalProveedor').onclick = function () {
    document.getElementById('modalEditarProveedor').style.display = 'none';
};
window.onclick = function (event) {
    if (event.target == document.getElementById('modalEditarProveedor')) {
        document.getElementById('modalEditarProveedor').style.display = 'none';
    }
};

document.getElementById('formEditarProveedor').onsubmit = function(e) {
    e.preventDefault();
    const form = e.target;
    const datos = {
        id: form['id'].value,
        nombre: form['nombre'].value.trim(),
        telefono: form['telefono'].value.trim(),
        correo: form['correo'].value.trim(),
        direccion: form['direccion'].value.trim()
    };
    fetch('../controllers/proveedoresController.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        alertaEsquinaSuperior(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            document.getElementById('modalEditarProveedor').style.display = 'none';
            mostrarProveedores();
        }
    })
    .catch(() => alertaEsquinaSuperior('error', 'Error al editar proveedor'));
};

document.getElementById('btnBuscarProveedor').addEventListener('click', function(e) {
    e.preventDefault();
    proveedoresFiltro();
});
document.getElementById('buscarProveedor').addEventListener('change', proveedoresFiltro);

function proveedoresFiltro() {
    const filtro = document.getElementById('buscarProveedor').value.trim();
    if (filtro == "") {
        mostrarProveedores();
        return;
    }
    fetch('../controllers/proveedoresController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ filtroBusqueda: filtro })
    })
    .then(res => res.json())
    .then(data => {
        const tabla = document.getElementById('tablaProveedores');
        tabla.innerHTML = '';
        if (!data || data.length === 0) {
            mensajeError('No se encontraron proveedores con ese filtro.');
            return;
        }
        document.getElementById('mensajeSinProveedores').style.display = 'none';
        data.forEach((proveedor, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${proveedor.nombre}</td>
                <td>${proveedor.telefono}</td>
                <td>${proveedor.correo}</td>
                <td>${proveedor.direccion}</td>
                <td>
                    <button class="btn btn-sm btn-primary editar-btn" data-id="${proveedor.id}"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn btn-sm btn-danger eliminar-btn" data-id="${proveedor.id}"><i class="bi bi-trash"></i></button>
                </td>
            `;
            tabla.appendChild(tr);
        });
        proveedoresData = data;
        asociarEventos();
    })
    .catch(() => mensajeError('Error al buscar proveedores'));
}

function exportarProveedores(tipo) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../controllers/proveedoresController.php';
    form.target = '_blank';

    const inputExportar = document.createElement('input');
    inputExportar.type = 'hidden';
    inputExportar.name = 'exportar';
    inputExportar.value = tipo;
    form.appendChild(inputExportar);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
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
