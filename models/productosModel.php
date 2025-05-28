<?php
class ProductosModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Nueva función para validar existencia de categoría activa
    private function categoriaExiste($idCategoria) {
        $idCategoria = intval($idCategoria); // Asegura que sea entero
        $query = "SELECT id FROM categorias WHERE id = ? AND estado = 1";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $idCategoria);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function agregarProducto($codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen) {
        // Validar existencia de categoría
        if (!$this->categoriaExiste($idCategoria)) {
            throw new Exception("La categoría seleccionada no existe.");
        }
        $query = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, estado, idCategoria, stock_minimo, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($query);

        // Si hay imagen válida, cargarla, si no, pasar null
        if ($imagen && isset($imagen['tmp_name']) && is_uploaded_file($imagen['tmp_name'])) {
            $imagenData = file_get_contents($imagen['tmp_name']);
        } else {
            $imagenData = null;
        }

        // Tipos: sssdisiib (string, string, string, double, int, int, int, int, blob)
        $stmt->bind_param("sssdisiib", $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagenData);
        // Para blobs grandes, usar send_long_data
        if ($imagenData !== null) {
            $stmt->send_long_data(8, $imagenData);
        }
        return $stmt->execute();
    }

    public function obtenerProductos() {
        $query = "SELECT id, codigo, nombre, descripcion, precio, stock, estado, idCategoria, imagen, stock_minimo FROM productos";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($producto = $result->fetch_assoc()) {
            $productos[] = $producto;
        }
        return $productos;
    }

    // Nuevo método para obtener un producto por id
    public function obtenerProductoPorId($id) {
        $query = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Nuevo método para editar producto
    public function editarProducto($id, $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen) {
        // Validar existencia de categoría
        if (!$this->categoriaExiste($idCategoria)) {
            throw new Exception("La categoría seleccionada no existe.");
        }
        $query = "";
        $params = [];
        $types = "";

        if ($imagen && isset($imagen['tmp_name']) && is_uploaded_file($imagen['tmp_name']) && $imagen['size'] > 0) {
            $imagenData = file_get_contents($imagen['tmp_name']);
            $query = "UPDATE productos SET codigo=?, nombre=?, descripcion=?, precio=?, stock=?, estado=?, idCategoria=?, stock_minimo=?, imagen=? WHERE id=?";
            $types = "sssdisiibi";
            $params = [$codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagenData, $id];
        } else {
            $query = "UPDATE productos SET codigo=?, nombre=?, descripcion=?, precio=?, stock=?, estado=?, idCategoria=?, stock_minimo=? WHERE id=?";
            $types = "sssdisiii";
            $params = [$codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $id];
        }

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param($types, ...$params);
        // Si hay imagen, usar send_long_data
        if (isset($imagenData)) {
            $stmt->send_long_data(8, $imagenData);
        }
        return $stmt->execute();
    }

    // Nuevo método para eliminar producto
    public function eliminarProducto($id) {
        $query = "DELETE FROM productos WHERE id=?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function buscarProducto($nombre_producto)
{
    $query = "SELECT id, codigo, nombre, descripcion, precio, stock, estado, idCategoria, stock_minimo 
              FROM productos 
              WHERE estado = 1 AND nombre LIKE ?";

    $stmt = $this->db->getConnection()->prepare($query);
    // El controlador ya agrega los comodines %
    $stmt->bind_param('s', $nombre_producto);

    $stmt->execute();
    $result = $stmt->get_result();

    $productos = [];

    while ($producto = $result->fetch_assoc()) {
        $productos[] = $producto;
    }

    return $productos;
}

}


?>