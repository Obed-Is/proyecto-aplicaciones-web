<?php
session_start();
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

if (!isset($_SESSION['ticket'])) {
    die("No hay datos para mostrar el ticket.");
}

$data = $_SESSION['ticket'];
$productos = $_SESSION['productos'];
$fechaFormateada = date('d/m/Y H:i', strtotime($data['fechaVenta']));

$totalVenta = number_format($data['totalVenta'], 2, '.', ',');
$pagoCliente = number_format($data['pagoCliente'], 2, '.', ',');
$cambioDevuelto = number_format($data['cambioDevuelto'], 2, '.', ',');

$pdf = new TCPDF('P', 'mm', [80, 200], true, 'UTF-8', false); 
$pdf->SetCreator('Oro Verde');
$pdf->SetAuthor('Oro Verde');
$pdf->SetTitle('Ticket de Venta');
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();

$html = <<<EOD
<style>
    body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 8.5pt; 
        color: #333;
        line-height: 1.2;
    }
    .header {
        text-align: center;
        font-weight: bold;
        font-size: 14pt; 
        color: #000;
        margin-bottom: 2px;
        padding-bottom: 3px;
    }
    .subheader {
        text-align: center;
        font-size: 7.5pt;
        margin-bottom: 7px;
        color: #555;
    }
    .divider {
        border-bottom: 1px dashed #666;
        margin: 5px 0;
    }
    .section-title {
        font-weight: bold;
        font-size: 9pt;
        margin-top: 8px;
        margin-bottom: 3px;
        text-align: center;
        color: #444;
    }
    .info-table {
        width: 100%;
        margin-bottom: 7px;
        border-collapse: collapse;
    }
    .info-table td {
        padding: 1px 0px;
        vertical-align: top;
        font-size: 8pt;
    }
    .productos-table {
        width: 100%;
        margin-bottom: 6px;
        border-collapse: collapse;
        font-size: 7.5pt;
    }
    .productos-table thead th {
        font-weight: bold; 
        font-size: 8pt;
        background-color: #f8f8f8; 
        padding: 3px 4px;
        border-bottom: 1.5px solid #bbb;
        text-align: left;
    }
    .productos-table tbody tr:nth-child(odd) {
        background-color: #ffffff; 
    }
    .productos-table tbody td {
        padding: 2px 4px;
        border-bottom: 1px dotted #e0e0e0; 
        vertical-align: middle;
    }
    .productos-table tbody tr:last-child td {
        border-bottom: none;
    }

    .productos-table th.producto-col, .productos-table td.producto-col {
        width: 48%; 
        text-align: left;
    }
    .productos-table th.cantidad-col, .productos-table td.cantidad-col {
        width: 12%;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .productos-table th.precio-unit-col, .productos-table td.precio-unit-col {
        width: 20%; 
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    .productos-table th.subtotal-col, .productos-table td.subtotal-col {
        width: 20%;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .totales-table {
        width: 100%;
        margin-top: 8px; 
        border-collapse: collapse;
    }
    .totales-table td {
        padding: 2px 0px;
        font-size: 8pt;
    }
    .totales-table .total-label {
        font-weight: bold;
        text-align: left;
    }
    .totales-table .total-value {
        font-weight: bold;
        text-align: right;
        font-size: 9pt; 
        color: #000;
    }
    .totales-table tr:first-child .total-value {
        font-size: 10pt; 
        color: #000;
    }
    .footer {
        text-align: center;
        font-style: italic;
        font-size: 7.5pt;
        margin-top: 12px;
        padding-top: 5px;
        border-top: 1px dashed #666;
        color: #555;
    }
</style>

<div class="header">ORO VERDE</div>
<div class="subheader">
    4a Calle ote. Terminal de buses, San Miguel<br/>
    Correo: oroverde325@gmail.com
</div>

<div class="divider"></div>

<table class="info-table">
    <tr><td><strong>Ticket N°:</strong> {$data['idVenta']}</td></tr>
    <tr><td><strong>Fecha:</strong> {$fechaFormateada}</td></tr>
    <tr><td><strong>Personal:</strong> {$data['usuario']}</td></tr>
    <tr><td><strong>Cliente:</strong> {$data['cliente']}</td></tr>
    <tr><td><strong>Correo:</strong> {$data['correo']}</td></tr>
</table>

<div class="divider"></div>

<table class="productos-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="producto-col">Producto</th>
            <th class="cantidad-col">Cant.</th>
            <th class="precio-unit-col">P. Unit.</th>
            <th class="subtotal-col">Importe</th>
        </tr>
    </thead>
    <tbody>
EOD;

foreach ($productos as $producto) {
    $nombre = htmlspecialchars(mb_strimwidth($producto['nombre'], 0, 25, "..."));
    $cantidad = $producto['cantidad'];
    $precioUnitario = number_format($producto['precio'], 2, '.', ',');
    $subtotalProducto = number_format($cantidad * $producto['precio'], 2, '.', ',');

    $html .= <<<EOD
        <tr>
            <td class="producto-col">{$nombre}</td>
            <td class="cantidad-col">{$cantidad}</td>
            <td class="precio-unit-col">{$precioUnitario}</td>
            <td class="subtotal-col">{$subtotalProducto}</td>
        </tr>
EOD;
}

$html .= <<<EOD
    </tbody>
</table>

<div class="divider"></div>

<table class="totales-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="total-label">TOTAL:</td>
        <td class="total-value">$ {$totalVenta}</td>
    </tr>
    <tr>
        <td class="total-label">Pago del cliente:</td>
        <td class="total-value">$ {$pagoCliente}</td>
    </tr>
    <tr>
        <td class="total-label">Cambio total:</td>
        <td class="total-value">$ {$cambioDevuelto}</td>
    </tr>
</table>

<div class="footer">
    ¡Gracias por su compra!<br/>
    ¡Vuelva pronto!
</div>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');

unset($_SESSION['ticket']);
unset($_SESSION['productos']);

$pdf->Output('ticket_venta_' . $data['idVenta'] . '.pdf', 'I');
?>