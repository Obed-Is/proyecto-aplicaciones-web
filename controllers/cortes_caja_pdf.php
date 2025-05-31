<?php
require_once '../models/db.php';
require_once '../models/cortesCajaModel.php';
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

session_start();

date_default_timezone_set('America/El_Salvador');

$cortesModel = new CortesCajaModel();
$cortes = $cortesModel->obtenerCortesCaja();

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Oro Verde');
$pdf->SetAuthor('Oro Verde');
$pdf->SetTitle('Reporte de Cortes de Caja');
$pdf->SetMargins(10, 15, 10);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Cortes de Caja', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 8, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
$pdf->Ln(3);

$html = <<<EOD
<style>
    table { border-collapse: collapse; width: 100%; font-size: 10pt; }
    th, td { border: 1px solid #bbb; padding: 5px 4px; text-align: left; vertical-align: middle; }
    th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
</style>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha y Hora Inicio</th>
            <th>Hora Fin</th>
            <th>Monto Inicial</th>
            <th>Monto Final</th>
            <th>Ventas</th>
            <th>Total Ganancia</th>
            <th>Usuario</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
EOD;

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
    $html .= '<tr>
        <td>' . $i . '</td>
        <td>' . htmlspecialchars($fechaHoraInicio) . '</td>
        <td>' . htmlspecialchars($corte['hora_fin'] ?? '-') . '</td>
        <td>$' . number_format($corte['monto_inicial'], 2) . '</td>
        <td>$' . number_format($corte['monto_final'], 2) . '</td>
        <td>' . htmlspecialchars($corte['ventas']) . '</td>
        <td>$' . number_format($corte['total'], 2) . '</td>
        <td>' . htmlspecialchars($corte['usuario']) . '</td>
        <td>' . htmlspecialchars($corte['estado']) . '</td>
    </tr>';
    $i++;
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('reporte_cortes_caja.pdf', 'I');
exit();
?>
