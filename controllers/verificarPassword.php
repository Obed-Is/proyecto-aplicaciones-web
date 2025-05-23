<?php
require_once '../models/db.php';
require_once '../models/userModel.php';
//para verificar la contraseña antes de entrar al modulo de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents("php://input"), true);
    $contraseña = $input['password'];
    $idUsuario = $_SESSION['idUsuario'];

    if (!$contraseña) {
        return;
    }

    if (!$idUsuario) {
        exit();
    }

    $userModel = new UserModel();

    $respuestaModelEstado = $userModel->verificarContraseña($idUsuario, $contraseña);

    if ($respuestaModelEstado == 1) {
        echo json_encode(["success" => true]);
        exit();
    }
    echo json_encode(["success" => false]);
    exit();

}

?>