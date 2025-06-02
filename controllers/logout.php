<?php
require_once '../models/db.php';
require_once '../models/userModel.php';

session_start();

header('Content-Type: application/json');
$userModel = new UserModel();

if (isset($_SESSION['idUsuario'])) {
    $userModel->salidaSesion(); 
}

if (isset($_SESSION['corteCaja']) && $_SESSION['corteCaja'] === 'activo') {
    if (isset($_SESSION['horaCorteParcial'])) {
        unset($_SESSION['usuario'], $_SESSION['rol'], $_SESSION['fecha_entrada']);
        session_write_close();
        echo json_encode(['success' => true, 'message' => 'Cierre parcial detectado. Sesion cerrada, caja sigue activa.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Debes cerrar la caja antes de cerrar sesion.']);
    exit;
}

session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Sesion cerrada.']);
exit;
