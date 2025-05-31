<?php
require_once '../models/db.php';
require_once '../models/proveedoresModel.php';

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
