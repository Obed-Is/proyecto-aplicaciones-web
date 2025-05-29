<?php
class VentasModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function crearVenta($idUsuario, $cliente, $correo, $pagoCliente, $cambioDevuelto, $totalVenta)
    {
        $sql = "INSERT INTO ventas (idUsuario,cliente, correo_cliente, monto_cliente, monto_devuelto, monto_total, fecha) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('issddd', $idUsuario, $cliente, $correo, $pagoCliente, $cambioDevuelto, $totalVenta);

        if ($stmt->execute()) {
            return $this->db->getConnection()->insert_id;
        }
        return false;
    }

    public function agregarProductoVenta($idVenta, $idProducto, $cantidad, $precio)
    {
        $sql = "INSERT INTO detalles_ventas (idVenta, idProducto, cantidad, precio) 
            VALUES (?, ?, ?, ?)";

        $stmt = $this->db->getConnection()->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiid', $idVenta, $idProducto, $cantidad, $precio);

        return $stmt->execute();
    }

    public function reducirStockProducto($idProducto, $cantidadVendida)
    {
        $sql = "UPDATE productos SET stock = stock - ? WHERE id = ?";

        $stmt = $this->db->getConnection()->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $cantidadVendida, $idProducto);

        return $stmt->execute();
    }

    public function obtenerProductosActivos(): array   
    {
        $query = "SELECT p.id, p.codigo, p.nombre, p.descripcion, p.precio, p.stock, p.estado, p.idCategoria, p.imagen, p.stock_minimo, p.idProveedor, 
                         pr.nombre as proveedor_nombre, c.nombre as nombre_categoria
                  FROM productos p
                  LEFT JOIN proveedores pr ON p.idProveedor = pr.id
                  LEFT JOIN categorias c ON p.idCategoria = c.id WHERE p.estado = 1";
        $stmt = $this->db->getConnection()->prepare($query);
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