<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte de Ventas - Oro verde</title>
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
    <!-- PARA USO DE Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../css/reportes.css">
    <script src="../js/reportes.js" defer></script>
</head>

<body>
    <?php include 'includes/navbar.php' ?>

    <div class="container py-5 mt-5">
        <h2 class="text-center dashboard-title mb-4 px-2">Reporte de Ventas</h2>
        <div class="card p-4 shadow-sm mb-4">
            <div class="filtros" role="search" aria-label="Filtros de búsqueda y exportacion">
                <input type="text" id="inputCliente" placeholder="Buscar cliente..." aria-label="Buscar cliente"
                    autocomplete="off" class="form-control mb-2" />
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="date" id="fechaInicio" aria-label="Fecha inicio" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <input type="date" id="fechaFin" aria-label="Fecha fin" class="form-control" />
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button id="btnFiltrar" title="Filtrar" class="btn btn-primary flex-fill"><i class="bi bi-filter"></i> Filtrar</button>
                        <button id="btnLimpiar" title="Limpiar filtros" type="button" class="btn btn-secondary flex-fill"><i class="bi bi-x-circle"></i> Limpiar</button>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button id="btnExportarPDF" title="Exportar a PDF" type="button" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                    <button id="btnExportarExcel" title="Exportar a Excel" type="button" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" aria-label="Tabla de ventas">
                    <thead class="table-light">
                        <tr>
                            <th class="text-truncate">#</th>
                            <th class="text-truncate">Fecha</th>
                            <th class="text-truncate">Cliente</th>
                            <th class="text-truncate">Total ($)</th>
                            <th class="text-truncate">Pago Cliente</th>
                            <th class="text-truncate">Cambio</th>
                            <th class="text-truncate">Correo</th>
                            <th class="text-truncate">Usuario</th>
                            <th class="text-truncate text-center" scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaVentas">
                        <!-- aqui van los registros -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Venta -->
    <div class="modal fade" id="modalEditarVenta" tabindex="-1" aria-labelledby="tituloModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditarVenta" class="modal-content" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">Editar Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="indiceEditar" />

                    <div class="mb-3">
                        <label for="fechaEditar" class="form-label">Fecha</label>
                        <input type="date" id="fechaEditar" class="form-control" required />
                        <div class="invalid-feedback">Por favor, selecciona una fecha valida.</div>
                    </div>

                    <div class="mb-3">
                        <label for="clienteEditar" class="form-label">Cliente</label>
                        <input type="text" id="clienteEditar" class="form-control" placeholder="Nombre del cliente"
                            required />
                        <div class="invalid-feedback">El nombre del cliente es obligatorio.</div>
                    </div>

                    <div class="mb-3">
                        <label for="totalEditar" class="form-label">Total ($)</label>
                        <input type="number" id="totalEditar" class="form-control" min="0" step="0.01" required />
                        <div class="invalid-feedback">Ingresa un total valido mayor o igual a cero.</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>