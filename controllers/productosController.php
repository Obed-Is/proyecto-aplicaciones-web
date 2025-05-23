<?php
require_once '../models/db.php';
require_once '../models/productosModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $imagen = $_FILES['imagen'];

    $productosModel = new ProductosModel();
    if ($productosModel->agregarProducto($nombre, $descripcion, $precio, $stock, $imagen)) {
        echo "Producto agregado con éxito.";
    } else {
        echo "Error al agregar el producto.";
    }
}
?>
