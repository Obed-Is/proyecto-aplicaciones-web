<?php
require_once '../models/db.php';
require_once '../models/userModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contraseña'];
    session_start();

    $userModel = new UserModel();
    $usuarioData = $userModel->validarUsuario($usuario, $contrasena);

    if ($usuarioData) {
        $_SESSION['usuario'] = $usuarioData['usuario_nombre'] . ' ' . $usuarioData['usuario_apellido'];
        $_SESSION['rol'] = $usuarioData['nombre_rol'];
        $_SESSION['idUsuario'] = $usuarioData['usuario_id'];
        date_default_timezone_set('America/El_Salvador');
        $_SESSION['fecha_entrada'] = date('Y-m-d H:i:s');
        $userModel->inicioSesion();
        header('Location: ../views/dashboard.php');
        unset($_SESSION['credenciales']);
        exit();
    } else {
        $_SESSION['credenciales']['usuario'] = $usuario;
        $_SESSION['credenciales']['contraseña'] = $contrasena;
        $_SESSION['errores']['login'] = 'Usuario o contraseña incorrectos';
        header('Location: ../views/login.php');
        exit();
    }
}
?>