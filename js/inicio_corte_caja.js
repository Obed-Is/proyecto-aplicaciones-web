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
const montoInicialInput = document.getElementById('initialAmount');
const btnAbrirCaja = document.getElementById('btnAbrirCaja');
const cerrarCaja = document.getElementById('cerrarCaja');
const simulatedSaleAmountInput = document.getElementById('simulatedSaleAmount');
const montoErrorDiv = document.getElementById('montoError');
const estadoDeCaja = document.getElementById('estadoDeCaja');
const dailySalesDisplay = document.getElementById('dailySalesDisplay');
const reportInitialAmount = document.getElementById('reportInitialAmount');
const reportDailySales = document.getElementById('reportDailySales');
const reportExpectedTotal = document.getElementById('reportExpectedTotal');
const resetAppBtn = document.getElementById('resetAppBtn');

// Funcion para abrir la caja
const openCashRegister = () => {
    const montoInput = document.getElementById('initialAmount');
    const montoErrorDiv = document.getElementById('montoError');
    const monto = parseFloat(montoInput.value);

    if (isNaN(monto) || monto < 0) {
        montoInput.classList.add('is-invalid');
        montoErrorDiv.textContent = 'Por favor, ingresa un monto numerico valido y positivo.';
        montoErrorDiv.style.display = 'block';
        return;
    } else {
        montoInput.classList.remove('is-invalid');
        montoErrorDiv.style.display = 'none';
    }

    Swal.fire({
        title: 'Confirmar Apertura de Caja',
        html: `Estas seguro de abrir la caja con <strong>$${monto.toFixed(2)}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Abrir Caja',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            abrirCajaPHP(monto);
        }
    });
};



if (btnAbrirCaja) {
    btnAbrirCaja.addEventListener('click', openCashRegister);
}
if (cerrarCaja) {
    cerrarCaja.addEventListener('click', () => {
        cerrarCajaPHP();
    });
}
if (montoInicialInput) {
    montoInicialInput.value = '';
    montoInicialInput.addEventListener('input', () => {
        if (montoInicialInput.classList.contains('is-invalid')) {
            montoInicialInput.classList.remove('is-invalid');
            montoErrorDiv.style.display = 'none';
        }
    });
}

// funcion para mandar a abrir la caja a la db
function abrirCajaPHP(montoInicial) {
    fetch('../controllers/inicioCajaController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ montoInicial })
    }).then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarCajaAbierta();
                alertaEsquinaSuperior('success', 'Corte de caja', data.message);
                console.log('data del fetcj de iniciar corte de caja: ', data);
                return true;
            } else {
                estadoDeCaja.classList.remove('status-open');
                estadoDeCaja.classList.add('status-closed');
                estadoDeCaja.textContent = 'Caja Cerrada';
                alertaEsquinaSuperior('error', 'Corte de caja', data.message);
            }
            console.log('data del fetcj de iniciar corte de caja: ', data);
        })
        .catch(err => {
            alertaEsquinaSuperior('error', 'Corte de caja', 'Ocurrio un error al intentar iniciar el corte de caja');
            console.log('error del fetch de inicio de corte de caja:', err);
        })
    return false;
}

// funcion para cerrar la caja
function cerrarCajaPHP() {
    Swal.fire({
        title: '¿Que tipo de corte deseas realizar?',
        text: 'Puedes generar un corte parcial o cerrar la caja completamente.',
        icon: 'question',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Cerrar Caja',
        denyButtonText: 'Corte Parcial',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        denyButtonColor: '#f0ad4e',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            corteCaja('cerrar'); 
        } else if (result.isDenied) {
            corteCaja('parcial'); 
        }
    });


    function corteCaja(tipoCorte) {
        fetch('../controllers/inicioCajaController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ tipoCorte })
        }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    if(tipoCorte === 'cerrar'){
                        actualizarCajaCerrada();
                    }else{
                       estadoDeCaja.textContent = 'Caja cerrada temporalmente';
                    }
                    alertaEsquinaSuperior('success', 'Corte de caja', data.message);
                    console.log('data del fetch de cerrar corte de caja: ', data);
                    return true;
                } else {
                    alertaEsquinaSuperior('error', 'Corte de caja', data.message);
                }
                console.log('data del fetcj de cerrar  corte de caja: ', data);
            })
            .catch(err => {
                alertaEsquinaSuperior('error', 'Corte de caja', 'Ocurrio un error al intentar iniciar el corte de caja');
                console.log('error del fetch de cerrar de corte de caja:', err);
            })
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

function actualizarCajaAbierta() {
    const containerCard = document.getElementById('contenidoEstadoCaja');
    estadoDeCaja.classList.remove('status-closed');
    estadoDeCaja.classList.add('status-open');
    estadoDeCaja.textContent = 'Caja Abierta';
    containerCard.innerHTML = `
        <div class="mb-4">
            <h3 class="section-title"><i class="bi bi-receipt"></i> Operaciones Diarias</h3>
            <p class="text-muted mb-3">Ventas realizadas desde el inicio de caja del dia</p>
            <div class="alert alert-info py-2" role="alert">
                Total de Ventas del Dia: 
                <strong id="dailySalesDisplay">
                    $0.00
                </strong>
            </div>

            <h3 class="section-title"><i class="bi bi-box-arrow-in-left"></i> Cierre de Caja</h3>
            <p class="card-text mb-4">Finaliza el dia cerrando la caja. Se generara un reporte.</p>
            <button id="cerrarCaja" class="btn btn-custom btn-custom-red btn-lg w-100">
                <span>Cerrar Caja</span>
            </button>
        </div>
    `;
    const nuevoBotonCerrarCaja = document.getElementById('cerrarCaja');
    if (nuevoBotonCerrarCaja) {
        nuevoBotonCerrarCaja.addEventListener('click', () => {
            cerrarCajaPHP();
        });
    }
}

function actualizarCajaCerrada() {
    const containerCard = document.getElementById('contenidoEstadoCaja');
    estadoDeCaja.classList.remove('status-open');
    estadoDeCaja.classList.add('status-closed');
    estadoDeCaja.textContent = 'Caja Cerrada';

    containerCard.innerHTML = `
        <div id="openCashRegisterSection" class="mb-4">
            <h3 class="section-title"><i class="bi bi-box-arrow-in-right"></i> Apertura de Caja</h3>
            <p class="card-text mb-4">Ingresa el monto inicial con el que abriras la caja hoy.</p>
            <div class="mb-3">
                <label for="initialAmount" class="form-label visually-hidden">Monto Inicial</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control form-control-lg text-center" id="initialAmount"
                        placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="invalid-feedback text-start" id="montoError">
                    Por favor, ingresa un monto valido.
                </div>
            </div>
            <button id="btnAbrirCaja" class="btn btn-custom btn-lg w-100 mt-4">
                <span>Abrir Caja</span>
            </button>
        </div>
    `;

    configurarEventosCaja();
}

function configurarEventosCaja() {
    const btnAbrirCaja = document.getElementById('btnAbrirCaja');
    const montoInput = document.getElementById('initialAmount');
    const montoErrorDiv = document.getElementById('montoError');

    if (btnAbrirCaja) {
        btnAbrirCaja.addEventListener('click', openCashRegister);
    }

    if (montoInput) {
        montoInput.addEventListener('input', () => {
            if (montoInput.classList.contains('is-invalid')) {
                montoInput.classList.remove('is-invalid');
                montoErrorDiv.style.display = 'none';
            }
        });
    }
}
