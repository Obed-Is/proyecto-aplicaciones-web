<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['idUsuario'])) {
    header('Location: login.php');
    exit();
}
// Corrige la comprobación de admin (debe ser 'administrador' para coincidir con el sistema)
$esAdmin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cortes de Caja - Oro Verde</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>

    <link rel="stylesheet" href="../css/ventas.css" />
    <script defer src="../js/cortes_caja.js"></script>
</head>

<body>
    <?php include 'includes/navbar.php' ?>
    <div class="container py-5 mt-5">
        <h2 class="text-center dashboard-title mb-4 px-2">
            <?php echo $esAdmin ? 'Cortes de Caja (Administrador)' : 'Gestión de Cortes de Caja'; ?>
        </h2>
        <?php if (!$esAdmin): ?>
            <div class="card p-4 shadow-sm mb-4">
                <form id="formCorteCaja" class="row g-3">
                    <div class="col-md-4">
                        <label for="monto_inicial" class="form-label">Monto Inicial:</label>
                        <input type="number" step="0.01" id="monto_inicial" name="monto_inicial" required
                            class="form-control">
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <button type="button" id="btnIniciarCorte" class="btn btn-success me-2">
                            <i class="bi bi-play-circle"></i> Iniciar Corte
                        </button>
                        <!-- Botón unificado para pausar/reanudar, se controla por JS -->
                        <button type="button" id="btnPausarReanudarCorte" style="display:none;"
                            class="btn btn-warning me-2">
                            <i class="bi bi-pause-circle"></i> Reanudar Corte
                        </button>
                        <button type="button" id="btnPausarCorte" class="btn btn-warning me-2" style="display:none;">
                            <i class="bi bi-pause-circle"></i> Pausar Corte
                        </button>
                        <button type="button" id="btnFinalizarCorte" class="btn btn-danger" style="display:none;">
                            <i class="bi bi-stop-circle"></i> Finalizar Corte
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="mb-4 d-flex justify-content-end gap-2">
                <a href="../controllers/cortes_caja_excel.php" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                </a>
                <a href="../controllers/cortes_caja_pdf.php" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                </a>
            </div>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-truncate">ID</th>
                            <th class="text-truncate">Fecha</th>
                            <th class="text-truncate">Hora Inicio</th>
                            <th class="text-truncate">Hora Fin</th>
                            <th class="text-truncate">Monto Inicial</th>
                            <th class="text-truncate">Monto Final</th>
                            <th class="text-truncate">Ventas</th>
                            <th class="text-truncate">Total Ganancia</th>
                            <th class="text-truncate">Usuario</th>
                            <th class="text-truncate">Estado</th>
                            <!-- <th>Acciones</th> -->
                        </tr>
                    </thead>
                    <tbody id="tablaCortesCaja">
                        <!-- Las filas se llenarán dinámicamente por JS -->
                    </tbody>
                </table>
                <div id="mensajeSinCortes" class="w-50 mx-auto text-center bg-light p-2 rounded" style="display: none;">
                    <!-- Mensaje de no resultados -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>