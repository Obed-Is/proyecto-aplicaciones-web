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
        die("ID de usuario no especificado.");
    }

    $userModel = new UserModel();
    $sesionesData = $userModel->consultarSesiones($idUsuario);

    // Crear PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configuracion del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Oro Verde');
    $pdf->SetTitle('Informe de Sesiones de Usuario - ' . $usuario_nombre);
    $pdf->SetSubject('Informe de Actividad de Usuario');

    // Margenes
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Saltos de pagina automaticos
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Modo de imagen
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Fuente por defecto
    $pdf->SetFont('helvetica', '', 10);

    // Añadir una pagina
    $pdf->AddPage();

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Contenido de la cabecera
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell(0, 15, 'INFORME DE SESIONES DE USUARIO', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell(0, 5, 'Empresa: Oro Verde', 0, 1, 'R');
    $pdf->Cell(0, 5, 'Fecha de informe: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    $pdf->Ln(5);
    $pdf->SetDrawColor(190, 190, 190); 
    $pdf->Line(10, $pdf->getY(), $pdf->getPageWidth() - 10, $pdf->getY());
    $pdf->Ln(10); 
    // --- FIN CABECERA  ---

    // Informacion del usuario
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


    $totalSesiones = count($sesionesData);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 7, 'Total de sesiones registradas: ' . $totalSesiones, 0, 1, 'L');
    $pdf->Ln(8);

    // --- TABLA DE SESIONES ---
    $pdf->SetFillColor(230, 240, 255);
    $pdf->SetTextColor(0, 0, 0); 
    $pdf->SetFont('helvetica', 'B', 11);

    $w = array(60, 60, 50);

    // Encabezados de la tabla
    $pdf->Cell($w[0], 10, 'Fecha de Entrada', 1, 0, 'C', 1);
    $pdf->Cell($w[1], 10, 'Fecha de Salida', 1, 0, 'C', 1);
    $pdf->Cell($w[2], 10, 'Duracion', 1, 1, 'C', 1);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(255, 255, 255); // Fondo blanco para filas de datos
    $pdf->SetTextColor(0, 0, 0); // Texto negro

    $rowCounter = 0;
    foreach ($sesionesData as $row) {
        $entrada = $row['entrada'] ? date('d/m/Y H:i:s', strtotime($row['entrada'])) : 'N/A';
        $salida = $row['salida'] ? date('d/m/Y H:i:s', strtotime($row['salida'])) : 'Activa';
        $duracion = '';

        if ($row['entrada'] && $row['salida']) {
            $e = new DateTime($row['entrada']);
            $s = new DateTime($row['salida']);
            $interval = $e->diff($s);
            $duracion = $interval->format('%h horas %i minutos %s s');
        } else if ($row['entrada'] && !$row['salida']) {
            $e = new DateTime($row['entrada']);
            $now = new DateTime();
            $interval = $e->diff($now);
            $duracion = '-';
        } else {
            $duracion = 'N/A';
        }

        $fill = ($rowCounter % 2 == 0) ? 0 : 1; 
        if ($fill) {
            $pdf->SetFillColor(245, 245, 245); 
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $pdf->Cell($w[0], 8, $entrada, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 8, $salida, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 8, $duracion, 'LR', 1, 'L', $fill); 
        $rowCounter++;
    }

    // Salida
    $pdf->Output("informe_sesiones_{$idUsuario}.pdf", 'I');
    exit();
}
?>