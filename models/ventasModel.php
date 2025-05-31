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

    public function obtenerVentasConDetalles() {
        $query = "SELECT v.id, v.fecha, v.cliente, v.correo_cliente, v.monto_total, v.monto_cliente, v.monto_devuelto, v.estado, u.nombreUsuario as usuario
                  FROM ventas v
                  LEFT JOIN usuarios u ON v.idUsuario = u.idUsuario
                  ORDER BY v.fecha DESC";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            $row['detalles'] = $this->obtenerDetallesVenta($row['id']);
            $ventas[] = $row;
        }
        return $ventas;
    }

    // Obtener detalles de una venta por su ID
    public function obtenerDetallesVenta($idVenta) {
        $query = "SELECT d.idProducto, p.nombre, d.cantidad, d.precio
                  FROM detalles_ventas d
                  JOIN productos p ON d.idProducto = p.id
                  WHERE d.idVenta = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('i', $idVenta);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        return $detalles;
    }

    // Obtener ventas por idCorteCaja
    public function obtenerVentasPorCorte($idCorteCaja) {
        $sql = "SELECT v.*, u.nombre as usuario
                FROM ventas v
                LEFT JOIN usuarios u ON v.idUsuario = u.idUsuario
                WHERE v.idCorteCaja = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $idCorteCaja);
        $stmt->execute();
        $result = $stmt->get_result();
        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            // Obtener detalles de la venta
            $row['detalles'] = $this->obtenerDetallesVenta($row['id']);
            $ventas[] = $row;
        }
        return $ventas;
    }

}
?>