<?php
require_once '../models/db.php';
require_once '../models/ventasModel.php';

session_start();

$cliente = $_POST['cliente'] ?? '';
$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';

$ventasModel = new VentasModel();
$ventas = $ventasModel->obtenerVentasConDetalles();

// Filtrar ventas según los parámetros recibidos
$ventasFiltradas = array_filter($ventas, function($v) use ($cliente, $fechaInicio, $fechaFin) {
    $cumpleCliente = $cliente === '' || stripos($v['cliente'], $cliente) !== false;
    $fechaVenta = $v['fecha'] ? date('Y-m-d', strtotime($v['fecha'])) : '';
    $cumpleFechaInicio = $fechaInicio === '' || $fechaVenta >= $fechaInicio;
    $cumpleFechaFin = $fechaFin === '' || $fechaVenta <= $fechaFin;
    return $cumpleCliente && $cumpleFechaInicio && $cumpleFechaFin;
});

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_ventas.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>#</th>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Total ($)</th>
        <th>Pago Cliente</th>
        <th>Cambio</th>
        <th>Correo</th>
        <th>Usuario</th>
        <th>Productos</th>
      </tr>";

$i = 1;
foreach ($ventasFiltradas as $venta) {
    $productosStr = '';
    foreach ($venta['detalles'] as $d) {
        $productosStr .= "{$d['nombre']} (x{$d['cantidad']}), ";
    }
    $productosStr = rtrim($productosStr, ', ');
    echo "<tr>
            <td>{$i}</td>
            <td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fecha']))) . "</td>
            <td>" . htmlspecialchars($venta['cliente']) . "</td>
            <td>" . number_format($venta['monto_total'], 2) . "</td>
            <td>" . number_format($venta['monto_cliente'], 2) . "</td>
            <td>" . number_format($venta['monto_devuelto'], 2) . "</td>
            <td>" . htmlspecialchars($venta['correo_cliente']) . "</td>
            <td>" . htmlspecialchars($venta['usuario'] ?? '') . "</td>
            <td>{$productosStr}</td>
          </tr>";
    $i++;
}
echo "</table>";
exit();
?>
