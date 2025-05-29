<?php
class ProveedoresModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerProveedores() {
        $query = "SELECT id, nombre, telefono, correo, direccion FROM proveedores";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }
        return $proveedores;
    }

    public function agregarProveedor($nombre, $telefono, $correo, $direccion) {
        if ($this->existeProveedor($nombre, $correo)) {
            return 'duplicado';
        }
        $query = "INSERT INTO proveedores (nombre, telefono, correo, direccion) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('ssss', $nombre, $telefono, $correo, $direccion);
        return $stmt->execute();
    }

    public function editarProveedor($id, $nombre, $telefono, $correo, $direccion) {
        if ($this->existeProveedor($nombre, $correo, $id)) {
            return 'duplicado';
        }
        $query = "UPDATE proveedores SET nombre=?, telefono=?, correo=?, direccion=? WHERE id=?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('ssssi', $nombre, $telefono, $correo, $direccion, $id);
        return $stmt->execute();
    }

    public function eliminarProveedor($id) {
        $query = "DELETE FROM proveedores WHERE id=?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function buscarProveedor($filtro) {
        $query = "SELECT id, nombre, telefono, correo, direccion FROM proveedores WHERE nombre LIKE ? OR correo LIKE ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result();
        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }
        return $proveedores;
    }

    private function existeProveedor($nombre, $correo, $excluirId = null) {
        $query = "SELECT COUNT(*) as total FROM proveedores WHERE (nombre = ? OR correo = ?)";
        if ($excluirId) {
            $query .= " AND id != ?";
        }
        $stmt = $this->db->getConnection()->prepare($query);
        if ($excluirId) {
            $stmt->bind_param('ssi', $nombre, $correo, $excluirId);
        } else {
            $stmt->bind_param('ss', $nombre, $correo);
        }
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        return $total > 0;
    }
}
?>
