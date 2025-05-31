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
document.addEventListener('DOMContentLoaded', () => {
    mostrarCortes();
    let corteActivo = null;

    fetch('../controllers/cortesCajaController.php?activo=1')
        .then(res => res.json())
        .then(corte => {
            // Si no hay corte, corte será null o {}
            if (corte && corte.estado) {
                corteActivo = corte;
                actualizarBotonesCorte(corteActivo);
                if (corteActivo.monto_inicial) {
                    document.getElementById('monto_inicial').value = corteActivo.monto_inicial;
                    document.getElementById('monto_inicial').disabled = true;
                }
            } else {
                // No hay corte activo
                corteActivo = null;
                actualizarBotonesCorte({});
                document.getElementById('monto_inicial').value = '';
                document.getElementById('monto_inicial').disabled = false;
            }
        });

    document.getElementById('btnIniciarCorte').addEventListener('click', function() {
        const montoInicial = document.getElementById('monto_inicial').value;
        if (!montoInicial) return;
        fetch('../controllers/cortesCajaController.php', {
            method: 'POST',
            body: new URLSearchParams({
                accion: 'iniciar',
                monto_inicial: montoInicial
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                corteActivo = data.corte;
                actualizarBotonesCorte(corteActivo);
                mostrarCortes();
            }
        });
    });

    let btnPausarReanudar = document.getElementById('btnPausarReanudarCorte');
    if (!btnPausarReanudar) {
        btnPausarReanudar = document.createElement('button');
        btnPausarReanudar.type = 'button';
        btnPausarReanudar.id = 'btnPausarReanudarCorte';
        btnPausarReanudar.className = 'btn btn-warning me-2';
        btnPausarReanudar.style.display = 'none';
        btnPausarReanudar.innerHTML = '<i class="bi bi-pause-circle"></i> Pausar Corte';
        document.querySelector('#formCorteCaja .col-md-8').appendChild(btnPausarReanudar);
    }

    btnPausarReanudar.onclick = function() {
        if (btnPausarReanudar.dataset.estado === 'activo') {
            fetch('../controllers/cortesCajaController.php', {
                method: 'POST',
                body: new URLSearchParams({
                    accion: 'pausar'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    corteActivo = data.corte;
                    actualizarBotonesCorte(corteActivo);
                    mostrarCortes();
                }
            });
        } else if (btnPausarReanudar.dataset.estado === 'pausado') {
            fetch('../controllers/cortesCajaController.php', {
                method: 'POST',
                body: new URLSearchParams({
                    accion: 'reanudar'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    corteActivo = data.corte;
                    actualizarBotonesCorte(corteActivo);
                    mostrarCortes();
                }
            });
        }
    };

    document.getElementById('btnPausarCorte').style.display = 'none';

    document.getElementById('btnFinalizarCorte').addEventListener('click', function() {
        Swal.fire({
            title: '¿Finalizar Corte?',
            text: '¿Estás seguro de finalizar el corte de caja? Los datos se calcularán automáticamente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('../controllers/cortesCajaController.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        accion: 'finalizar'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        corteActivo = null;
                        actualizarBotonesCorte({});
                        document.getElementById('formCorteCaja').reset();
                        mostrarCortes();
                    }
                });
            }
        });
    });

    // Eliminar el evento para mostrar el modal de información de corte
});

async function mostrarCortes() {
    try {
        const res = await fetch('../controllers/cortesCajaController.php');
        const cortes = await res.json();
        const tabla = document.getElementById('tablaCortesCaja');
        tabla.innerHTML = '';
        if (!cortes || cortes.length === 0) {
            document.getElementById('mensajeSinCortes').style.display = 'block';
            document.getElementById('mensajeSinCortes').innerText = 'No hay cortes de caja registrados.';
            return;
        }
        document.getElementById('mensajeSinCortes').style.display = 'none';
        cortes.forEach(corte => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${corte.id}</td>
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
    } catch (error) {
        document.getElementById('mensajeSinCortes').style.display = 'block';
        document.getElementById('mensajeSinCortes').innerText = 'Error de conexión o de servidor.';
    }
}

function actualizarBotonesCorte(corte) {
    const btnIniciar = document.getElementById('btnIniciarCorte');
    const btnFinalizar = document.getElementById('btnFinalizarCorte');
    const btnPausarReanudar = document.getElementById('btnPausarReanudarCorte');
    if (!corte || !corte.estado) {
        btnIniciar.style.display = '';
        btnFinalizar.style.display = 'none';
        btnPausarReanudar.style.display = 'none';
        document.getElementById('monto_inicial').disabled = false;
    } else if (corte.estado === 'activo') {
        btnIniciar.style.display = 'none';
        btnFinalizar.style.display = '';
        btnPausarReanudar.style.display = '';
        btnPausarReanudar.className = 'btn btn-warning me-2';
        btnPausarReanudar.innerHTML = '<i class="bi bi-pause-circle"></i> Pausar Corte';
        btnPausarReanudar.dataset.estado = 'activo';
        document.getElementById('monto_inicial').disabled = true;
    } else if (corte.estado === 'pausado') {
        btnIniciar.style.display = 'none';
        btnFinalizar.style.display = '';
        btnPausarReanudar.style.display = '';
        // Cambia btn-primary por btn-warning para igualar tamaño y estilo
        btnPausarReanudar.className = 'btn btn-warning me-2';
        btnPausarReanudar.innerHTML = '<i class="bi bi-play-circle"></i> Reanudar Corte';
        btnPausarReanudar.dataset.estado = 'pausado';
        document.getElementById('monto_inicial').disabled = true;
    } else {
        btnIniciar.style.display = '';
        btnFinalizar.style.display = 'none';
        btnPausarReanudar.style.display = 'none';
        document.getElementById('monto_inicial').disabled = false;
    }
}
