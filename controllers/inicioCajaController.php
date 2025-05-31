<?php
require_once '../models/db.php';
require_once '../models/inicioCajaModel.php';
// require_once '../models/ventasModel.php';

session_start();

header('Content-Type: application/json');
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
        $_SESSION['montoInicialCaja'] = $montoInicial;
        $_SESSION['corteCaja'] = 'activo';
        $_SESSION['idCorteCaja'] = $peticionCaja;
        echo json_encode(['success' => true, 'message' => 'Corte de caja iniciado correctamente.', 'idCorteCaja' => $peticionCaja]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al iniciar el corte de caja.']);
        unset($_SESSION['corteCaja']);
    }
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data['tipoCorte'] === true) {
    if (!isset($_SESSION['montoInicialCaja'], $_SESSION['idCorteCaja'])) {
        echo json_encode(['success' => false, 'message' => 'No se encontró información de la caja en sesión.']);
        exit();
    }

    $horaFinal = date('H:i:s');
    $totalDeVentas = $_SESSION['totalVentasCaja'] ?? 0.00;
    $montoInicial = $_SESSION['montoInicialCaja'];
    $montoFinal = $montoInicial + $totalDeVentas;

    $numVentas = $_SESSION['numeroDeVentas'] ?? 0;
    $idCaja = $_SESSION['idCorteCaja'];
    $estado = 'Finalizado';

    $peticionCorteCaja = $cajaModel->cerrarCaja($horaFinal, $montoFinal, $numVentas, $totalDeVentas, $estado, $idCaja);

    if($peticionCorteCaja === true){
        echo json_encode(['success' => true, 'message' => 'Se finalizó el corte de caja correctamente']);
        unset($_SESSION['idCorteCaja'], $_SESSION['totalVentasCaja'], $_SESSION['numeroDeVentas'], $_SESSION['montoInicialCaja'], $_SESSION['corteCaja']);
    }else{
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error al intentar cerrar la caja, comunícate con administración para reportarlo.']);
    }
    exit();
}




?>