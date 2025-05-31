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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Oro Verde</title>
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
    <!-- PARA USO DE Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../css/proveedores.css" />
    <script defer src="../js/proveedores.js"></script>
</head>

<body>
    <?php include 'includes/navbar.php' ?>

    <div class="container py-5 mt-5">
        <h2 class="text-center dashboard-title mb-4 px-2">Gestión de Proveedores</h2>
        <div class="card p-4 shadow-sm mb-4">
            <form id="formAgregarProveedor" class="row g-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" required class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="correo" class="form-label">Correo:</label>
                    <input type="email" id="correo" name="correo" required class="form-control">
                </div>
                <div class="col-md-12">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required class="form-control">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Agregar Proveedor
                    </button>
                </div>
            </form>
        </div>
        <div class="row mb-3 align-items-center">
            <div class="d-flex col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="buscarProveedor" placeholder="Buscar proveedores...">
                </div>
                <button id="btnBuscarProveedor" class="btn btn-light ms-2">Buscar</button>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-truncate">ID</th>
                            <th class="text-truncate">Nombre</th>
                            <th class="text-truncate">Teléfono</th>
                            <th class="text-truncate">Correo</th>
                            <th class="text-truncate">Dirección</th>
                            <th class="text-truncate">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProveedores">
                        <!-- Las filas se llenarán dinámicamente -->
                    </tbody>
                </table>
                <div id="mensajeSinProveedores" class="w-50 mx-auto text-center bg-light p-2 rounded" style="display: none;">
                    <!-- Mensaje de no resultados -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición -->
    <div id="modalEditarProveedor" class="modal" tabindex="-1" style="display:none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Proveedor</h5>
                    <button type="button" class="btn-close" id="cerrarModalProveedor"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarProveedor" class="row g-3">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="col-md-6">
                            <label for="edit-nombre" class="form-label">Nombre:</label>
                            <input type="text" id="edit-nombre" name="nombre" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-telefono" class="form-label">Teléfono:</label>
                            <input type="text" id="edit-telefono" name="telefono" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-correo" class="form-label">Correo:</label>
                            <input type="email" id="edit-correo" name="correo" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-direccion" class="form-label">Dirección:</label>
                            <input type="text" id="edit-direccion" name="direccion" required class="form-control">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>