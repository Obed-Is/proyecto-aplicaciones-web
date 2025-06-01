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
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
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
        <?php if ($esAdmin): ?>
            <div class="mb-4 d-flex justify-content-end gap-2">
                <a href="../controllers/cortes_caja_excel.php" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                </a>
                <a href="../controllers/cortes_caja_pdf.php" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                </a>
            </div>
        <?php endif; ?>

        <div class="card p-3 mb-3">
            <div class="row g-2 align-items-end">
                <?php if ($esAdmin): ?>
                <div class="col-md-4">
                    <label for="filtroUsuarioCorte" class="form-label mb-0">Buscar usuario</label>
                    <input type="text" id="filtroUsuarioCorte" class="form-control" placeholder="Nombre de usuario..." />
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label for="fechaInicioCorte" class="form-label mb-0">Fecha inicio</label>
                    <input type="date" id="fechaInicioCorte" class="form-control" />
                </div>
                <div class="col-md-4">
                    <label for="fechaFinCorte" class="form-label mb-0">Fecha fin</label>
                    <input type="date" id="fechaFinCorte" class="form-control" />
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button id="btnFiltrarCortes" class="btn btn-primary flex-fill" type="button"><i class="bi bi-filter"></i> Filtrar</button>
                    <button id="btnLimpiarCortes" class="btn btn-secondary flex-fill" type="button"><i class="bi bi-x-circle"></i> Limpiar</button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-truncate">#</th>
                            <th class="text-truncate">Fecha</th>
                            <th class="text-truncate">Hora Inicio</th>
                            <th class="text-truncate">Hora Fin</th>
                            <th class="text-truncate">Monto Inicial</th>
                            <th class="text-truncate">Monto Final</th>
                            <th class="text-truncate">Ventas</th>
                            <th class="text-truncate">Total Ganancia</th>
                            <th class="text-truncate">Usuario</th>
                            <th class="text-truncate">Estado</th>
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