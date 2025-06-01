const contenedorFecha = document.getElementById('current-date');
const fechaData = new Date();
const formatoFecha = fechaData.toLocaleDateString('es-ES', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});
contenedorFecha.textContent = formatoFecha;

<<<<<<< Updated upstream
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
=======
// Solo agregar listener si existe el botón logout
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
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
}
>>>>>>> Stashed changes

document.querySelectorAll('.editar-btn').forEach(btn => {
    btn.addEventListener('click', async function (e) {
        e.preventDefault();
        document.getElementById('edit-id').value = this.dataset.id;
        document.getElementById('edit-codigo').value = this.dataset.codigo;
        document.getElementById('edit-nombre').value = this.dataset.nombre;
        document.getElementById('edit-descripcion').value = this.dataset.descripcion;
        document.getElementById('edit-precio').value = this.dataset.precio;
        document.getElementById('edit-stock').value = this.dataset.stock;
        document.getElementById('edit-stock_minimo').value = this.dataset.stock_minimo;
        document.getElementById('edit-estado').value = this.dataset.estado;
        let selectCat = document.getElementById('edit-idCategoria');
        selectCat.value = this.dataset.idcategoria;
        let selectProv = document.getElementById('edit-idProveedor');
        selectProv.value = this.dataset.idproveedor;
        const imagenActualDiv = document.getElementById('imagen-actual');
        imagenActualDiv.innerHTML = '<p>Cargando imagen...</p>';
        try {
            const res = await fetch('../controllers/productosController.php?getImage=1&id=' + encodeURIComponent(this.dataset.id));
            const data = await res.json();
            if (data && data.success && data.imgSrc) {
                imagenActualDiv.innerHTML = '<p>Imagen actual:</p><img src="' + data.imgSrc + '" width="60" height="60" class="img-sustituta rounded" />';
            } else {
                imagenActualDiv.innerHTML = '<p>Sin imagen</p>';
            }
        } catch {
            imagenActualDiv.innerHTML = '<p>Sin imagen</p>';
        }
        document.getElementById('edit-imagen').value = '';
        document.getElementById('modalEditar').style.display = 'block';
    });
});
document.getElementById('cerrarModal').onclick = function () {
    document.getElementById('modalEditar').style.display = 'none';
};
window.onclick = function (event) {
    if (event.target == document.getElementById('modalEditar')) {
        document.getElementById('modalEditar').style.display = 'none';
    }
};

function alertaEsquinaSuperior(icono, mensaje) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 6000,
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

// Envío de edición por AJAX
document.getElementById('formEditarProducto').onsubmit = async function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const res = await fetch('../controllers/productosController.php', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    if (data.success) {
        alertaEsquinaSuperior('success', data.message);
        form.reset();
        document.getElementById('modalEditar').style.display = 'none';
        await mostrarProductos();
    } else {
        alertaEsquinaSuperior('error', data.message || 'Error al editar producto');
    }
};

async function cargarCategoriasYProveedores() {
    const catRes = await fetch('../controllers/categoriasController.php');
    let categorias = [];
    try { categorias = await catRes.json(); } catch {}
    const selectCat = document.getElementById('idCategoria');
    const selectCatEdit = document.getElementById('edit-idCategoria');
    if (selectCat) selectCat.innerHTML = '<option value="">Seleccione una categoría</option>';
    if (selectCatEdit) selectCatEdit.innerHTML = '<option value="">Seleccione una categoría</option>';
    if (Array.isArray(categorias)) {
        categorias.forEach(cat => {
            if (selectCat) selectCat.innerHTML += `<option value="${cat.id_categoria}">${cat.nombre_categoria}</option>`;
            if (selectCatEdit) selectCatEdit.innerHTML += `<option value="${cat.id_categoria}">${cat.nombre_categoria}</option>`;
        });
    }

    const provRes = await fetch('../controllers/proveedoresController.php');
    let proveedores = [];
    try { proveedores = await provRes.json(); } catch {}
    const selectProv = document.getElementById('idProveedor');
    const selectProvEdit = document.getElementById('edit-idProveedor');
    if (selectProv) selectProv.innerHTML = '<option value="">Seleccione un proveedor</option>';
    if (selectProvEdit) selectProvEdit.innerHTML = '<option value="">Seleccione un proveedor</option>';
    if (Array.isArray(proveedores)) {
        proveedores.forEach(prov => {
            if (selectProv) selectProv.innerHTML += `<option value="${prov.id}">${prov.nombre}</option>`;
            if (selectProvEdit) selectProvEdit.innerHTML += `<option value="${prov.id}">${prov.nombre}</option>`;
        });
    }
}

async function mostrarProductos(productos = null) {
    if (!productos) {
        const res = await fetch('../controllers/productosController.php');
        try { productos = await res.json(); } catch { productos = []; }
    }
    const tabla = document.getElementById('tablaProductos');
    const mensajeSinProductos = document.getElementById('mensajeSinProductos');
    tabla.innerHTML = '';
    if (!productos || productos.length === 0) {
        mensajeSinProductos.textContent = 'No se encontraron productos.';
        mensajeSinProductos.style.display = 'block';
        return;
    }
    mensajeSinProductos.style.display = 'none';
    productos.forEach(producto => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${producto.codigo}</td>
            <td>${producto.nombre}</td>
            <td>${producto.descripcion}</td>
            <td>${producto.precio}</td>
            <td>${producto.stock}</td>
            <td>${producto.estado == 1 ? 'Activo' : 'Inactivo'}</td>
            <td>${producto.nombre_categoria || producto.idCategoria || ''}</td>
            <td>${producto.proveedor_nombre || ''}</td>
            <td>${producto.stock_minimo}</td>
            <td class="td-imagen" data-id="${producto.id}">
                <img src="../img/imgFaltante.png" alt="Sin imagen" width="60" height="60" class="img-sustituta rounded" />
            </td>
            ${esAdmin ? `
            <td>
                <a href="#" class="btn btn-sm btn-primary editar-btn"
                    data-id="${producto.id}"
                    data-codigo="${producto.codigo}"
                    data-nombre="${producto.nombre}"
                    data-descripcion="${producto.descripcion}"
                    data-precio="${producto.precio}"
                    data-stock="${producto.stock}"
                    data-estado="${producto.estado}"
                    data-idcategoria="${producto.idCategoria}"
                    data-idproveedor="${producto.idProveedor}"
                    data-stock_minimo="${producto.stock_minimo}"
                ><i class="bi bi-pencil-square"></i></a>
                <a href="../controllers/productosController.php?action=delete&id=${producto.id}" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este producto?')"><i class="bi bi-trash"></i></a>
            </td>
            ` : ''}
        `;
        tabla.appendChild(tr);
    });

    document.querySelectorAll('.td-imagen').forEach(function (td) {
        const id = td.getAttribute('data-id');
        fetch('../controllers/productosController.php?getImage=1&id=' + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                const img = td.querySelector('img');
                if (data && data.success && data.imgSrc) {
                    img.src = data.imgSrc;
                    img.alt = "Imagen producto";
                } else {
                    img.src = "../img/imgFaltante.png";
                    img.alt = "Sin imagen";
                }
            })
            .catch(() => {
                const img = td.querySelector('img');
                img.src = "../img/imgFaltante.png";
                img.alt = "Sin imagen";
            });
    });

    document.querySelectorAll('.editar-btn').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-codigo').value = this.dataset.codigo;
            document.getElementById('edit-nombre').value = this.dataset.nombre;
            document.getElementById('edit-descripcion').value = this.dataset.descripcion;
            document.getElementById('edit-precio').value = this.dataset.precio;
            document.getElementById('edit-stock').value = this.dataset.stock;
            document.getElementById('edit-stock_minimo').value = this.dataset.stock_minimo;
            document.getElementById('edit-estado').value = this.dataset.estado;
            let selectCat = document.getElementById('edit-idCategoria');
            selectCat.value = this.dataset.idcategoria;
            let selectProv = document.getElementById('edit-idProveedor');
            selectProv.value = this.dataset.idproveedor;
            const imagenActualDiv = document.getElementById('imagen-actual');
            imagenActualDiv.innerHTML = '<p>Cargando imagen...</p>';
            try {
                const res = await fetch('../controllers/productosController.php?getImage=1&id=' + encodeURIComponent(this.dataset.id));
                const data = await res.json();
                if (data && data.success && data.imgSrc) {
                    imagenActualDiv.innerHTML = '<p>Imagen actual:</p><img src="' + data.imgSrc + '" width="60" height="60" class="img-sustituta rounded" />';
                } else {
                    imagenActualDiv.innerHTML = '<p>Sin imagen</p>';
                }
            } catch {
                imagenActualDiv.innerHTML = '<p>Sin imagen</p>';
            }
            document.getElementById('edit-imagen').value = '';
            document.getElementById('modalEditar').style.display = 'block';
        });
    });
}

document.addEventListener('DOMContentLoaded', async function () {
    await cargarCategoriasYProveedores();
    await mostrarProductos();
    document.querySelectorAll('.td-imagen').forEach(function (td) {
        const id = td.getAttribute('data-id');
        fetch('../controllers/productosController.php?getImage=1&id=' + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                const img = td.querySelector('img');
                if (data && data.success && data.imgSrc) {
                    img.src = data.imgSrc;
                    img.alt = "Imagen producto";
                } else {
                    img.src = "../img/imgFaltante.png";
                    img.alt = "Sin imagen";
                }
            })
            .catch(() => {
                const img = td.querySelector('img');
                img.src = "../img/imgFaltante.png";
                img.alt = "Sin imagen";
            });
    });
});

// Solo agregar listener si existe el formulario de agregar producto
const formAgregarProducto = document.getElementById('formAgregarProducto');
if (formAgregarProducto) {
    formAgregarProducto.addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const res = await fetch('../controllers/productosController.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alertaEsquinaSuperior('success', data.message);
            form.reset();
            await mostrarProductos();
        } else {
            alertaEsquinaSuperior('error', data.message || 'No se pudo agregar el producto');
        }
    });
}

// Solo agregar listeners de búsqueda si existen los elementos
const btnBuscarProducto = document.getElementById('btnBuscarProducto');
const buscarProducto = document.getElementById('buscarProducto');
if (btnBuscarProducto && buscarProducto) {
    btnBuscarProducto.addEventListener('click', function(e) {
        e.preventDefault();
        productosFiltro();
    });
    buscarProducto.addEventListener('change', productosFiltro);
}

function productosFiltro() {
    const filtroBusqueda = document.getElementById('buscarProducto').value.trim();

    if (filtroBusqueda == "") {
        mostrarProductos();
        return;
    }

    fetch('../controllers/productosController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ filtroBusqueda })
    })
    .then(res => res.json())
    .then(data => {
        mostrarProductos(data);
        if (!data || data.length === 0) {
            alertaEsquinaSuperior('info', 'No se encontraron productos con ese filtro.');
        }
    })
    .catch(err => {
        alertaEsquinaSuperior('error', 'Ocurrió un error al buscar productos');
        console.log(err);
    });
}

// Exportar productos a Excel
const btnExportarProductosExcel = document.getElementById('btnExportarProductosExcel');
if (btnExportarProductosExcel) {
    btnExportarProductosExcel.addEventListener('click', function(e) {
        e.preventDefault();
        // Crea un formulario temporal para enviar por POST y abrir en nueva pestaña
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../controllers/productosController.php';
        form.target = '_blank';
        const inputExportar = document.createElement('input');
        inputExportar.type = 'hidden';
        inputExportar.name = 'exportar';
        inputExportar.value = 'excel';
        form.appendChild(inputExportar);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });
}

// Importar productos desde Excel
const inputImportarProductos = document.getElementById('inputImportarProductos');
const formImportarProductos = document.getElementById('formImportarProductos');
if (inputImportarProductos && formImportarProductos) {
    inputImportarProductos.addEventListener('change', function() {
        if (!inputImportarProductos.files.length) return;
        const formData = new FormData();
        formData.append('importar', 'excel');
        formData.append('archivo', inputImportarProductos.files[0]);
        fetch('../controllers/productosController.php', {
            method: 'POST',
            body: formData
        })
        .then(async res => {
            // Lee el cuerpo solo una vez como texto
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Respuesta no JSON:', text);
                alertaEsquinaSuperior('error', 'Respuesta inesperada del servidor');
                return;
            }
            if (!data.success && data.errores && data.errores.length > 0) {
                console.error('Errores de importación:', data.errores);
            }
            alertaEsquinaSuperior(data.success ? 'success' : 'error', data.message);
            if (data.success) mostrarProductos();
        })
        .catch((err) => {
            alertaEsquinaSuperior('error', 'Error al importar productos');
            console.error('Error al importar productos:', err);
        });
        inputImportarProductos.value = '';
    });
}