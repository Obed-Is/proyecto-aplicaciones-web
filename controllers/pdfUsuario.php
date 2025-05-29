<?php
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $jsonData = $_GET['data'];
    $userData = json_decode($jsonData, true);
}

// Asegúrate de que el salario esté formateado correctamente antes de insertarlo en el HTML
$salarioFormateado = '$' . number_format($userData['salario'], 2);

// Preparar el texto de estado con la clase CSS para el color
$estadoTexto = ($userData['estado'] == 1) ? '<span class="status-activo">Activo</span>' : '<span class="status-inactivo">Inactivo</span>';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Oro Verde');
$pdf->SetAuthor('Oro Verde');
$pdf->SetTitle('Perfil de Usuario');

// Margenes: izquierda, superior, derecha. (ajusta superior si el encabezado lo requiere)
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20); // Salto de pagina automatico con margen inferior
$pdf->AddPage();

// Encabezado principal (estas celdas se renderizaran antes que el HTML)
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Ficha de Perfil de Usuario', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 7, 'Informacion detallada de: ' . $userData['usuario_nombre'] . ' ' . $userData['usuario_apellido'], 0, 1, 'C');
$pdf->Ln(10); // Espacio entre el encabezado y el contenido HTML

// Estilos CSS y estructura HTML para replicar el diseño de la imagen
$html = <<<EOD
<style>
    /* VARIABLES DE COLOR (puedes ajustar estos valores si es necesario, pero intentamos apegarnos a la imagen) */
    /* TCPDF no soporta ':root' ni variables CSS. Los colores se repetiran. */
    /* Colores aproximados de la imagen */
    .color-verde-oscuro { color: #4CAF50; } /* Usado para línea de título y estado Activo */
    .color-gris-claro-fondo { background-color: #F8F8F8; } /* Fondo de filas pares */
    .color-gris-borde { border-color: #E0E0E0; } /* Borde de tabla y líneas de fila */
    .color-texto-label { color: #666666; } /* Texto de las etiquetas de campo */
    .color-texto-valor { color: #333333; } /* Texto de los valores */
    .color-azul-rol { color: #2196F3; } /* Texto del rol */
    .color-rojo-inactivo { color: #F44336; } /* Texto de estado Inactivo */


    body {
        font-family: 'helvetica', sans-serif;
        font-size: 10pt;
        color: #333333; /* Color de texto general */
        line-height: 1.4; /* Espaciado entre líneas para mejor lectura */
    }

    /* Estilos para los títulos de seccion (ej. "Informacion Personal") */
    .section-title {
        font-size: 13pt;
        color: #333333;
        margin-top: 180px; /* Mas espacio superior para separar del encabezado PHP */
        margin-bottom: 5px;
        padding-bottom: 5px;
        border-bottom: 2px solid #4CAF50; /* Línea verde debajo del título */
        font-weight: bold;
    }

    /* Contenedor principal (puede no ser estrictamente necesario en TCPDF para este diseño) */
    .info-card {
        background-color: #FFFFFF; /* Fondo blanco */
        padding: 0px;
        /* TCPDF no soporta box-shadow completamente */
    }

    /* Estilos generales de la tabla */
    table {
        width: 100%;
        border-collapse: collapse; /* Crucial para eliminar espacios entre celdas */
        margin-bottom: 20px; /* Espacio después de cada tabla */
        border: 1px solid #E0E0E0; /* Borde exterior de la tabla */
    }

    /* Estilos para las filas de la tabla */
    tr {
        background-color: #FFFFFF; /* Fondo por defecto para todas las filas */
    }

    /* Fondo para filas pares */
    tr:nth-child(even) {
        background-color: #F8F8F8; /* Fondo gris claro */
    }

    /* Estilos para todas las celdas (td) */
    td {
        padding: 8px 12px; /* Relleno interno para todas las celdas */
        border-left: none; /* Eliminar bordes verticales */
        border-right: none; /* Eliminar bordes verticales */
        border-top: none;   /* Eliminar borde superior */
        border-bottom: 1px solid #E0E0E0; /* Solo borde inferior */
        vertical-align: middle; /* Centrar verticalmente el texto */
    }

    /* La última fila de la tabla no debe tener borde inferior (simula el diseño de la imagen) */
    table tr:last-child td {
        border-bottom: none;
    }

    /* Estilos para la columna de "Campo" (primera columna) */
    td.label {
        width: 35%; /* Ancho fijo para las etiquetas, ajusta si es necesario. (TCPDF puede ignorar esto si no hay thead) */
        font-weight: bold;
        color: #666666;
    }

    /* Estilos para la columna de "Valor" (segunda columna) */
    td.value {
        width: 65%; /* El resto del ancho */
        color: #333333;
    }

    /* Estilos específicos para el estado y el rol */
    .status-activo {
        color: #4CAF50; /* Verde para Activo */
        font-weight: bold;
    }
    .status-inactivo {
        color: #F44336; /* Rojo para Inactivo */
        font-weight: bold;
    }
    .rol-admin {
        color: #2196F3; /* Azul para Administrador */
        font-weight: bold;
    }
   
</style>

<div class="info-card">
    <div class="section-title">Informacion Personal</div>
    <table>
        <tr>
            <td class="label">Nombre Completo:</td>
            <td class="value">{$userData['usuario_nombre']} {$userData['usuario_apellido']}</td>
        </tr>
        <tr>
            <td class="label">DUI:</td>
            <td class="value">{$userData['dui']}</td>
        </tr>
        <tr>
            <td class="label">Correo Electronico:</td>
            <td class="value">{$userData['correo_electronico']}</td>
        </tr>
        <tr>
            <td class="label">Teléfono:</td>
            <td class="value">{$userData['telefono']}</td>
        </tr>
        <tr>
            <td class="label">Fecha de Nacimiento:</td>
            <td class="value">{$userData['fecha_nacimiento']}</td>
        </tr>
        <tr>
            <td class="label">Direccion:</td>
            <td class="value">{$userData['direccion']}</td>
        </tr>
    </table>


    <p class="separador"></p>
    <div class="section-title">Cuenta y Empleo</div>

    <table>
        <tr>
            <td class="label">Nombre de Usuario:</td>
            <td class="value">{$userData['nombreUsuario']}</td>
        </tr>
        <tr>
            <td class="label">Rol:</td>
            <td class="value"><span class="rol-admin">{$userData['nombre_rol']}</span></td>
        </tr>
        <tr>
            <td class="label">Fecha de Registro:</td>
            <td class="value">{$userData['fecha_registro']}</td>
        </tr>
        <tr>
            <td class="label">Estado:</td>
            <td class="value">{$estadoTexto}</td>
        </tr>
        <tr>
            <td class="label">Salario:</td>
            <td class="value">{$salarioFormateado}</td>
        </tr>
        <tr>
            <td class="label">Tipo de Contrato:</td>
            <td class="value">{$userData['tipo_contrato']}</td>
        </tr>
    </table>
</div>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');

// Pie de pagina (estas celdas se renderizaran después del HTML)
$pdf->Ln(8); // Espacio antes del pie de pagina
$pdf->SetFont('helvetica', 'I', 8);
// Alinea a la derecha para no solaparse con el número de pagina si TCPDF lo pone automaticamente
$pdf->Cell(0, 10, 'Generado por Oro Verde - Pagina ' . $pdf->getPage() . ' de ' . $pdf->getNumPages(), 0, 0, 'C');


// Salida del PDF
$pdf->Output('perfil_usuario_' . $userData['usuario_id'] . '.pdf', 'I');
exit();
?>