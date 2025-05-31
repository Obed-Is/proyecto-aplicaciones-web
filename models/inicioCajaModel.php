<?php
class InicioCajaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function abrirCaja($fechaInicio, $horaInicio, $montoInicial, $idUsuario, $estado): bool|int|string {
        $query = "INSERT INTO cortes_caja (fecha, hora_inicio, monto_inicial, usuario_id, estado) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->getConnection()->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param('ssdis', $fechaInicio, $horaInicio, $montoInicial, $idUsuario, $estado);

        if($stmt->execute()){
            return $this->db->getConnection()->insert_id;
        }
        return false;
    }

    public function cerrarCaja($horaFinal, $montoFinal, $numVentas, $totalDeVentas, $estado, $idCaja) {
        $query = "UPDATE cortes_caja SET hora_fin = ?, monto_final = ?, ventas = ?, total = ?, estado = ?
                WHERE id = ?";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('ssidsi', $horaFinal, $montoFinal, $numVentas, $totalDeVentas, $estado, $idCaja);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        }
        return false;
    }
}
?>
