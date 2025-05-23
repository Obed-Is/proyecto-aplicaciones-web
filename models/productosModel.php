<?php
class ProductosModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function agregarProducto($nombre, $descripcion, $precio, $stock, $imagen) {
        $imagenData = file_get_contents($imagen['tmp_name']);
        $query = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("ssdib", $nombre, $descripcion, $precio, $stock, $imagenData);
        return $stmt->execute();
    }
}
?>
