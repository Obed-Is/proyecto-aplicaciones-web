<?php
require_once '../models/db.php';
require_once '../models/ventasModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = $_POST['cliente'];
    $telefono = $_POST['telefono'];
    $producto = $_POST['producto'];
    $cantidad = $_POST['cantidad'];

    $ventasModel = new VentasModel();
    if ($ventasModel->registrarVenta($cliente, $telefono, $producto, $cantidad)) {
        echo "Venta registrada con éxito.";
    } else {
        echo "Error al registrar la venta.";
    }
}
?>
