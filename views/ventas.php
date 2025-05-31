<?php
session_start();

//con esto se verifica la sesion, en caso de no existir los manda al login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar corte de caja activo antes de permitir acceso a ventas
require_once '../models/db.php';
require_once '../models/cortesCajaModel.php';
$cortesModel = new CortesCajaModel();
$idUsuario = $_SESSION['idUsuario'] ?? null;
$rolUsuario = $_SESSION['rol'] ?? '';
$corteActivo = null;
if ($idUsuario) {
    $corteActivo = $cortesModel->obtenerCorteActivo($idUsuario);
}
// Solo restringir si NO es administrador
if ($rolUsuario !== 'administrador') {
    if (!isset($_SESSION['corteCaja'])) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Corte de caja requerido</title>
            <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <style>
                body {
                    background: linear-gradient(135deg, #f1f1f1, #cfe8ff);
                }
            </style>
        </head>
        <body class="bg-light">
            <?php include "includes/navbar.php" ?>
            <div class="container py-5 mt-5 d-flex justify-content-center">
                <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%;">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-1"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-3 text-warning">Corte de caja requerido</h4>
                        <p class="card-text text-muted">
                            Para continuar con las ventas, debes iniciar un corte de caja activo.
                        </p>
                        <hr class="my-4">
                        <a href="inicio_cortes_caja.php" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ir a Cortes de Caja
                        </a>
                    </div>
                </div>
            </div>
            <script>
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
            </script>
        </body>
        </html>
        <?php
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Ventas | MyStore Pro</title>
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/ventas.css" />
    <script defer src="../js/ventas.js"></script>
</head>

<body>

    <?php include 'includes/navbar.php' ?>

    <div class="container-fluid mt-4 py-3 px-4">
        <div class="row g-4">
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex align-items-center py-2 rounded-top-2">
                        <i class="bi bi-person-circle me-2"></i>
                        <h6 class="mb-0 fw-bold">Información del Cliente</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label for="clienteNombre" class="form-label mb-1">Nombre del Cliente</label>
                            <input type="text" class="form-control" id="clienteNombre" placeholder="Ej: Vin Diesel">
                        </div>
                        <div class="mb-3">
                            <label for="clienteCorreo" class="form-label mb-1">Correo Electrónico (opcional)</label>
                            <input type="email" class="form-control" id="clienteCorreo"
                                placeholder="Ej: rapidofurioso@email.com">
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100 mt-2" id="btnGuardarCliente">
                            <i class="bi bi-check-circle me-2"></i>Asignar Cliente
                        </button>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white d-flex align-items-center py-2 rounded-top-2">
                        <i class="bi bi-box-seam me-2"></i>
                        <h6 class="mb-0 fw-bold">Buscar y Agregar Productos</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="buscarProducto"
                                placeholder="Buscar por nombre o código...">
                            <button class="btn btn-primary" type="button" id="btnBuscarProducto">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        <div id="productosEncontrados"
                            class="list-group list-group-flush border rounded-3 overflow-auto"
                            style="max-height: 250px;">
                            <p class="text-center text-muted p-3 mb-0" id="noProductosFound">Escribe para empezar a
                                buscar productos.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white d-flex align-items-center py-2 rounded-top-2">
                        <i class="bi bi-receipt me-2"></i>
                        <h6 class="mb-0 fw-bold">Detalle de la Venta</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0" id="tablaVenta">
                                <thead>
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col" class="text-end">P. Unitario</th>
                                        <th scope="col" class="text-center">Cantidad</th>
                                        <th scope="col" class="text-end">Subtotal</th>
                                        <th scope="col" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaVenta">
                                    <tr id="emptyCartRow">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-cart-x fs-5 d-block mb-2"></i>
                                            Aún no hay productos en la venta.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end align-items-center mt-3 pt-3 border-top">
                            <div class="col-auto">
                                <h5 class="mb-0 text-primary">Total:</h5>
                            </div>
                            <div class="col-auto">
                                <h5 class="mb-0 text-primary fw-bold fs-4">$<span id="totalVenta">0.00</span></h5>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="pagoCliente" class="form-label mb-1">Pago del Cliente</label>
                                <input type="number" class="form-control" id="pagoCliente" placeholder="Monto recibido"
                                    min="0" step="0.01" disabled>
                            </div>
                            <div class="col-md-4 d-grid">
                                <button class="btn btn-outline-danger btn-sm" id="msjMontoInvalido">
                                    Monto invalido
                                </button>
                            </div>
                            <div class="col-md-3 text-end">
                                <label class="form-label mb-1 d-block">Cambio:</label>
                                <h5 class="mb-0 text-success fw-bold fs-4">$<span id="cambioDevuelto">0.00</span></h5>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3 pt-3 border-top">
                            <button class="btn btn-success btn-sm" id="btnGenerarTicket" disabled>
                                <i class="bi bi-check-circle-fill me-2"></i>Finalizar Venta y Generar Ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
</body>

</html>