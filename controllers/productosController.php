<?php
require_once '../models/db.php';
require_once '../models/productosModel.php';

$productosModel = new ProductosModel();

// --- Lógica de búsqueda: debe ir antes del manejo general de POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Soporta tanto application/json como x-www-form-urlencoded
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $filtro = trim($input['filtroBusqueda'] ?? '');
    } else {
        $filtro = trim($_POST['filtroBusqueda'] ?? '');
    }
    if (!empty($filtro)) {
        $nombre_producto = $filtro . "%"; // <-- Agrega los comodines aquí
        $peticion = $productosModel->buscarProducto($nombre_producto);
        echo json_encode($peticion);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'edit' && isset($_POST['id'])) {
            // Edición de producto
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

            if (empty($idCategoria) || $idCategoria <= 0) {
                echo "<script>alert('Error: Debe seleccionar una categoría válida.'); window.location='../views/productos.php';</script>";
                exit();
            }

            if ($productosModel->editarProducto($id, $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen)) {
                echo "<script>alert('Producto editado con éxito.'); window.location='../views/productos.php';</script>";
            } else {
                echo "<script>alert('Error al editar el producto.'); window.location='../views/productos.php';</script>";
            }
            exit();
        } else {
            // Agregar producto
            $codigo = $_POST['codigo'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $precio = floatval($_POST['precio']);
            $stock = intval($_POST['stock']);
            $estado = intval($_POST['estado']);
            $idCategoria = intval($_POST['idCategoria']);
            $stock_minimo = intval($_POST['stock_minimo']);
            $imagen = $_FILES['imagen'];

            if (empty($idCategoria) || $idCategoria <= 0) {
                echo "<script>alert('Error: Debe seleccionar una categoría válida.'); window.location='../views/productos.php';</script>";
                exit();
            }

            if ($productosModel->agregarProducto($codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen)) {
                echo "<script>alert('Producto agregado con éxito.'); window.location='../views/productos.php';</script>";
            } else {
                echo "<script>alert('Error al agregar el producto.'); window.location='../views/productos.php';</script>";
            }
            exit();
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location='../views/productos.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Soporte para obtener solo la imagen de un producto (AJAX)
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
    // Soporte para eliminar producto
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $productosModel->eliminarProducto($_GET['id']);
        header('Location: ../views/productos.php');
        exit();
    }
    $productos = $productosModel->obtenerProductos();
    header('Content-Type: application/json');
    echo json_encode($productos);
    exit();
}

?>
