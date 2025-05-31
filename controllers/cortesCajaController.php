<?php
require_once '../models/db.php';
require_once '../models/cortesCajaModel.php';
session_start();

$model = new CortesCajaModel();
$esAdmin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador');
$usuario_id = $_SESSION['idUsuario'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['idUsuario'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesión expirada o usuario no autenticado']);
        exit();
    }
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'iniciar') {
        $montoInicial = floatval($_POST['monto_inicial'] ?? 0);
        $ok = $model->iniciarCorte($_SESSION['idUsuario'], $montoInicial);
        header('Content-Type: application/json');
        // Devuelve el estado del corte activo tras iniciar
        $corte = $model->obtenerCorteActivo($_SESSION['idUsuario']);
        echo json_encode(['success' => $ok, 'corte' => $corte]);
        exit();
    } elseif ($accion === 'pausar') {
        $ok = $model->pausarCorte($_SESSION['idUsuario']);
        header('Content-Type: application/json');
        // Devuelve el estado actualizado del corte
        $corte = $model->obtenerCorteActivo($_SESSION['idUsuario']);
        echo json_encode(['success' => $ok, 'corte' => $corte]);
        exit();
    } elseif ($accion === 'reanudar') {
        $ok = $model->reanudarCorte($_SESSION['idUsuario']);
        header('Content-Type: application/json');
        $corte = $model->obtenerCorteActivo($_SESSION['idUsuario']);
        echo json_encode(['success' => $ok, 'corte' => $corte]);
        exit();
    } elseif ($accion === 'finalizar') {
        $ok = $model->finalizarCorte($_SESSION['idUsuario']);
        header('Content-Type: application/json');
        $corte = $model->obtenerCorteActivo($_SESSION['idUsuario']);
        echo json_encode(['success' => $ok, 'corte' => $corte]);
        exit();
    } 
    else {
        $montoInicial = floatval($_POST['monto_inicial'] ?? 0);
        $montoFinal = floatval($_POST['monto_final'] ?? 0);
        $ventas = intval($_POST['ventas'] ?? 0);
        $horaInicio = $_POST['hora_inicio'] ?? '';
        $horaFin = $_POST['hora_fin'] ?? '';
        $usuario_id = $_SESSION['idUsuario'];

        $ok = $model->registrarCorte($usuario_id, $montoInicial, $montoFinal, $ventas, $horaInicio, $horaFin);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $corte = $model->obtenerCortePorId(intval($_GET['id']), $usuario_id, $esAdmin);
        header('Content-Type: application/json');
        echo json_encode($corte);
        exit();
    }
    $cortes = $model->obtenerCortes($usuario_id, $esAdmin);
    header('Content-Type: application/json');
    echo json_encode($cortes);
    exit();
}
?>
