<?php
require_once '../models/db.php';
require_once '../models/proveedoresModel.php';

// EXPORTAR PDF O EXCEL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar']) && $_POST['exportar'] === 'pdf') {
    require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');
    $proveedoresModel = new ProveedoresModel();
    $proveedores = $proveedoresModel->obtenerProveedores();

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Oro Verde');
    $pdf->SetAuthor('Oro Verde');
    $pdf->SetTitle('Reporte de Proveedores');
    $pdf->SetMargins(10, 15, 10);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Reporte de Proveedores', 0, 1, 'C');
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
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Dirección</th>
        </tr>
    </thead>
    <tbody>
EOD;

    $i = 1;
    foreach ($proveedores as $prov) {
        $html .= '<tr>
            <td>' . $i . '</td>
            <td>' . htmlspecialchars($prov['nombre']) . '</td>
            <td>' . htmlspecialchars($prov['telefono']) . '</td>
            <td>' . htmlspecialchars($prov['correo']) . '</td>
            <td>' . htmlspecialchars($prov['direccion']) . '</td>
        </tr>';
        $i++;
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('reporte_proveedores.pdf', 'I');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar']) && $_POST['exportar'] === 'excel') {
    $proveedoresModel = new ProveedoresModel();
    $proveedores = $proveedoresModel->obtenerProveedores();

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=reporte_proveedores.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Dirección</th>
          </tr>";

    $i = 1;
    foreach ($proveedores as $prov) {
        echo "<tr>
                <td>{$i}</td>
                <td>" . htmlspecialchars($prov['nombre']) . "</td>
                <td>" . htmlspecialchars($prov['telefono']) . "</td>
                <td>" . htmlspecialchars($prov['correo']) . "</td>
                <td>" . htmlspecialchars($prov['direccion']) . "</td>
              </tr>";
        $i++;
    }
    echo "</table>";
    exit();
}

header('Content-Type: application/json');
$proveedoresModel = new ProveedoresModel();
$data = json_decode(file_get_contents("php://input"), true);

// Obtener proveedores
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $proveedores = $proveedoresModel->obtenerProveedores();
    echo json_encode($proveedores);
    exit();
}

// Agregar o buscar proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['filtroBusqueda'])) {
        $filtro = "%" . $data['filtroBusqueda'] . "%";
        $proveedores = $proveedoresModel->buscarProveedor($filtro);
        echo json_encode($proveedores);
        exit();
    }
    $nombre = trim($data['nombre'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    $correo = trim($data['correo'] ?? '');
    $direccion = trim($data['direccion'] ?? '');

    // Validaciones
    if (!$nombre || strlen($nombre) < 3 || strlen($nombre) > 100) {
        echo json_encode(['success' => false, 'message' => 'Nombre requerido (3-100 caracteres)']);
        exit();
    } else if (!preg_match('/^[\p{L}\p{N}\s\-\+\.\,\(\)\'"]+$/u', $nombre)) {
        echo json_encode(['success' => false, 'message' => 'Nombre de proveedor inválido. Use solo letras, números y caracteres comunes.']);
        exit();
    }
    if (!$telefono || strlen($telefono) < 7 || strlen($telefono) > 20) {
        echo json_encode(['success' => false, 'message' => 'Teléfono requerido (7-20 caracteres)']);
        exit();
    } else if ($telefono[0] != '6' && $telefono[0] != '7') {
        echo json_encode(['success' => false, 'message' => 'El teléfono debe comenzar con 6 o 7.']);
        exit();
    } else if (!ctype_digit($telefono)) {
        echo json_encode(['success' => false, 'message' => 'El teléfono solo debe contener números.']);
        exit();
    }

    if (!$correo || strlen($correo) < 6 || strlen($correo) > 100) {
        echo json_encode(['success' => false, 'message' => 'Correo requerido y válido (6-100 caracteres)']);
        exit();
    } else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
        exit();
    }

    }
    if (!$direccion || strlen($direccion) < 5 || strlen($direccion) > 255) {
        echo json_encode(['success' => false, 'message' => 'Dirección requerida (5-255 caracteres)']);
        exit();
    }

    $res = $proveedoresModel->agregarProveedor($nombre, $telefono, $correo, $direccion);
    if ($res === 'duplicado') {
        echo json_encode(['success' => false, 'message' => 'El proveedor ya existe']);
    } elseif ($res) {
        echo json_encode(['success' => true, 'message' => 'Proveedor agregado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar proveedor']);
    }
    exit();
}

// Editar proveedor
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = intval($data['id'] ?? 0);
    $nombre = trim($data['nombre'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    $correo = trim($data['correo'] ?? '');
    $direccion = trim($data['direccion'] ?? '');

    // Validaciones
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Identificador inválido']);
        exit();
    }
    if (!$nombre || strlen($nombre) < 3 || strlen($nombre) > 100) {
        echo json_encode(['success' => false, 'message' => 'Nombre requerido (3-100 caracteres)']);
        exit();
    } else if (!preg_match('/^[\p{L}\p{N}\s\-\+\.\,\(\)\'"]+$/u', $nombre)) {
        echo json_encode(['success' => false, 'message' => 'Nombre de proveedor inválido. Use solo letras, números y caracteres comunes.']);
        exit();
    }
    if (!$telefono || strlen($telefono) < 7 || strlen($telefono) > 20) {
        echo json_encode(['success' => false, 'message' => 'Teléfono requerido (7-20 caracteres)']);
        exit();
    } else if ($telefono[0] != '6' && $telefono[0] != '7') {
        echo json_encode(['success' => false, 'message' => 'El teléfono debe comenzar con 6 o 7.']);
        exit();
    } else if (!ctype_digit($telefono)) {
        echo json_encode(['success' => false, 'message' => 'El teléfono solo debe contener números.']);
        exit();
    }

    if (!$correo || strlen($correo) < 6 || strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo requerido y válido (6-100 caracteres)']);
        exit();
    } else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
        exit();
    }

    if (!$direccion || strlen($direccion) < 5 || strlen($direccion) > 255) {
        echo json_encode(['success' => false, 'message' => 'Dirección requerida (5-255 caracteres)']);
        exit();
    }

    $res = $proveedoresModel->editarProveedor($id, $nombre, $telefono, $correo, $direccion);
    if ($res === 'duplicado') {
        echo json_encode(['success' => false, 'message' => 'El proveedor ya existe']);
    } elseif ($res) {
        echo json_encode(['success' => true, 'message' => 'Proveedor editado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al editar proveedor']);
    }
    exit();
}

// Eliminar proveedor
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($data['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Identificador inválido']);
        exit();
    }
    $res = $proveedoresModel->eliminarProveedor($id);
    if ($res) {
        echo json_encode(['success' => true, 'message' => 'Proveedor eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar proveedor']);
    }
    exit();
}
