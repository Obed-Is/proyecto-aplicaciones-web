<?php
class ProductosModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function categoriaExiste($idCategoria) {
        $idCategoria = intval($idCategoria); // Asegura que sea entero
        $query = "SELECT id FROM categorias WHERE id = ? AND estado = 1";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $idCategoria);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function agregarProducto($codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen, $idProveedor) {
        if (!$this->categoriaExiste($idCategoria)) {
            throw new Exception("La categoría seleccionada no existe.");
        }
        $query = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, estado, idCategoria, stock_minimo, imagen, idProveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($query);

        // Permitir que $imagen sea null (por ejemplo, en importación)
        $imagenData = null;
        if ($imagen && isset($imagen['tmp_name']) && is_uploaded_file($imagen['tmp_name'])) {
            $imagenData = file_get_contents($imagen['tmp_name']);
        } else if (is_string($imagen) && $imagen !== '') {
            $imagenData = $imagen;
        } else {
            $imagenData = null;
        }

        // Si hay imagen, usar 'b' (blob), si no, usar 's' (null string)
        if ($imagenData !== null) {
            $stmt->bind_param("sssdisiibi", $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagenData, $idProveedor);
            $stmt->send_long_data(8, $imagenData);
        } else {
            // Cambia el tipo de dato de la imagen a 's' y pasa a null
            $stmt->bind_param("sssdisiisi", $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagenData, $idProveedor);
        }
        if (!$stmt->execute()) {
            error_log('Error en agregarProducto: ' . $stmt->error);
            throw new Exception('Error al agregar producto: ' . $stmt->error);
        }
        return true;
    }

    public function obtenerProductos() {
        $query = "SELECT p.id, p.codigo, p.nombre, p.descripcion, p.precio, p.stock, p.estado, p.idCategoria, p.imagen, p.stock_minimo, p.idProveedor, 
                         pr.nombre as proveedor_nombre, c.nombre as nombre_categoria
                  FROM productos p
                  LEFT JOIN proveedores pr ON p.idProveedor = pr.id
                  LEFT JOIN categorias c ON p.idCategoria = c.id WHERE p.estado != -1";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($producto = $result->fetch_assoc()) {
            $productos[] = $producto;
        }
        return $productos;
    }

    //obtener un producto por id
    public function obtenerProductoPorId($id) {
        $query = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    //editar producto
    public function editarProducto($id, $codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagen, $idProveedor) {
        if (!$this->categoriaExiste($idCategoria)) {
            throw new Exception("La categoría seleccionada no existe.");
        }
        $query = "";
        $params = [];
        $types = "";

        if ($imagen && isset($imagen['tmp_name']) && is_uploaded_file($imagen['tmp_name']) && $imagen['size'] > 0) {
            $imagenData = file_get_contents($imagen['tmp_name']);
            $query = "UPDATE productos SET codigo=?, nombre=?, descripcion=?, precio=?, stock=?, estado=?, idCategoria=?, stock_minimo=?, imagen=?, idProveedor=? WHERE id=?";
            $types = "sssdisiibii";
            $params = [$codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $imagenData, $idProveedor, $id];
        } else {
            $query = "UPDATE productos SET codigo=?, nombre=?, descripcion=?, precio=?, stock=?, estado=?, idCategoria=?, stock_minimo=?, idProveedor=? WHERE id=?";
            $types = "sssdisiiii";
            $params = [$codigo, $nombre, $descripcion, $precio, $stock, $estado, $idCategoria, $stock_minimo, $idProveedor, $id];
        }

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param($types, ...$params);
        if (isset($imagenData)) {
            $stmt->send_long_data(8, $imagenData);
        }
        return $stmt->execute();
    }

    //eliminar producto
    public function eliminarProducto($id) {
        $query = "UPDATE productos SET estado = -1 WHERE id=?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function buscarProducto($nombre_producto)
{
    $query = "SELECT p.id, p.codigo, p.nombre, p.descripcion, p.precio, p.stock, p.estado, p.idCategoria, p.stock_minimo, 
                         p.idProveedor, pr.nombre as proveedor_nombre, c.nombre as nombre_categoria
                  FROM productos p
                  LEFT JOIN proveedores pr ON p.idProveedor = pr.id
                  LEFT JOIN categorias c ON p.idCategoria = c.id
                  WHERE p.estado = 1 AND p.nombre LIKE ?";
    $stmt = $this->db->getConnection()->prepare($query);
    $stmt->bind_param('s', $nombre_producto);
    $stmt->execute();
    $result = $stmt->get_result();

    $productos = [];
    while ($producto = $result->fetch_assoc()) {
        $productos[] = $producto;
    }

    return $productos;
}

    public function codigoExiste($codigo, $excluirId = null) {
        $query = "SELECT COUNT(*) as total FROM productos WHERE codigo = ?";
        if ($excluirId) {
            $query .= " AND id != ?";
        }
        $stmt = $this->db->getConnection()->prepare($query);
        if ($excluirId) {
            $stmt->bind_param('si', $codigo, $excluirId);
        } else {
            $stmt->bind_param('s', $codigo);
        }
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        return $total > 0;
    }



}


?>