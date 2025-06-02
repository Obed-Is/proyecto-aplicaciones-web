<?php
require_once '../models/db.php';
require_once '../models/inicioCajaModel.php';
// require_once '../models/ventasModel.php';

session_start();

header('Content-Type: application/json');
date_default_timezone_set('America/El_Salvador');
$cajaModel = new InicioCajaModel();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['montoInicial'])) {
    $fechaInicio = date('Y-m-d');
    $horaInicio = date('H:i:s');
    $montoInicial = $data['montoInicial'];
    $idUsuario = $_SESSION['idUsuario'];
    $estado = 'Activo';

    $peticionCaja = $cajaModel->abrirCaja($fechaInicio, $horaInicio, $montoInicial, $idUsuario, $estado);

    if (is_int($peticionCaja)) {
        $_SESSION['montoInicial'] = $montoInicial;
        $_SESSION['corteCaja'] = 'activo';
        $_SESSION['idCorteCaja'] = $peticionCaja;
        echo json_encode(['success' => true, 'message' => 'Corte de caja iniciado correctamente.', 'idCorteCaja' => $peticionCaja]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al iniciar el corte de caja.']);
        unset($_SESSION['corteCaja']);
    }
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data['tipoCorte'] === 'cerrar') {
    if (!isset($_SESSION['montoInicial'], $_SESSION['idCorteCaja'])) {
        echo json_encode(['success' => false, 'message' => 'No se encontró información de la caja en la sesion.']);
        exit();
    }

    $horaFinal = date('H:i:s');
    $totalDeVentas = $_SESSION['totalVentasCaja'] ?? 0.00;
    $montoInicial = $_SESSION['montoInicial'];
    $montoFinal = $montoInicial + $totalDeVentas;

    $numVentas = $_SESSION['numeroDeVentas'] ?? 0;
    $idCaja = $_SESSION['idCorteCaja'];
    $estado = 'Finalizado';

    $peticionCorteCaja = $cajaModel->cerrarCaja($horaFinal, $montoFinal, $numVentas, $totalDeVentas, $estado, $idCaja);

    if ($peticionCorteCaja === true) {
        echo json_encode(['success' => true, 'message' => 'Se finalizó el corte de caja correctamente']);
        unset($_SESSION['idCorteCaja'], $_SESSION['totalVentasCaja'], $_SESSION['numeroDeVentas'], $_SESSION['montoInicial'], $_SESSION['corteCaja']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error al intentar cerrar la caja, comunícate con administración para reportarlo.']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data['tipoCorte'] === 'parcial') {
    // Guardar la hora del corte parcial
    $_SESSION['horaCorteParcial'] = time(); // Timestamp Unix
    $_SESSION['usuarioCorteParcial'] = $_SESSION['idUsuario'];
    echo json_encode(['success' => true, 'message' => 'Corte parcial realizado. La caja sigue activa.']);
    exit();
}



?>