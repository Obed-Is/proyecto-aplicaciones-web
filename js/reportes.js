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

        // Reemplaza los datos de ejemplo por la carga real desde el backend
        let ventas = [];
        let ventasFiltradas = [];

        async function cargarVentas() {
            try {
                const res = await fetch('../controllers/ventasController.php?all=1');
                ventas = await res.json();
                ventasFiltradas = [...ventas];
                renderTabla(ventasFiltradas);
            } catch (e) {
                ventas = [];
                ventasFiltradas = [];
                renderTabla([]);
            }
        }

        function renderTabla(data) {
            tablaVentas.innerHTML = '';
            if (!data || data.length === 0) {
                tablaVentas.innerHTML = `
  <tr>
    <td colspan="9" class="text-center text-muted fst-italic">No hay ventas para mostrar</td>
  </tr>`;
                return;
            }

            data.forEach((venta, idx) => {
                tablaVentas.innerHTML += `
  <tr>
    <td>${idx + 1}</td>
    <td>${venta.fecha ? venta.fecha.substring(0, 19) : ''}</td>
    <td>${venta.cliente}</td>
    <td>$${parseFloat(venta.monto_total).toFixed(2)}</td>
    <td>$${parseFloat(venta.monto_cliente).toFixed(2)}</td>
    <td>$${parseFloat(venta.monto_devuelto).toFixed(2)}</td>
    <td>${venta.correo_cliente}</td>
    <td>${venta.usuario || ''}</td>
    <td class="actions text-center">
      <button title="Ver detalles" class="action-btn detalles-btn" data-index="${idx}" aria-label="Ver detalles venta ${idx + 1}">
        <i class="bi bi-eye-fill"></i>
      </button>
    </td>
  </tr>
`;
            });
        }

        // Filtro por cliente y fechas
        function filtrarVentas() {
            const clienteTexto = inputCliente.value.trim().toLowerCase();
            const inicio = fechaInicio.value ? new Date(fechaInicio.value) : null;
            const fin = fechaFin.value ? new Date(fechaFin.value) : null;

            ventasFiltradas = ventas.filter(v => {
                const cumpleCliente = v.cliente.toLowerCase().includes(clienteTexto);
                const fechaVenta = v.fecha ? new Date(v.fecha) : null;
                const cumpleFechaInicio = inicio ? (fechaVenta && fechaVenta >= inicio) : true;
                const cumpleFechaFin = fin ? (fechaVenta && fechaVenta <= fin) : true;
                return cumpleCliente && cumpleFechaInicio && cumpleFechaFin;
            });
            renderTabla(ventasFiltradas);
        }

        // Limpiar filtros
        function limpiarFiltros() {
            inputCliente.value = '';
            fechaInicio.value = '';
            fechaFin.value = '';
            ventasFiltradas = [...ventas];
            renderTabla(ventasFiltradas);
        }

        // Eventos para filtros
        btnFiltrar.addEventListener('click', filtrarVentas);
        btnLimpiar.addEventListener('click', limpiarFiltros);

        // Delegado para botones editar y eliminar
        tablaVentas.addEventListener('click', e => {
            if (e.target.closest('.detalles-btn')) {
                const index = Number(e.target.closest('.detalles-btn').dataset.index);
                mostrarDetallesVenta(index);
            }
        });

        function mostrarDetallesVenta(idx) {
            const venta = ventasFiltradas[idx];
            if (!venta) return;
            let detallesHtml = '';
            if (venta.detalles && venta.detalles.length > 0) {
                detallesHtml = `
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ${venta.detalles.map(d => `
                    <tr>
                        <td>${d.nombre}</td>
                        <td>${d.cantidad}</td>
                        <td>$${parseFloat(d.precio).toFixed(2)}</td>
                        <td>$${(d.cantidad * d.precio).toFixed(2)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
            } else {
                detallesHtml = '<p>No hay detalles para esta venta.</p>';
            }
            Swal.fire({
                title: `Detalles de la venta`,
                html: `
        <p><strong>Cliente:</strong> ${venta.cliente}</p>
        <p><strong>Correo:</strong> ${venta.correo_cliente}</p>
        <p><strong>Usuario:</strong> ${venta.usuario || ''}</p>
        <p><strong>Fecha:</strong> ${venta.fecha ? venta.fecha.substring(0, 19) : ''}</p>
        <p><strong>Total:</strong> $${parseFloat(venta.monto_total).toFixed(2)}</p>
        <hr>
        ${detallesHtml}
    `,
                width: 700,
                confirmButtonText: 'Cerrar'
            });
        }

        // Inicializar la tabla con los datos reales
        cargarVentas();

        // --- EXPORTAR PDF Y EXCEL ---
        document.getElementById('btnExportarPDF').addEventListener('click', function () {
            exportarReporte('pdf');
        });
        document.getElementById('btnExportarExcel').addEventListener('click', function () {
            exportarReporte('excel');
        });

        function exportarReporte(tipo) {
            // Obtiene los filtros actuales
            const cliente = document.getElementById('inputCliente').value.trim();
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;

            // Crea un formulario temporal para enviar por POST y abrir en nueva pestaña
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = tipo === 'pdf' ? '../controllers/pdfReporteVentas.php' : '../controllers/excelReporteVentas.php';
            form.target = '_blank';

            const inputCliente = document.createElement('input');
            inputCliente.type = 'hidden';
            inputCliente.name = 'cliente';
            inputCliente.value = cliente;
            form.appendChild(inputCliente);

            const inputFechaInicio = document.createElement('input');
            inputFechaInicio.type = 'hidden';
            inputFechaInicio.name = 'fechaInicio';
            inputFechaInicio.value = fechaInicio;
            form.appendChild(inputFechaInicio);

            const inputFechaFin = document.createElement('input');
            inputFechaFin.type = 'hidden';
            inputFechaFin.name = 'fechaFin';
            inputFechaFin.value = fechaFin;
            form.appendChild(inputFechaFin);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }