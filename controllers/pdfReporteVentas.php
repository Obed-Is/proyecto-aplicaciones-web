<?php
require_once '../models/db.php';
require_once '../models/ventasModel.php';
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

session_start();

date_default_timezone_set('America/El_Salvador'); // <-- Agregado para la zona horaria correcta

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

// Crear PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Oro Verde');
$pdf->SetAuthor('Oro Verde');
$pdf->SetTitle('Reporte de Ventas');
$pdf->SetMargins(10, 15, 10);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Ventas', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 8, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
$pdf->Ln(3);

// Ajuste de anchos: suma aprox. 270mm (A4 horizontal útil)
$html = <<<EOD
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 10pt;
    }
    th, td {
        border: 1px solid #bbb;
        padding: 5px 4px;
        text-align: left;
        vertical-align: middle;
    }
    th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }
    td.monto, th.monto {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    td.productos {
        font-size: 9pt;
    }
</style>
<table>
    <thead>
        <tr>
            <th style="width:3%;">#</th>
            <th style="width:11%;">Fecha</th>
            <th style="width:13%;">Cliente</th>
            <th style="width:10%;" class="monto">Total (\$)</th>
            <th style="width:11%;" class="monto">Pago Cliente</th>
            <th style="width:9%;" class="monto">Cambio</th>
            <th style="width:13%;">Correo</th>
            <th style="width:13%;">Usuario</th>
            <th style="width:17%;">Productos</th>
        </tr>
    </thead>
    <tbody>
EOD;

$i = 1;
foreach ($ventasFiltradas as $venta) {
    $productosStr = '';
    foreach ($venta['detalles'] as $d) {
        $productosStr .= htmlspecialchars($d['nombre']) . " (x{$d['cantidad']})<br>";
    }
    $html .= '<tr>
        <td style="width:3%;">' . $i . '</td>
        <td style="width:11%;">' . htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fecha']))) . '</td>
        <td style="width:13%;">' . htmlspecialchars($venta['cliente']) . '</td>
        <td style="width:10%;" class="monto">$' . number_format($venta['monto_total'], 2) . '</td>
        <td style="width:11%;" class="monto">$' . number_format($venta['monto_cliente'], 2) . '</td>
        <td style="width:9%;" class="monto">$' . number_format($venta['monto_devuelto'], 2) . '</td>
        <td style="width:13%;">' . htmlspecialchars($venta['correo_cliente']) . '</td>
        <td style="width:13%;">' . htmlspecialchars($venta['usuario'] ?? '') . '</td>
        <td style="width:17%;" class="productos">' . $productosStr . '</td>
    </tr>';
    $i++;
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('reporte_ventas.pdf', 'I');
exit();
?>
