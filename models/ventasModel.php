<?php
class VentasModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function registrarVenta($cliente, $telefono, $producto, $cantidad) {
        $query = "INSERT INTO ventas (cliente, telefono, producto, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("sssi", $cliente, $telefono, $producto, $cantidad);
        return $stmt->execute();
    }
}
?>
