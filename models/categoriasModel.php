<?php

class categoriasModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function obtenerCategorias()
    {
        $query = "SELECT id AS id_categoria, nombre AS nombre_categoria, descripcion AS descripcion_categoria
                    FROM categorias WHERE estado = 1";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];

        while ($categoria = $result->fetch_assoc()) {
            $categorias[] = $categoria;
        }

        return $categorias;
    }


    public function agregarCategoria($nombre_categoria, $descripcion_categoria)
    {

        if (empty(trim($nombre_categoria)) || empty(trim($descripcion_categoria))) {
            return 'sin datos';
        }

        if ($this->evitarCategoriaDuplicada($nombre_categoria) === 'duplicado') {
            return 'duplicado';
        }

        $query = "INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, ?) ";
        $estado = 1;

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('ssi', $nombre_categoria, $descripcion_categoria, $estado);

        //lo mando directamente ya que devuelve true o false, ya en el controllador se valida la respuesta
        return $stmt->execute();
    }

    private function evitarCategoriaDuplicada($nombre_categoria)
    {
        $query = "SELECT COUNT(*) AS total FROM categorias WHERE nombre = ? AND estado != -1";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('s', $nombre_categoria);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if ($resultado['total'] > 0) {
            return 'duplicado';
        }
        return false;
    }

    public function buscarCategoria($nombre_categoria)
    {
        $query = "SELECT id AS id_categoria, nombre AS nombre_categoria, descripcion AS descripcion_categoria
                    FROM categorias WHERE estado = 1 AND nombre LIKE ?";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('s', $nombre_categoria);
        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];

        while ($categoria = $result->fetch_assoc()) {
            $categorias[] = $categoria;
        }

        return $categorias;
    }

    public function editarCategoria($nombre_categoriaModificada, $descripcion_categoriaModificada, $id_categoria)
    {

        if ($this->evitarCategoriaDuplicada($nombre_categoriaModificada) === 'duplicado') {
            return 'duplicado';
        }

        $query = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('ssi', $nombre_categoriaModificada, $descripcion_categoriaModificada, $id_categoria);

        if ($stmt->execute()) {
            return ($stmt->affected_rows > 0) ? true : false;
        }

        return false;
    }

    public function eliminarCategoria($idCategoria)
    {

        $query = "SELECT COUNT(*) AS total FROM productos WHERE idCategoria = ?";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if ($resultado['total'] > 0) {
            return 'Productos en categoria';
        }

        $consulta = "DELETE FROM categorias WHERE id = ?";

        $stmt = $this->db->getConnection()->prepare($consulta);
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        }

        return false;
    }

}

?>