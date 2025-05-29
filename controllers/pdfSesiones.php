<?php
require_once '../models/db.php';
require_once '../models/userModel.php';
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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

    // Márgenes
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Saltos de página automáticos
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Modo de imagen
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Fuente por defecto
    $pdf->SetFont('helvetica', '', 10);

    // Añadir una página
    $pdf->AddPage();

    // --- CABECERA PERSONALIZADA ---
    // Puedes definir tu propia cabecera si necesitas más control
    // Eliminar cabecera por defecto para usar una personalizada
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Contenido de la cabecera
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(40, 40, 40); // Gris oscuro
    $pdf->Cell(0, 15, 'INFORME DE SESIONES DE USUARIO', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(90, 90, 90); // Gris más claro
    $pdf->Cell(0, 5, 'Empresa: Oro Verde', 0, 1, 'R');
    $pdf->Cell(0, 5, 'Fecha de informe: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    $pdf->Ln(5);
    $pdf->SetDrawColor(190, 190, 190); // Línea divisoria gris
    $pdf->Line(10, $pdf->getY(), $pdf->getPageWidth() - 10, $pdf->getY());
    $pdf->Ln(10); // Espacio después de la línea
    // --- FIN CABECERA PERSONALIZADA ---

    // Información del usuario
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(40, 40, 40); // Gris oscuro para un look más elegante
    $pdf->Cell(0, 8, 'Información del Usuario', 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(60, 60, 60); // Texto normal ligeramente más claro

    $pdf->Cell(40, 8, 'Nombre:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, $usuario_nombre, 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 8, 'Correo electronico:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, $usuario_correo, 0, 1, 'L');

    $pdf->Ln(5); // Espacio antes del siguiente bloque


    // Resumen de sesiones (Opcional, pero añade valor)
    $totalSesiones = count($sesionesData);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 7, 'Total de sesiones registradas: ' . $totalSesiones, 0, 1, 'L');
    $pdf->Ln(8);

    // --- TABLA DE SESIONES ---
    $pdf->SetFillColor(230, 240, 255); // Azul claro para el encabezado
    $pdf->SetTextColor(0, 0, 0); // Texto negro
    $pdf->SetFont('helvetica', 'B', 11);

    // Anchos de columna (ajusta según necesidad)
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
            $duracion = $interval->format('%h horas %i minutos %s s'); // Más detalle en la duracion
        } else if ($row['entrada'] && !$row['salida']) {
            $e = new DateTime($row['entrada']);
            $now = new DateTime();
            $interval = $e->diff($now);
            $duracion = '-';
        } else {
            $duracion = 'N/A';
        }

        // Alternar color de fila para mejor legibilidad
        $fill = ($rowCounter % 2 == 0) ? 0 : 1; // 0 para sin relleno (blanco), 1 para relleno
        if ($fill) {
            $pdf->SetFillColor(245, 245, 245); // Gris muy claro
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $pdf->Cell($w[0], 8, $entrada, 'LR', 0, 'L', $fill); // Borde Left/Right
        $pdf->Cell($w[1], 8, $salida, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 8, $duracion, 'LR', 1, 'L', $fill); // Borde Right, nueva línea
        $rowCounter++;
    }

    // Salida
    $pdf->Output("informe_sesiones_{$idUsuario}.pdf", 'I');
    exit();
}
?>