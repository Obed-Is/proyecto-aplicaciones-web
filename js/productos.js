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

// Abrir modal y rellenar datos
document.querySelectorAll('.editar-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('edit-id').value = this.dataset.id;
        document.getElementById('edit-codigo').value = this.dataset.codigo;
        document.getElementById('edit-nombre').value = this.dataset.nombre;
        document.getElementById('edit-descripcion').value = this.dataset.descripcion;
        document.getElementById('edit-precio').value = this.dataset.precio;
        document.getElementById('edit-stock').value = this.dataset.stock;
        document.getElementById('edit-stock_minimo').value = this.dataset.stock_minimo;
        document.getElementById('edit-estado').value = this.dataset.estado;
        // Seleccionar la categoría correcta en el select
        let selectCat = document.getElementById('edit-idCategoria');
        selectCat.value = this.dataset.idcategoria;
        // Mostrar imagen actual si existe
        const fila = this.closest('tr');
        const imgTag = fila.querySelector('img');
        const imagenActualDiv = document.getElementById('imagen-actual');
        if (imgTag) {
            imagenActualDiv.innerHTML = '<p>Imagen actual:</p>' + imgTag.outerHTML;
        } else {
            imagenActualDiv.innerHTML = '<p>Sin imagen</p>';
        }
        document.getElementById('edit-imagen').value = ''; // Limpiar input file
        document.getElementById('modalEditar').style.display = 'block';
    });
});
// Cerrar modal
document.getElementById('cerrarModal').onclick = function () {
    document.getElementById('modalEditar').style.display = 'none';
};
window.onclick = function (event) {
    if (event.target == document.getElementById('modalEditar')) {
        document.getElementById('modalEditar').style.display = 'none';
    }
};

// Enviar formulario de edición por POST (con archivos)
document.getElementById('formEditarProducto').onsubmit = function (e) {
    // Permite el envío normal del formulario (POST con enctype multipart/form-data)
    // Si quieres hacerlo por AJAX, deberías usar FormData y fetch aquí.
    // Por defecto, el formulario recargará la página y mostrará el mensaje del controlador.
};

// Cargar imágenes reales de productos después de renderizar la tabla
document.addEventListener('DOMContentLoaded', function () {
    // Obtener todos los td con clase td-imagen
    document.querySelectorAll('.td-imagen').forEach(function (td) {
        const id = td.getAttribute('data-id');
        // Petición para obtener la imagen real (si existe)
        fetch('../controllers/productosController.php?getImage=1&id=' + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.imgSrc) {
                    const img = td.querySelector('img');
                    img.src = data.imgSrc;
                    img.alt = "Imagen producto";
                }
            })
            .catch(() => {
                // Si falla, se queda la imagen sustituta
            });
    });
});

// Cambiar el evento al botón correcto y limpiar la tabla si no hay resultados
document.getElementById('btnBuscarProducto').addEventListener('click', function(e) {
    e.preventDefault();
    productosFiltro();
});
document.getElementById('buscarProducto').addEventListener('change', productosFiltro);

function productosFiltro() {
    const filtroBusqueda = document.getElementById('buscarProducto').value.trim();

    if (filtroBusqueda == "") {
        window.location.reload();
        return;
    }

    fetch('../controllers/productosController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ filtroBusqueda })
    }).then(res => res.json())
        .then(data => {
            mostrarProductos(data);
        })
        .catch(err => console.log(err))
}

// Función para mostrar productos en la tabla
function mostrarProductos(productos) {
    const tabla = document.getElementById('tablaProductos');
    const mensajeSinProductos = document.getElementById('mensajeSinProductos');
    tabla.innerHTML = '';
    if (!productos || productos.length === 0) {
        mensajeSinProductos.textContent = 'No se encontraron productos con ese filtro.';
        mensajeSinProductos.style.display = 'block';
        return;
    }
    mensajeSinProductos.style.display = 'none';
    productos.forEach(producto => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${producto.id}</td>
            <td>${producto.codigo}</td>
            <td>${producto.nombre}</td>
            <td>${producto.descripcion}</td>
            <td>${producto.precio}</td>
            <td>${producto.stock}</td>
            <td>${producto.estado == 1 ? 'Activo' : 'Inactivo'}</td>
            <td>${producto.idCategoria}</td>
            <td>${producto.stock_minimo}</td>
            <td class="td-imagen" data-id="${producto.id}">
                <img src="../img/imgFaltante.png" alt="Cargando..." width="60" height="60" class="img-sustituta rounded" />
            </td>
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
                    data-stock_minimo="${producto.stock_minimo}"
                ><i class="bi bi-pencil-square"></i></a>
                <a href="../controllers/productosController.php?action=delete&id=${producto.id}" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este producto?')"><i class="bi bi-trash"></i></a>
            </td>
        `;
        tabla.appendChild(tr);
    });

    // Recargar imágenes de los productos filtrados
    document.querySelectorAll('.td-imagen').forEach(function (td) {
        const id = td.getAttribute('data-id');
        fetch('../controllers/productosController.php?getImage=1&id=' + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.imgSrc) {
                    const img = td.querySelector('img');
                    img.src = data.imgSrc;
                    img.alt = "Imagen producto";
                }
            });
    });

    // Volver a asociar eventos a los nuevos botones de editar
    document.querySelectorAll('.editar-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
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
            const imagenActualDiv = document.getElementById('imagen-actual');
            imagenActualDiv.innerHTML = '<p>Imagen actual:</p><img src="../img/imgFaltante.png" width="60" height="60" class="img-sustituta rounded" />';
            document.getElementById('edit-imagen').value = '';
            document.getElementById('modalEditar').style.display = 'block';
        });
    });
}