<?php
class CortesCajaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function iniciarCorte($usuario_id, $montoInicial) {
        if (!$usuario_id || !is_numeric($usuario_id)) return false;
        // Solo permite un corte activo o pausado por usuario
        $sqlCheck = "SELECT id FROM cortes_caja WHERE usuario_id=? AND estado IN ('activo','pausado') LIMIT 1";
        $stmtCheck = $this->db->getConnection()->prepare($sqlCheck);
        $stmtCheck->bind_param('i', $usuario_id);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) return false;

        $sql = "INSERT INTO cortes_caja (fecha, hora_inicio, monto_inicial, usuario_id, estado) VALUES (CURDATE(), CURTIME(), ?, ?, 'activo')";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('di', $montoInicial, $usuario_id);
        return $stmt->execute();
    }

    public function pausarCorte($usuario_id) {
        $sql = "UPDATE cortes_caja SET estado='pausado' WHERE usuario_id=? AND estado='activo'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        return $stmt->execute();
    }

    public function reanudarCorte($usuario_id) {
        $sql = "UPDATE cortes_caja SET estado='activo' WHERE usuario_id=? AND estado='pausado'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        return $stmt->execute();
    }

    public function finalizarCorte($usuario_id, $montoFinal = null, $ventas = null) {
        // Busca el corte activo o pausado
        $sql = "SELECT id, monto_inicial, fecha, hora_inicio FROM cortes_caja WHERE usuario_id=? AND estado IN ('activo','pausado') ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $corte = $result->fetch_assoc();
        if (!$corte) return false;

        // Calcular ventas y monto final automáticamente
        $fecha = $corte['fecha'];
        $hora_inicio = $corte['hora_inicio'];
        $monto_inicial = $corte['monto_inicial'];

        // Buscar ventas hechas por el usuario en ese periodo
        $sqlVentas = "SELECT COUNT(*) as num_ventas, IFNULL(SUM(monto_total),0) as suma_ventas
                      FROM ventas
                      WHERE idUsuario = ? AND fecha >= CONCAT(?, ' ', ?) AND fecha <= NOW()";
        $stmtVentas = $this->db->getConnection()->prepare($sqlVentas);
        $stmtVentas->bind_param('iss', $usuario_id, $fecha, $hora_inicio);
        $stmtVentas->execute();
        $resVentas = $stmtVentas->get_result()->fetch_assoc();

        $ventas = $resVentas['num_ventas'];
        $monto_final = $monto_inicial + $resVentas['suma_ventas'];
        $total = $monto_final - $monto_inicial;

        // Actualizar el corte
        $sql2 = "UPDATE cortes_caja SET hora_fin=CURTIME(), monto_final=?, ventas=?, total=?, estado='finalizado' WHERE id=?";
        $stmt2 = $this->db->getConnection()->prepare($sql2);
        $stmt2->bind_param('didi', $monto_final, $ventas, $total, $corte['id']);
        return $stmt2->execute();
    }

    public function obtenerCorteActivo($usuario_id) {
        $sql = "SELECT * FROM cortes_caja WHERE usuario_id=? AND estado IN ('activo','pausado') ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function obtenerCortes($usuario_id = null, $esAdmin = false) {
        if ($esAdmin) {
            $sql = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.monto_inicial, c.monto_final, c.ventas, c.total, u.nombre AS usuario, c.estado
                    FROM cortes_caja c
                    JOIN usuarios u ON c.usuario_id = u.idUsuario
                    ORDER BY c.id DESC";
            $stmt = $this->db->getConnection()->prepare($sql);
        } else {
            $sql = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.monto_inicial, c.monto_final, c.ventas, c.total, u.nombre AS usuario, c.estado
                    FROM cortes_caja c
                    JOIN usuarios u ON c.usuario_id = u.idUsuario
                    WHERE c.usuario_id = ?
                    ORDER BY c.id DESC";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param('i', $usuario_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $cortes = [];
        while ($row = $result->fetch_assoc()) {
            $cortes[] = $row;
        }
        return $cortes;
    }

    public function obtenerCortePorId($id, $usuario_id = null, $esAdmin = false) {
        if ($esAdmin) {
            $sql = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.monto_inicial, c.monto_final, c.ventas, c.total, u.nombre AS usuario, c.estado
                    FROM cortes_caja c
                    JOIN usuarios u ON c.usuario_id = u.idUsuario
                    WHERE c.id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param('i', $id);
        } else {
            $sql = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.monto_inicial, c.monto_final, c.ventas, c.total, u.nombre AS usuario, c.estado
                    FROM cortes_caja c
                    JOIN usuarios u ON c.usuario_id = u.idUsuario
                    WHERE c.id = ? AND c.usuario_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param('ii', $id, $usuario_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Eliminar método registrarCorte con observaciones
    public function registrarCorte($usuario_id, $montoInicial, $montoFinal, $ventas, $horaInicio, $horaFin) {
        $sql = "INSERT INTO cortes_caja (fecha, hora_inicio, hora_fin, monto_inicial, monto_final, ventas, total, usuario_id, estado)
                VALUES (CURDATE(), ?, ?, ?, ?, ?, (? - ?), ?, 'finalizado')";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param(
            'ssddiddi',
            $horaInicio,
            $horaFin,
            $montoInicial,
            $montoFinal,
            $ventas,
            $montoFinal,
            $montoInicial,
            $usuario_id
        );
        return $stmt->execute();
    }

    // Añadir este método para reportes (PDF/Excel)
    public function obtenerCortesCaja() {
        $sql = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.monto_inicial, c.monto_final, c.ventas, c.total, u.nombre AS usuario, c.estado
                FROM cortes_caja c
                JOIN usuarios u ON c.usuario_id = u.idUsuario
                ORDER BY c.id DESC";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $cortes = [];
        while ($row = $result->fetch_assoc()) {
            $cortes[] = $row;
        }
        return $cortes;
    }
}
?>
