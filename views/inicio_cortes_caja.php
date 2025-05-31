<?php
session_start();

//con esto se verifica la sesion, en caso de no existir los manda al login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Caja - Oro Verde</title>
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
    <!-- PARA USO DE Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>

    <link rel="stylesheet" href="../css/inicio_corte_caja.css">
    <script defer src="../js/inicio_corte_caja.js"></script>
</head>

<body>
    <!-- NAVBAR PARA LA NAVEGACION -->
    <?php include 'includes/navbar.php' ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main>
        <div class="cash-register-card text-center">
            <div class="card-header-custom">
                <h2 class="d-flex align-items-center">
                    <i class="bi bi-cash-coin me-2"></i> Gestion de Caja
                </h2>
                <span
                    class="badge <?php echo (isset($_SESSION['corteCaja']) && $_SESSION['corteCaja'] === 'activo') ? 'status-open' : 'status-closed' ?>"
                    id="estadoDeCaja">
                    <?php echo (isset($_SESSION['corteCaja']) && $_SESSION['corteCaja'] === 'activo') ? 'Caja Abierta' : 'Caja Cerrada'; ?>
                </span>
            </div>

            <div class="card-body" id="contenidoEstadoCaja">
                <?php if(!isset($_SESSION['corteCaja'])) : ?>
                    <div id="openCashRegisterSection" class="mb-4">
                        <h3 class="section-title"><i class="bi bi-box-arrow-in-right"></i> Apertura de Caja</h3>
                        <p class="card-text mb-4">Ingresa el monto inicial con el que abriras la caja hoy.</p>

                        <div class="mb-3">
                            <label for="initialAmount" class="form-label visually-hidden">Monto Inicial</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control form-control-lg text-center" id="initialAmount"
                                    placeholder="0.00" min="0" step="0.01" autocomplete="off">
                            </div>
                            <div class="invalid-feedback text-start" id="montoError">
                                Por favor, ingresa un monto valido.
                            </div>
                        </div>

                        <button id="btnAbrirCaja" class="btn btn-custom btn-lg w-100 mt-4">
                            <span>Abrir Caja</span>
                        </button>
                    </div>
                <?php else : ?>
                    <div class="mb-4">
                        <h3 class="section-title"><i class="bi bi-receipt"></i> Operaciones Diarias</h3>
                        <p class="text-muted mb-3">Ventas realizadas desde el inicio de caja del dia</p>
                        <div class="alert alert-info py-2" role="alert">
                            Total de Ventas del Dia: 
                            <strong id="dailySalesDisplay">
                                $<?php echo (isset($_SESSION['totalVentasCaja'])) ? $_SESSION['totalVentasCaja'] : '0.00' ?>
                            </strong>
                        </div>
                        <h3 class="section-title"><i class="bi bi-box-arrow-in-left"></i> Cierre de Caja</h3>
                        <p class="card-text mb-4">Finaliza el dia cerrando la caja. Se generara un reporte.</p>
                        <button id="cerrarCaja" class="btn btn-custom btn-custom-red btn-lg w-100">
                            <span>Cerrar Caja</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- <div id="closeCashRegisterSection" class="mb-4">

                </div> -->

                <!-- <div id="reportSection" class="summary-box">
                    <h3 class="section-title text-start"><i class="bi bi-journal-text"></i> Reporte de Cierre</h3>
                    <div class="summary-item">
                        <span>Monto Inicial:</span> <strong id="reportInitialAmount">$0.00</strong>
                    </div>
                    <div class="summary-item">
                        <span>Total Ventas del Dia:</span> <strong id="reportDailySales">$0.00</strong>
                    </div>
                    <div class="total-summary text-end">
                        <span>Total Final Esperado:</span> <span id="reportExpectedTotal">$0.00</span>
                    </div>
                    <p class="text-muted text-start mt-3 placeholder-text">
                        * En un sistema real, aqui compararias el total esperado con el conteo fisico y mostrarias la
                        diferencia.
                    </p>
                    <button id="resetAppBtn" class="btn btn-custom btn-custom-secondary mt-3">
                        <span>Reiniciar Aplicacion</span>
                    </button>
                </div> -->

            </div>
        </div>
    </main>

</body>

</html>