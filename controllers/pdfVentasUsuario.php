<?php
require_once '../models/db.php';
require_once '../models/userModel.php';
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    date_default_timezone_set('America/El_Salvador');
    $idUsuario = $_GET['id'] ?? null;
    $usuario_nombre = $_GET['nombre'] ?? 'Sin nombre';
    $usuario_correo = $_GET['correo'] ?? '-';

    if (!$idUsuario) {
        die("ID no especificado.");
    }

    $userModel = new UserModel();
    $ventasUsuario = $userModel->consultarVentasUsuario($idUsuario);

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Oro Verde');
    $pdf->SetTitle('Informe de ventas - ' . $usuario_nombre);
    $pdf->SetSubject('Informe de ventas');

    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->AddPage();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell(0, 15, 'INFORME DE VENTAS DEL USUARIO', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell(0, 5, 'Empresa: Oro Verde', 0, 1, 'R');
    $pdf->Cell(0, 5, 'Fecha de informe: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    $pdf->Ln(5);
    $pdf->SetDrawColor(190, 190, 190);
    $pdf->Line(10, $pdf->getY(), $pdf->getPageWidth() - 10, $pdf->getY());
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell(0, 8, 'Informacion del Usuario', 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(60, 60, 60);

    $pdf->Cell(40, 8, 'Nombre:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, $usuario_nombre, 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 8, 'Correo electronico:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, $usuario_correo, 0, 1, 'L');

    $pdf->Ln(5);

    $totalSesiones = count($ventasUsuario);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 7, 'Total de sesiones registradas: ' . $totalSesiones, 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->SetFillColor(230, 240, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);

    $w = array(32, 26, 26, 30, 30, 40);

    $pdf->Cell($w[0], 10, 'Fecha', 1, 0, 'C', 1);
    $pdf->Cell($w[1], 10, 'Monto total', 1, 0, 'C', 1);
    $pdf->Cell($w[2], 10, 'Monto cliente', 1, 0, 'C', 1);
    $pdf->Cell($w[3], 10, 'Monto devuelto', 1, 0, 'C', 1);
    $pdf->Cell($w[4], 10, 'Cliente', 1, 0, 'C', 1);
    $pdf->Cell($w[5], 10, 'Correo cliente', 1, 1, 'C', 1);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

    $rowCounter = 0;
    $totalVentas = 0;

    foreach ($ventasUsuario as $row) {
        $fecha = $row['fecha'] ?? '';
        $montoTotal = '$' . number_format($row['monto_total'], 2);
        $montoCliente = '$' . number_format($row['monto_cliente'], 2);
        $montoDevuelto = '$' . number_format($row['monto_devuelto'], 2);
        $cliente = $row['cliente'] ?? '';
        $correo = $row['correo_cliente'] ?? '';

        $fill = ($rowCounter % 2 == 0) ? 0 : 1;
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);

        $pdf->Cell($w[0], 8, $fecha, 'LR', 0, 'L', 1);
        $pdf->Cell($w[1], 8, $montoTotal, 'LR', 0, 'R', 1);
        $pdf->Cell($w[2], 8, $montoCliente, 'LR', 0, 'R', 1);
        $pdf->Cell($w[3], 8, $montoDevuelto, 'LR', 0, 'R', 1);
        $pdf->Cell($w[4], 8, $cliente, 'LR', 0, 'L', 1);
        $pdf->Cell($w[5], 8, $correo, 'LR', 1, 'L', 1);

        $totalVentas += $row['monto_total'];
        $rowCounter++;
    }

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(230, 240, 255);
    $pdf->Cell($w[0], 8, 'Total vendido', 1, 0, 'L', 1);
    $pdf->Cell($w[1], 8, '', 1, 0, 'R', 1);
    $pdf->Cell($w[2], 8, '', 1, 0, 'R', 1);
    $pdf->Cell($w[3], 8, '', 1, 0, 'R', 1);
    $pdf->Cell($w[4], 8, '', 1, 0, 'R', 1);
    $pdf->Cell($w[5], 8, '$' . number_format($totalVentas, 2), 1, 1, 'R', 1);

    $pdf->Output("informe_ventas_{$idUsuario}.pdf", 'I');
    exit();
}
?>
