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
    <script src="../js/productos.js" defer></script>
</head>
<body>
    <?php include 'includes/navbar.php' ?>

    <div class="container py-5 mt-5">
        <h2 class="text-center dashboard-title mb-4 px-2">Gestión de Productos</h2>
        
        <?php
        require_once '../models/db.php';
        require_once '../models/productosModel.php';
        require_once '../models/categoriasModel.php';
        $productosModel = new ProductosModel();
        $categoriasModel = new categoriasModel();
        $productos = $productosModel->obtenerProductos();
        $categorias = $categoriasModel->obtenerCategorias();
        // Crear array asociativo para mapear id_categoria => nombre_categoria
        $categoriasPorId = [];
        foreach ($categorias as $cat) {
            $categoriasPorId[$cat['id_categoria']] = $cat['nombre_categoria'];
        }
        ?>
        <div class="card p-4 shadow-sm mb-4">
            <form action="../controllers/productosController.php" method="POST" enctype="multipart/form-data" class="row g-3">
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
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id_categoria']) ?>">
                                <?= htmlspecialchars($cat['nombre_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
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
        <!-- Buscador de productos -->
        <div class="row mb-3 align-items-center">
            <div class="d-flex col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar productos...">
                </div>
                <button id="btnBuscarProducto" class="btn btn-light ms-2">Buscar</button>
            </div>
        </div>
        <!-- Fin buscador -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Categoría</th>
                            <th>Stock Mínimo</th>
                            <th>Imagen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductos">
                        <!-- Las filas se llenarán dinámicamente si se usa JS -->
                        <?php
                        foreach ($productos as $producto) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($producto['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($producto['codigo']) . "</td>";
                            echo "<td>" . htmlspecialchars($producto['nombre']) . "</td>";
                            echo "<td>" . htmlspecialchars($producto['descripcion']) . "</td>";
                            echo "<td>" . htmlspecialchars($producto['precio']) . "</td>";
                            echo "<td>" . htmlspecialchars($producto['stock']) . "</td>";
                            echo "<td>" . (isset($producto['estado']) ? ($producto['estado'] ? 'Activo' : 'Inactivo') : '') . "</td>";
                            // Mostrar el nombre de la categoría en vez del ID
                            $nombreCategoria = isset($categoriasPorId[$producto['idCategoria']]) ? $categoriasPorId[$producto['idCategoria']] : '';
                            echo "<td>" . htmlspecialchars($nombreCategoria) . "</td>";
                            echo "<td>" . htmlspecialchars($producto['stock_minimo']) . "</td>";
                            echo '<td class="td-imagen" data-id="' . htmlspecialchars($producto['id']) . '">';
                            echo '<img src="../img/imgFaltante.png" alt="Cargando..." width="60" height="60" class="img-sustituta rounded" />';
                            echo '</td>';
                            echo "<td>";
                            echo '<a href="#" class="btn btn-sm btn-primary editar-btn"
                                data-id="' . htmlspecialchars($producto['id']) . '"
                                data-codigo="' . htmlspecialchars($producto['codigo']) . '"
                                data-nombre="' . htmlspecialchars($producto['nombre']) . '"
                                data-descripcion="' . htmlspecialchars($producto['descripcion']) . '"
                                data-precio="' . htmlspecialchars($producto['precio']) . '"
                                data-stock="' . htmlspecialchars($producto['stock']) . '"
                                data-estado="' . (isset($producto['estado']) ? $producto['estado'] : '') . '"
                                data-idcategoria="' . htmlspecialchars($producto['idCategoria']) . '"
                                data-stock_minimo="' . htmlspecialchars($producto['stock_minimo']) . '"
                            ><i class="bi bi-pencil-square"></i></a> ';
                            echo '<a href="../controllers/productosController.php?action=delete&id=' . $producto['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Seguro que deseas eliminar este producto?\')"><i class="bi bi-trash"></i></a>';
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
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
                    <form id="formEditarProducto" enctype="multipart/form-data" method="POST" action="../controllers/productosController.php" class="row g-3">
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
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['id_categoria']) ?>">
                                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
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