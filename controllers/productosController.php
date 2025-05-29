<?php
require_once '../models/db.php';
require_once '../models/productosModel.php';

$productosModel = new ProductosModel();

// POST: búsqueda, edición o agregado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    // Si es búsqueda (JSON con filtro)
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $filtro = trim($input['filtroBusqueda'] ?? '');
        if (!empty($filtro)) {
            $filtroLike = $filtro . "%";
            $peticion = $productosModel->buscarProducto($filtroLike);
            header('Content-Type: application/json');
            echo json_encode($peticion);
            exit();
        }
    }

    // Si no es búsqueda, es edición o agregado (formulario)
    $action = $_POST['action'] ?? '';
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"], 'application/json') !== false);

    header('Content-Type: application/json');
    try {
        if ($action === 'edit' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $codigo = $_POST['codigo'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $precio = floatval($_POST['precio']);
            $stock = intval($_POST['stock']);
            $estado = intval($_POST['estado']);
            $idCategoria = intval($_POST['idCategoria']);
            $stock_minimo = intval($_POST['stock_minimo']);
            $imagen = (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) ? $_FILES['imagen'] : null;
            $idProveedor = intval($_POST['idProveedor']);

            // Validaciones
            if (!$codigo || strlen($codigo) > 4) {
                echo json_encode(['success' => false, 'message' => 'Código requerido y máximo 4 caracteres.']);
                exit();
            } else if ($productosModel->codigoExiste($codigo, $id)) {
                echo json_encode(['success' => false, 'message' => 'Ya hay un producto con ese código.']);
                exit();
            }
            if (!$nombre || strlen($nombre) < 3 || strlen($nombre) > 40) {
                echo json_encode(['success' => false, 'message' => 'Nombre requerido (3-40 caracteres).']);
                exit();
            } else if (!preg_match('/^[\p{L}\p{N}\s\-\+\.\,\(\)\'"]+$/u', $nombre)) {
                echo json_encode(['success' => false, 'message' => 'Nombre de producto inválido. Use solo letras, números y caracteres comunes.']);
                exit();
            }
            if (!$descripcion || strlen($descripcion) < 5 || strlen($descripcion) > 100) {
                echo json_encode(['success' => false, 'message' => 'Descripción requerida (5-100 caracteres).']);
                exit();
            }
            if ($precio <= 0) {
                echo json_encode(['success' => false, 'message' => 'Precio inválido.']);
                exit();
            }
            if ($stock < 0) {
                echo json_encode(['success' => false, 'message' => 'Stock inválido.']);
                exit();
            }
            if ($stock_minimo < 0) {
                echo json_encode(['success' => false, 'message' => 'Stock mínimo inválido.']);
                exit();
            }
            if ($estado != 0 && $estado != 1) {
                echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
                exit();
            }
            if (empty($idCategoria) || $idCategoria <= 0) {
                echo json_encode(['success' => false, 'message' => 'Debe seleccionar una categoría válida.']);
                exit();
            }
            if (empty($idProveedor) || $idProveedor <= 0) {
                echo json_encode(['success' => false, 'message' => 'Debe seleccionar un proveedor válido.']);
                exit();
            }

            $ok = $productosModel->editarProducto($id, $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen, $idProveedor);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Producto editado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al editar el producto.']);
            }
            exit();
        } else {
            $codigo = $_POST['codigo'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $precio = floatval($_POST['precio']);
            $stock = intval($_POST['stock']);
            $estado = intval($_POST['estado']);
            $idCategoria = intval($_POST['idCategoria']);
            $stock_minimo = intval($_POST['stock_minimo']);
            $imagen = $_FILES['imagen'];
            $idProveedor = intval($_POST['idProveedor']);

            // Validaciones
            if (!$codigo || strlen($codigo) > 4) {
                echo json_encode(['success' => false, 'message' => 'Código requerido y máximo 4 caracteres.']);
                exit();
            } else if ($productosModel->codigoExiste($codigo)) {
                echo json_encode(['success' => false, 'message' => 'Ya hay un producto con ese código.']);
                exit();
            }
            if (!$nombre || strlen($nombre) < 3 || strlen($nombre) > 40) {
                echo json_encode(['success' => false, 'message' => 'Nombre requerido (3-40 caracteres).']);
                exit();
            } else if (!preg_match('/^[\p{L}\p{N}\s\-\+\.\,\(\)\'"]+$/u', $nombre)) {
                echo json_encode(['success' => false, 'message' => 'Nombre de producto inválido. Use solo letras, números y caracteres comunes.']);
                exit();
            }
            if (!$descripcion || strlen($descripcion) < 5 || strlen($descripcion) > 100) {
                echo json_encode(['success' => false, 'message' => 'Descripción requerida (5-100 caracteres).']);
                exit();
            }
            if ($precio <= 0) {
                echo json_encode(['success' => false, 'message' => 'Precio inválido.']);
                exit();
            }
            if ($stock < 0) { // Limite razonable para stock
                echo json_encode(['success' => false, 'message' => 'Stock inválido.']);
                exit();
            }
            if ($stock_minimo < 0 || $stock_minimo > $stock) {
                echo json_encode(['success' => false, 'message' => 'Stock mínimo inválido.']);
                exit();
            }
            if ($estado != 0 && $estado != 1) {
                echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
                exit();
            }
            if (empty($idCategoria) || $idCategoria <= 0) {
                echo json_encode(['success' => false, 'message' => 'Debe seleccionar una categoría válida.']);
                exit();
            }
            if (empty($idProveedor) || $idProveedor <= 0) {
                echo json_encode(['success' => false, 'message' => 'Debe seleccionar un proveedor válido.']);
                exit();
            }

            $ok = $productosModel->agregarProducto($codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen, $idProveedor);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Producto agregado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al agregar el producto.']);
            }
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// GET: obtener productos, imagen o eliminar
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['getImage']) && $_GET['getImage'] == 1 && isset($_GET['id'])) {
        $producto = $productosModel->obtenerProductoPorId($_GET['id']);
        if ($producto && !empty($producto['imagen'])) {
            $finfo = finfo_open();
            $mime = finfo_buffer($finfo, $producto['imagen'], FILEINFO_MIME_TYPE);
            finfo_close($finfo);
            $imgData = base64_encode($producto['imagen']);
            echo json_encode([
                'success' => true,
                'imgSrc' => 'data:' . $mime . ';base64,' . $imgData
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit();
    }
    // Eliminar producto
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $ok = $productosModel->eliminarProducto($_GET['id']);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto.']);
        }
        exit();
    }
    $productos = $productosModel->obtenerProductos();
    $productos_normalizados = [];
    foreach ($productos as $p) {
        $productos_normalizados[] = [
            'id' => $p['id'],
            'codigo' => $p['codigo'],
            'nombre' => $p['nombre'],
            'descripcion' => $p['descripcion'],
            'precio' => $p['precio'],
            'stock' => $p['stock'],
            'estado' => $p['estado'],
            'idCategoria' => $p['idCategoria'],
            'nombre_categoria' => $p['nombre_categoria'],
            'idProveedor' => $p['idProveedor'],
            'proveedor_nombre' => $p['proveedor_nombre'],
            'stock_minimo' => $p['stock_minimo']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($productos_normalizados);
    exit();
}
?>