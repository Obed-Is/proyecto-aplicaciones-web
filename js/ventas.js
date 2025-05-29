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


// ------ Elementos del cliente ----------

const clienteNombreInput = document.getElementById('clienteNombre');
const clienteCorreoInput = document.getElementById('clienteCorreo');
// ------- Elementos para agregar producto y buscar -----------
const btnGuardarCliente = document.getElementById('btnGuardarCliente');
const buscarProductoInput = document.getElementById('buscarProducto');
const btnBuscarProducto = document.getElementById('btnBuscarProducto');
const productosEncontradosDiv = document.getElementById('productosEncontrados');
const noProductosFoundP = document.getElementById('noProductosFound');
// ------------ Elementos de la tabla de productos ------------
const cuerpoTablaVenta = document.getElementById('cuerpoTablaVenta');
const emptyCartRow = document.getElementById('emptyCartRow');
const totalVentaSpan = document.getElementById('totalVenta');
const pagoClienteInput = document.getElementById('pagoCliente');
const msjMontoInvalido = document.getElementById('msjMontoInvalido');
const cambioDevueltoSpan = document.getElementById('cambioDevuelto');
// ------------- BOTON PARA GENERAR LA VENTA ----------------
const btnGenerarTicket = document.getElementById('btnGenerarTicket');

// ------------- AQUI CONTIENE LA VENTA ACTUAL Y LA ACTUALIZACION DE LOS PRODUCTOS ----------------
let productosEnVenta = [];
let productosDisponibles = [];

// ------------- SE OBTIENE LOS PRODUCTOS PARA NO HACER TANTA CONSULTA AL BUSCARLOS ----------------
async function obtenerProductos() {
    const res = await fetch('../controllers/ventasController.php');
    try {
        productosDisponibles = await res.json();
    } catch (error) {
        productosDisponibles = [];
        console.error("Error al parsear JSON:", error);
    }
    console.log("PRODUCTOS DISPONIBLES: ",productosDisponibles);
}

obtenerProductos();

// ------------- ACTUALIZAR LA TABLA DE PRODUCTOS ----------------
const actualizarTablaVenta = () => {
    cuerpoTablaVenta.innerHTML = ''; 
    if (productosEnVenta.length === 0) {
        cuerpoTablaVenta.innerHTML = `
                                    <tr id="emptyCartRow">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-cart-x fs-5 d-block mb-2"></i>
                                            Aun no hay productos en la venta.
                                        </td>
                                    </tr>
            `;
        btnGenerarTicket.disabled = true;
        pagoClienteInput.disabled = true;
        pagoClienteInput.value = '';
        cambioDevueltoSpan.innerText = 0.00;
        cambioDevueltoSpan.innerText = 0.00;
    } else {
        emptyCartRow.style.display = 'none';
        productosEnVenta.forEach((item) => {
            const row = cuerpoTablaVenta.insertRow();
            row.innerHTML = `
                        <td>${item.nombre}</td>
                        <td class="text-end">$${item.precio.toFixed(2)}</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm mx-auto cantidad-producto" data-id="${item.id}" value="${item.cantidad}" min="1" max="${item.stockDisponible}">
                        </td>
                        <td class="text-end">$${(item.precio * item.cantidad).toFixed(2)}</td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm eliminar-producto" data-id="${item.id}" title="Eliminar producto">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </td>
                    `;
        });
        pagoClienteInput.disabled = false;
    }

    // -------- CALCULAR EL TOTAL DE LA VENTA AUTOMATICAMENTE-----------
    let total = 0;
    productosEnVenta.forEach(item => {
        total += item.precio * item.cantidad;
    });
    totalVentaSpan.textContent = total.toFixed(2);

    // ----------- CANTIDAD DEL PRODUCTO EN VENTA -----------
    document.querySelectorAll('.cantidad-producto').forEach(input => {
        input.addEventListener('change', (e) => {
            const productId = parseInt(e.target.dataset.id);
            const newQuantity = parseInt(e.target.value);
            const productIndex = productosEnVenta.findIndex(p => p.id === productId);

            if (productIndex !== -1) {
                if (newQuantity > productosEnVenta[productIndex].stockDisponible) {
                    alertaEsquinaSuperior(
                        'warning', 'Stock insuficiente',
                        `Solo quedan ${productosEnVenta[productIndex].stockDisponible} unidades de ${productosEnVenta[productIndex].nombre}.`
                    );
                    e.target.value = productosEnVenta[productIndex].cantidad; // Revert
                } else if (newQuantity <= 0) {
                    Swal.fire({
                        title: '¿Eliminar producto?',
                        text: `¿Estas seguro de que quieres eliminar "${productosEnVenta[productIndex].nombre}" de la venta?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#7f8c8d',
                        confirmButtonText: 'Si, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            productosEnVenta.splice(productIndex, 1);
                            alertaEsquinaSuperior('success', 'Producto Eliminado', 'El producto ha sido quitado de la venta.');
                            actualizarTablaVenta();
                        } else {
                            e.target.value = productosEnVenta[productIndex].cantidad; // Revert
                        }
                    });
                } else {
                    productosEnVenta[productIndex].cantidad = newQuantity;
                    actualizarTablaVenta();
                }
            }
        });
    });

    // ----------- ELIMINAR EL PRODUCTO DE LA TABLA DE VENTA ----------------
    document.querySelectorAll('.eliminar-producto').forEach(button => {
        button.addEventListener('click', (e) => {
            const productId = parseInt(button.dataset.id);
            const product = productosEnVenta.find(p => p.id === productId);
            Swal.fire({
                title: '¿Eliminar producto?',
                text: `¿Estas seguro de que quieres eliminar "${product.nombre}" de la venta?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#7f8c8d',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
            }).then((result) => {
                if (result.isConfirmed) {
                    productosEnVenta = productosEnVenta.filter(item => item.id !== productId);
                    alertaEsquinaSuperior('success', 'El producto fue eliminado de la venta correctamente');
                    actualizarTablaVenta();
                }
            });
        });
    });
};

// ---------------GUARDAR CLIENTE---------------- 
btnGuardarCliente.addEventListener('click', () => {
    const nombre = clienteNombreInput.value.trim();
    const correo = clienteCorreoInput.value.trim();

    if (nombre === '' || nombre.length < 3) {
        alertaEsquinaSuperior('error', 'Datos del cliente', 'Nombre de Cliente Requerido, debe contener al menos 3 caracteres', 'Nombre de Cliente Requerido, debe contener al menos 3 caracteres')
        return;
    }

    if (correo === '' || !/^[\w.-]+@([\w-]+\.)+[\w-]{2,4}$/.test(correo)) {
        alertaEsquinaSuperior('error', 'Datos del cliente', 'Correo Electronico Invalido')
        return;
    }
    alertaEsquinaSuperior('success', 'Datos del cliente', 'Cliente asignado correctamente');
});

// -------- BUSCAR PRODUCTO -----------
let tiempoBusquedaFiltro;
btnBuscarProducto.addEventListener('input', buscarProductoFn);
buscarProductoInput.addEventListener('keydown', () => {
    clearTimeout(tiempoBusquedaFiltro);
    tiempoBusquedaFiltro = setTimeout(() => {
        buscarProductoFn();
    }, 200);
});

function buscarProductoFn() {
    const productoBuscar = buscarProductoInput.value.trim().toLowerCase();
    productosEncontradosDiv.innerHTML = '';
    noProductosFoundP.style.display = 'none';

    if (productoBuscar === '') {
        noProductosFoundP.style.display = 'block';
        return;
    }

    const resultados = productosDisponibles.filter(producto =>
        producto.nombre.toLowerCase().includes(productoBuscar) || producto.codigo.includes(productoBuscar)
    );

    if (resultados.length === 0) {
        productosEncontradosDiv.innerHTML = '<p class="text-center text-muted p-3 mb-0">No se encontraron productos con ese criterio.</p>';
    } else {
        resultados.forEach(producto => {
            const item = document.createElement('a');
            item.href = '#';
            item.classList.add('list-group-item', 'list-group-item-action', 'd-flex', 'justify-content-between', 'align-items-center', 'py-3');
            item.dataset.id = producto.id;
            item.dataset.nombre = producto.nombre;
            item.dataset.precio = producto.precio;
            item.dataset.stock = producto.stock;
            item.innerHTML = `
                        <div>
                            <h6 class="mb-0 text-dark">${producto.nombre}</h6>
                            <small class="text-muted">$${producto.precio.toFixed(2)}</small>
                        </div>
                        <span class="badge bg-primary text-white rounded-pill px-3 py-2">Stock: ${producto.stock}</span>
                    `;
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const id = parseInt(item.dataset.id);
                const nombre = item.dataset.nombre;
                const precio = parseFloat(item.dataset.precio);
                const stock = parseInt(item.dataset.stock);

                const productoExistente = productosEnVenta.find(p => p.id === id);

                if (productoExistente) {
                    if (productoExistente.cantidad < stock) {
                        productoExistente.cantidad++;
                        alertaEsquinaSuperior('success', 'Informacion producto', 'Producto agregado');
                    } else {
                        alertaEsquinaSuperior('warning', 'Informacion producto', `Ya has agregado el stock maximo disponible (${stock}) de "${nombre}"`);
                    }
                } else {
                    if (stock > 0) {
                        productosEnVenta.push({
                            id: id,
                            nombre: nombre,
                            precio: precio,
                            cantidad: 1,
                            stockDisponible: stock
                        });
                        alertaEsquinaSuperior('success', 'Exito', 'Producto agregado');
                    } else {
                        alertaEsquinaSuperior('warning', 'Alerta producto', 'Producto Sin Stock');
                    }
                }
                actualizarTablaVenta();
            });
            productosEncontradosDiv.appendChild(item);
        });
    }
}

// ----------- CALCULAR PAGO DEL CLIENTE Y VUELTO -----------------
pagoClienteInput.addEventListener('keydown', (e) => {
    const totalVenta = parseFloat(totalVentaSpan.textContent);
    const pagoCliente = parseFloat(pagoClienteInput.value);

    if (e.key === 'Enter') {
        if (isNaN(pagoCliente) || pagoCliente < 0) {
            msjMontoInvalido.style.display = 'block';
            cambioDevueltoSpan.textContent = '0.00';
            btnGenerarTicket.disabled = true;
            return;
        }

        if (pagoCliente < totalVenta) {
            alertaEsquinaSuperior('warning', 'Pago Insuficiente', `El pago recibido es menor al total de la venta. Faltan $${(totalVenta - pagoCliente).toFixed(2)}.`);
            msjMontoInvalido.style.display = 'block';
            cambioDevueltoSpan.textContent = '0.00';
            btnGenerarTicket.disabled = true;
            return;
        }
        msjMontoInvalido.style.display = 'none';
        const cambio = pagoCliente - totalVenta;
        cambioDevueltoSpan.textContent = cambio.toFixed(2);

        btnGenerarTicket.disabled = false;
        // alertaEsquinaSuperior('success', 'cambio calculado', 'cambio del cliente calculado bien');
    }
});

// --------------- GENERAR VENTA Y TICKET -----------------
btnGenerarTicket.addEventListener('click', () => {
    if (productosEnVenta.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Carrito de Venta Vacio',
            text: 'No hay productos en la venta para generar un ticket. Agrega algunos primero.',
            confirmButtonText: 'Entendido',
            customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
    }

    const totalVenta = parseFloat(totalVentaSpan.textContent);
    const pagoCliente = parseFloat(pagoClienteInput.value);
    const cambioDevuelto = parseFloat(cambioDevueltoSpan.textContent);
    const nombreCliente = clienteNombreInput.value.trim();
    const correoCliente = clienteCorreoInput.value.trim();

    if (isNaN(pagoCliente) || pagoCliente < totalVenta) {
        Swal.fire({
            icon: 'error',
            title: 'Pago Pendiente',
            text: 'El pago del cliente no es suficiente o no se ha ingresado un monto valido para finalizar la venta.',
            confirmButtonText: 'Ingresar Pago Completo',
            customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
    }

    if (!nombreCliente || !correoCliente) {
        Swal.fire({
            icon: 'error',
            title: 'Sin cliente',
            text: 'Debe ingresar los datos del cliente',
            confirmButtonText: 'Entendido',
            customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
    }

    const dataDeVenta = {
        cliente: nombreCliente,
        correo: correoCliente,
        productos: productosEnVenta,
        pagoCliente,
        cambioDevuelto,
        totalVenta
    };

    console.log('venta: ', dataDeVenta)
    realizarVenta(dataDeVenta);
    obtenerProductos();
    limpiarCampos();
});

async function realizarVenta(ventaData) {
    try {
        const response = await fetch('../controllers/ventasController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(ventaData),
        });

        if (!response.ok) {
            alertaEsquinaSuperior('error', 'Venta rechazada', 'Ocurrio un error al intentar guardar la venta');
        }

        const result = await response.json();
        if (result.success && result.redirect) {
            alertaEsquinaSuperior('success', 'Venta exitosa', 'Se creo la venta correctamente');
            window.open(result.redirect, '_blank');
        } else {
            alertaEsquinaSuperior('error', 'Error en venta', 'Ocurrio un error al intentar crear la venta');
        }
        console.log('Venta realizada:', result);
    } catch (error) {
        console.log('error en el catch de la venta: ',error);
        alertaEsquinaSuperior('error', 'Venta rechazada', 'Ocurrio un error al intentar realizar la venta');
    }
}


function alertaEsquinaSuperior(icono, titulo, mensaje) {
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
        title: titulo,
        text: mensaje,
    });
}

// ---------- RESETEA LOS CAMPOS -----------
function limpiarCampos() {
    clienteNombreInput.value = '';
    clienteCorreoInput.value = '';
    // ------- Elementos para agregar producto y buscar -----------
    buscarProductoInput.value = '';
    cuerpoTablaVenta.innerHTML = `
                                    <tr id="emptyCartRow">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-cart-x fs-5 d-block mb-2"></i>
                                            Aun no hay productos en la venta.
                                        </td>
                                    </tr>
    `;
    noProductosFoundP.style.display = 'block';
    // ------------ Elementos de la tabla de productos ------------
    productosEncontradosDiv.innerHTML = '';
    totalVentaSpan.innerText = '0.00';
    pagoClienteInput.value = '';
    pagoClienteInput.disabled = true;
    cambioDevueltoSpan.innerText = '0.00';
    // ------------- BOTON PARA GENERAR LA VENTA ----------------
    btnGenerarTicket.disabled = true;
}