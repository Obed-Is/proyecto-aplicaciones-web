<?php
require_once '../models/db.php';
require_once '../models/productosModel.php';
require_once '../models/ventasModel.php';

session_start();

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$productosModel = new ProductosModel();
$ventasModel = new VentasModel();
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Si viene ?all=1, devolver todas las ventas con detalles
    if (isset($_GET['all'])) {
        $ventas = $ventasModel->obtenerVentasConDetalles();
        echo json_encode($ventas);
        exit();
    }

    $productos = $ventasModel->obtenerProductosActivos();
    $productos_normalizados = [];
    foreach ($productos as $p) {
        $productos_normalizados[] = [
            'id' => $p['id'],
            'codigo' => $p['codigo'],
            'nombre' => $p['nombre'],
            'descripcion' => $p['descripcion'],
            'precio' => floatval($p['precio']),
            'stock' => $p['stock'],
            'estado' => $p['estado'],
            'idCategoria' => $p['idCategoria'],
            'nombre_categoria' => $p['nombre_categoria'],
            'idProveedor' => $p['idProveedor'],
            'proveedor_nombre' => $p['proveedor_nombre'],
            'stock_minimo' => $p['stock_minimo']
        ];
    }
    echo json_encode($productos_normalizados);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = $data['cliente'] ?? null;
    $correo = $data['correo'] ?? null;
    $productos = $data['productos'] ?? [];
    $pagoCliente = $data['pagoCliente'] ?? 0;
    $cambioDevuelto = $data['cambioDevuelto'] ?? 0;
    $totalVenta = $data['totalVenta'] ?? 0;
    $idUsuario = $_SESSION['idUsuario'];

    if (!$cliente || !$correo || empty($productos) || $totalVenta <= 0) {
        echo json_encode(["success" => false, 'message' => 'Datos incompletos o invalidos']);
        exit();
    }

    $idVenta = $ventasModel->crearVenta($idUsuario, $cliente, $correo, $pagoCliente, $cambioDevuelto, $totalVenta);

    if (!$idVenta) {
        echo json_encode(["success" => false, 'message' => 'Ocurrio un error al intentar crear la venta']);
        exit();
    }

    $conn = $db->getConnection();
    $conn->begin_transaction();

    try {
        foreach ($productos as $prod) {
            $okDetalle = $ventasModel->agregarProductoVenta(
                $idVenta,
                $prod['id'],
                $prod['cantidad'],
                $prod['precio']
            );

            $okStock = $ventasModel->reducirStockProducto(
                $prod['id'],
                $prod['cantidad']
            );

            if (!$okDetalle || !$okStock) {
                throw new Exception('Error al guardar detalle o actualizar stock');
            }
        }

        $conn->commit();

        $_SESSION['ticket'] = [
            'idVenta' => $idVenta,
            'cliente' => $cliente,
            'correo' => $correo,
            'productos' => $productos,
            'pagoCliente' => $pagoCliente,
            'cambioDevuelto' => $cambioDevuelto,
            'totalVenta' => $totalVenta,
            'usuario' => $_SESSION['usuario'],
            'fechaVenta' => date('Y-m-d H:i:s')
        ];
        $_SESSION['productos'] = $productos;

        echo json_encode([
            'success' => true,
            'idVenta' => $idVenta,
            'message' => 'Venta registrada correctamente',
            'redirect' => '../controllers/pdfTicket.php'
        ]);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

?>