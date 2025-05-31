<?php
require_once '../models/db.php';
require_once '../models/userModel.php';

session_start();

header('Content-Type: application/json');

if (isset($_SESSION['corteCaja']) && $_SESSION['corteCaja'] === 'activo') {
    echo json_encode(['success' => false, 'message' => 'Debes cerrar la caja antes de cerrar sesion']);
    exit;
}

$userModel = new UserModel();

$userModel->salidaSesion();
session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Sesion cerrada']);
exit;
?>
