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
    <title>Productos - Oro Verde</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon" />
    <!-- PARA USO DE Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../css/productos.css">
    <script>
        var esAdmin = <?php echo (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador') ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/productos.js" defer></script>
</head>
<body>
    <?php include 'includes/navbar.php' ?>

    <div class="container py-5 mt-5">
        <h2 class="text-center dashboard-title mb-4 px-2">Gestión de Productos</h2>
        <?php if ($_SESSION['rol'] == 'administrador'): ?>
        <div class="card p-4 shadow-sm mb-4">
            <div class="mb-3 d-flex gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap w-100">
                    <span class="fw-semibold fs-5 text-success"><i class="bi bi-download me-1"></i> Exportar Productos</span>
                    <button id="btnExportarProductosExcel" class="btn btn-outline-success d-flex align-items-center">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                    </button>
                    <form id="formImportarProductos" enctype="multipart/form-data" class="d-inline-block ms-3">
                        <label for="inputImportarProductos" class="btn btn-outline-primary d-flex align-items-center mb-0">
                            <i class="bi bi-upload me-1"></i> Importar Excel
                        </label>
                        <input type="file" id="inputImportarProductos" name="archivo" accept=".xlsx,.xls" style="display:none;">
                    </form>
                </div>
            </div>
            <form id="formAgregarProducto" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-2">
                    <label for="codigo" class="form-label">Código:</label>
                    <input type="text" id="codigo" name="codigo" maxlength="4" required class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required class="form-control"></textarea>
                </div>
                <div class="col-md-2">
                    <label for="precio" class="form-label">Precio:</label>
                    <input type="number" id="precio" name="precio" step="0.01" required class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="stock" class="form-label">Stock:</label>
                    <input type="number" id="stock" name="stock" required class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="stock_minimo" class="form-label">Stock Mínimo:</label>
                    <input type="number" id="stock_minimo" name="stock_minimo" required class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado:</label>
                    <select id="estado" name="estado" required class="form-select">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="idCategoria" class="form-label">Categoría:</label>
                    <select id="idCategoria" name="idCategoria" required class="form-select">
                        <option value="">Seleccione una categoría</option>
                        <option value="" disabled>Cargando...</option>
                        <!-- Opciones dinamicas -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="idProveedor" class="form-label">Proveedor:</label>
                    <select id="idProveedor" name="idProveedor" required class="form-select">
                        <option value="">Seleccione un proveedor</option>
                        <option value="" disabled>Cargando...</option>
                        <!-- Opciones dinamicas -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="imagen" class="form-label">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*" required class="form-control">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Agregar Producto
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <!-- Buscador de productos -->
        <div class="row mb-3 align-items-center">
            <div class="d-flex col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar productos...">
                </div>
                <button id="btnBuscarProducto" class="btn btn-light ms-2">Buscar</button>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-truncate">Código</th>
                            <th class="text-truncate">Nombre</th>
                            <th class="text-truncate">Descripción</th>
                            <th class="text-truncate">Precio</th>
                            <th class="text-truncate">Stock</th>
                            <th class="text-truncate">Estado</th>
                            <th class="text-truncate">Categoría</th>
                            <th class="text-truncate">Proveedor</th>
                            <th class="text-truncate">Stock Mínimo</th>
                            <th class="text-truncate">Imagen</th>
                            <?php if ($_SESSION['rol'] == 'administrador'): ?>
                            <th class="text-truncate">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tablaProductos">
                        <!-- se agregan dinamicamente -->
                    </tbody>
                </table>
                <div id="mensajeSinProductos" class="w-50 mx-auto text-center bg-light p-2 rounded" style="display: none;">
                    <!-- Mensaje de no resultados -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición -->
    <div id="modalEditar" class="modal" tabindex="-1" style="display:none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Producto</h5>
                    <button type="button" class="btn-close" id="cerrarModal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarProducto" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" id="edit-id" name="id">
                        <input type="hidden" name="action" value="edit">
                        <div class="col-md-4">
                            <label for="edit-codigo" class="form-label">Código:</label>
                            <input type="text" id="edit-codigo" name="codigo" maxlength="4" required class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label for="edit-nombre" class="form-label">Nombre:</label>
                            <input type="text" id="edit-nombre" name="nombre" required class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="edit-descripcion" class="form-label">Descripción:</label>
                            <textarea id="edit-descripcion" name="descripcion" required class="form-control"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="edit-precio" class="form-label">Precio:</label>
                            <input type="number" id="edit-precio" name="precio" step="0.01" required class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="edit-stock" class="form-label">Stock:</label>
                            <input type="number" id="edit-stock" name="stock" required class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="edit-stock_minimo" class="form-label">Stock Mínimo:</label>
                            <input type="number" id="edit-stock_minimo" name="stock_minimo" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-estado" class="form-label">Estado:</label>
                            <select id="edit-estado" name="estado" required class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-idCategoria" class="form-label">Categoría:</label>
                            <select id="edit-idCategoria" name="idCategoria" required class="form-select">
                                <option value="">Seleccione una categoría</option>
                                <option value="" disabled>Cargando...</option>
                                <!-- Opciones dinamicas -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-idProveedor" class="form-label">Proveedor:</label>
                            <select id="edit-idProveedor" name="idProveedor" required class="form-select">
                                <option value="">Seleccione un proveedor</option>
                                <option value="" disabled>Cargando...</option>
                                <!-- Opciones dinamicas -->
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="edit-imagen" class="form-label">Imagen (opcional):</label>
                            <input type="file" id="edit-imagen" name="imagen" accept="image/*" class="form-control">
                            <div id="imagen-actual" class="mt-2"></div>
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