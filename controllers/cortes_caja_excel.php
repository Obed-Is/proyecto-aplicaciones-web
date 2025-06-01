<?php
require_once '../models/db.php';
require_once '../models/cortesCajaModel.php';

session_start();

$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';

$cortesModel = new CortesCajaModel();
$cortes = $cortesModel->obtenerCortesCaja();

// Filtro por fecha si se reciben parámetros
if ($fechaInicio || $fechaFin) {
    $cortes = array_filter($cortes, function($corte) use ($fechaInicio, $fechaFin) {
        $fecha = $corte['fecha'];
        $ok = true;
        if ($fechaInicio) $ok = $ok && ($fecha >= $fechaInicio);
        if ($fechaFin) $ok = $ok && ($fecha <= $fechaFin);
        return $ok;
    });
    $cortes = array_values($cortes);
}

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_cortes_caja.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>#</th>
        <th>Fecha y Hora Inicio</th>
        <th>Hora Fin</th>
        <th>Monto Inicial</th>
        <th>Monto Final</th>
        <th>Ventas</th>
        <th>Total Ganancia</th>
        <th>Usuario</th>
        <th>Estado</th>
      </tr>";

$i = 1;
foreach ($cortes as $corte) {
    // Combina fecha y hora_inicio en una sola columna
    $fechaHoraInicio = '';
    if (!empty($corte['fecha']) && !empty($corte['hora_inicio'])) {
        $fechaHoraInicio = date('d/m/Y', strtotime($corte['fecha'])) . ' ' . $corte['hora_inicio'];
    } elseif (!empty($corte['fecha'])) {
        $fechaHoraInicio = date('d/m/Y', strtotime($corte['fecha']));
    } else {
        $fechaHoraInicio = '-';
    }
    echo "<tr>
            <td>{$i}</td>
            <td>" . htmlspecialchars($fechaHoraInicio) . "</td>
            <td>" . htmlspecialchars($corte['hora_fin'] ?? '-') . "</td>
            <td>" . number_format($corte['monto_inicial'], 2) . "</td>
            <td>" . number_format($corte['monto_final'], 2) . "</td>
            <td>" . htmlspecialchars($corte['ventas']) . "</td>
            <td>" . number_format($corte['total'], 2) . "</td>
            <td>" . htmlspecialchars($corte['usuario']) . "</td>
            <td>" . htmlspecialchars($corte['estado']) . "</td>
          </tr>";
    $i++;
}
echo "</table>";
exit();
?>
