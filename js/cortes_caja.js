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

let cortesData = [];

document.addEventListener('DOMContentLoaded', () => {
    mostrarCortes();

    // --- Filtros de fecha para cortes de caja ---
    const fechaInicioCorte = document.getElementById('fechaInicioCorte');
    const fechaFinCorte = document.getElementById('fechaFinCorte');
    const btnFiltrarCortes = document.getElementById('btnFiltrarCortes');
    const btnLimpiarCortes = document.getElementById('btnLimpiarCortes');
    const filtroUsuarioCorte = document.getElementById('filtroUsuarioCorte'); // Puede ser null si no es admin

    btnFiltrarCortes.addEventListener('click', () => {
        filtrarCortesPorFechaYUsuario();
    });
    btnLimpiarCortes.addEventListener('click', () => {
        fechaInicioCorte.value = '';
        fechaFinCorte.value = '';
        if (filtroUsuarioCorte) filtroUsuarioCorte.value = '';
        // Mostrar todos los cortes al limpiar filtros
        renderTablaCortes(cortesData);
    });

});

async function mostrarCortes() {
    try {
        const res = await fetch('../controllers/cortesCajaController.php');
        const cortes = await res.json();
        cortesData = cortes; // Guardar todos los cortes para filtrar en frontend
        renderTablaCortes(cortesData);
    } catch (error) {
        document.getElementById('mensajeSinCortes').style.display = 'block';
        document.getElementById('mensajeSinCortes').innerText = 'Error de conexión o de servidor.';
    }
}

function renderTablaCortes(cortes) {
    const tabla = document.getElementById('tablaCortesCaja');
    tabla.innerHTML = '';
    if (!cortes || cortes.length === 0) {
        document.getElementById('mensajeSinCortes').style.display = 'block';
        document.getElementById('mensajeSinCortes').innerText = 'No hay cortes de caja registrados.';
        return;
    }
    document.getElementById('mensajeSinCortes').style.display = 'none';
    cortes.forEach((corte, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <!--<td>${corte.id}</td>-->
            <td>${corte.fecha}</td>
            <td>${corte.hora_inicio}</td>
            <td>${corte.hora_fin || '-'}</td>
            <td>$${parseFloat(corte.monto_inicial).toFixed(2)}</td>
            <td>$${parseFloat(corte.monto_final || 0).toFixed(2)}</td>
            <td>${corte.ventas || 0}</td>
            <td>$${parseFloat(corte.total).toFixed(2)}</td>
            <td>${corte.usuario}</td>
            <td>${corte.estado || '-'}</td>
        `;
        tabla.appendChild(tr);
    });
}

function filtrarCortesPorFechaYUsuario() {
    const inicio = fechaInicioCorte.value;
    const fin = fechaFinCorte.value;
    let filtrados = cortesData;
    // Filtro por usuario solo si el campo existe (admin)
    const filtroUsuarioCorte = document.getElementById('filtroUsuarioCorte');
    let usuarioFiltro = filtroUsuarioCorte ? filtroUsuarioCorte.value.trim().toLowerCase() : '';

    if (inicio) {
        filtrados = filtrados.filter(c => c.fecha >= inicio);
    }
    if (fin) {
        filtrados = filtrados.filter(c => c.fecha <= fin);
    }
    if (usuarioFiltro) {
        // Solo usuarios cuyo nombre EMPIEZA con el filtro
        filtrados = filtrados.filter(c => (c.usuario || '').toLowerCase().startsWith(usuarioFiltro));
    }
    renderTablaCortes(filtrados);
}
