<?php
require_once '../models/db.php';
require_once '../models/userModel.php';
require_once '../models/inicioCajaModel.php';
date_default_timezone_set('America/El_Salvador');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contraseña'];
    session_start();

    $userModel = new UserModel();
    $cajaModel = new InicioCajaModel();
    $usuarioData = $userModel->validarUsuario($usuario, $contrasena);

    if ($usuarioData) {
        if (isset($_SESSION['horaCorteParcial'])) {
            $horaParcial = $_SESSION['horaCorteParcial'];

            // Prueba de 1 minuto
            if (time() - $horaParcial > 60) {
                $idCaja = $_SESSION['idCorteCaja'];
                $horaFinal = date('H:i:s');
                $totalDeVentas = $_SESSION['totalVentasCaja'] ?? 0.00;
                $montoInicial = $_SESSION['montoInicial'];
                $montoFinal = $montoInicial + $totalDeVentas;
                $numVentas = $_SESSION['numeroDeVentas'] ?? 0;

                $estado = 'Finalizado';
                $peticionCorteCaja = $cajaModel->cerrarCaja($horaFinal, $montoFinal, $numVentas, $totalDeVentas, $estado, $idCaja);

                if ($peticionCorteCaja === true) {
                    unset($_SESSION['idCorteCaja'], $_SESSION['totalVentasCaja'], $_SESSION['numeroDeVentas'], $_SESSION['montoInicial'], $_SESSION['corteCaja'], $_SESSION['horaCorteParcial'], $_SESSION['usuarioCorteParcial']);
                }
            } else {
                // si Es el mismo usuario se quita el corte parcial
                if (isset($_SESSION['usuarioCorteParcial']) && $_SESSION['usuarioCorteParcial'] == $usuarioData['usuario_id']) {
                    unset($_SESSION['horaCorteParcial'], $_SESSION['usuarioCorteParcial']);
                } else {
                    //  Otro usuario inicia sesion se cierra la caja
                    $idCaja = $_SESSION['idCorteCaja'];
                    $horaFinal = date('H:i:s');
                    $totalDeVentas = $_SESSION['totalVentasCaja'] ?? 0.00;
                    $montoInicial = $_SESSION['montoInicial'];
                    $montoFinal = $montoInicial + $totalDeVentas;
                    $numVentas = $_SESSION['numeroDeVentas'] ?? 0;

                    $estado = 'Finalizado';
                    $peticionCorteCaja = $cajaModel->cerrarCaja($horaFinal, $montoFinal, $numVentas, $totalDeVentas, $estado, $idCaja);

                    if ($peticionCorteCaja === true) {
                        unset($_SESSION['idCorteCaja'], $_SESSION['totalVentasCaja'], $_SESSION['numeroDeVentas'], $_SESSION['montoInicial'], $_SESSION['corteCaja'], $_SESSION['horaCorteParcial'], $_SESSION['usuarioCorteParcial']);
                    }
                }
            }
        }

        $_SESSION['usuario'] = $usuarioData['usuario_nombre'] . ' ' . $usuarioData['usuario_apellido'];
        $_SESSION['rol'] = $usuarioData['nombre_rol'];
        $_SESSION['idUsuario'] = $usuarioData['usuario_id'];
        $_SESSION['fecha_entrada'] = date('Y-m-d H:i:s');
        $userModel->inicioSesion();
        header('Location: ../views/dashboard.php');
        unset($_SESSION['credenciales']);
        exit();

    } else {
        // Usuario o contraseña incorrectos
        $_SESSION['credenciales']['usuario'] = $usuario;
        $_SESSION['credenciales']['contraseña'] = $contrasena;
        $_SESSION['errores']['login'] = 'Usuario o contraseña incorrectos';
        header('Location: ../views/login.php');
        exit();
    }
}
?>
