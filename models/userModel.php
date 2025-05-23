<?php
class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function validarUsuario($usuario, $contraseña): array|bool
    {

        $query = "SELECT 
            u.idUsuario AS usuario_id,
            u.nombre AS usuario_nombre,
            u.apellido AS usuario_apellido,
            u.contraseña AS usuario_contraseña,
            u.estado AS estado,
            u.idRol AS usuario_idRol,
            r.nombre AS nombre_rol
          FROM usuarios u
          JOIN roles r ON u.idRol = r.id
          WHERE u.nombreUsuario = ? AND u.estado NOT IN (-1, 0)";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuarioData = $result->fetch_assoc();

            if (password_verify($contraseña, $usuarioData['usuario_contraseña'])) {
                if ($usuarioData['estado'] == 1) {
                    unset($usuarioData['usuario_contraseña']);
                    return $usuarioData;
                }
                return false;
            }
            return false;
        } else {
            return false;
        }
    }

    public function verificarContraseña($idUsuario, $contraseña)
    {
        $query = 'SELECT contraseña, estado FROM usuarios WHERE idUsuario = ? AND estado = 1';

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($contraseña, $row['contraseña'])) {
                return $row['estado'];
            }
            return false;
        }
        return false;
    }

    public function obtenerUsuarios()
    {
        $query = "SELECT 
            u.idUsuario AS usuario_id,
            u.nombre AS usuario_nombre,
            u.correo_electronico AS correo_electronico,
            u.apellido AS usuario_apellido,
            u.nombreUsuario AS nombreUsuario,
            u.fecha_registro AS fecha_registro,
            u.estado AS estado,
            u.idRol AS usuario_idRol,
            r.nombre AS nombre_rol
          FROM usuarios u
          JOIN roles r ON u.idRol = r.id 
          WHERE u.estado != -1 ORDER BY u.nombre ASC";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuarios = [];

        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }

        return $usuarios;
    }

    public function agregarUsuario($nombre, $correo, $apellido, $nombreUsuario, $contraseña, $permiso)
    {
        if ($this->evitarUsuarioDuplicado($nombreUsuario) > 0) {
            return 1;
        }

        if($this->evitarCorreoDuplicado($correo)){
                return '2';
        }

        if ($permiso != 'administrador' && $permiso != 'empleado') {
            return "rol invalido";
        }

        $passwordHash = password_hash($contraseña, PASSWORD_DEFAULT);
        $usuarioActivo = 1;

        $query = "INSERT INTO usuarios (nombre, apellido, nombreUsuario, contraseña, idRol, estado, correo_electronico)
            VALUES (?, ?, ?, ?, (SELECT id FROM roles where nombre = ?), ?, ?)";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('sssssis', $nombre, $apellido, $nombreUsuario, $passwordHash, $permiso, $usuarioActivo, $correo);

        //lo mando directamente ya que devuelve true o false, ya en el controllador se valida la respuesta
        return $stmt->execute();
    }

    private function evitarUsuarioDuplicado($nombreUsuario)
    {
        $query = 'SELECT COUNT(*) AS total FROM usuarios WHERE nombreUsuario = ? AND estado != -1';

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('s', $nombreUsuario);
        $stmt->execute();
        //vinculo el unico resultado devuelto a la variable q se le pasa
        $stmt->bind_result($totalDuplicados);
        //y aqui se extraen los datos para guardarlos en la variable vinculada
        $stmt->fetch();

        return $totalDuplicados;
    }

    private function evitarCorreoDuplicado($correo)
    {
        $query = 'SELECT COUNT(*) AS total FROM usuarios WHERE correo_electronico = ? AND estado != -1';

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        //vinculo el unico resultado devuelto a la variable q se le pasa
        $stmt->bind_result($totalDuplicados);
        //y aqui se extraen los datos para guardarlos en la variable vinculada
        $stmt->fetch();

        return $totalDuplicados;
    }

    public function eliminarUsuario($idUsuario)
    {
        $query = "UPDATE usuarios SET estado = -1 WHERE idUsuario = ?";

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        }
        return false;
    }

    public function actualizarUsuario($data)
    {
        $idUsuario = intval($data['usuario_id'] ?? 0);
        if (!$idUsuario) {
            return "identificador";
        }

        $campo = [];
        $tipoCampo = "";
        $valorCampo = [];

        if (array_key_exists('nombre_rol', $data)) {
            $permiso = trim($data['nombre_rol'] ?? '');
            $idRol = $this->obtenerIdRol($permiso);
            if (!$idRol) {
                return 'rol invalido';
            }
            $campo[] = "idRol = ?";
            $tipoCampo .= "i";
            $valorCampo[] = $idRol;
        }

        if (array_key_exists('usuario_nombre', $data)) {
            $campo[] = "nombre = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['usuario_nombre']);
        }

        if (array_key_exists('correo_electronico', $data)) {
            $correo = trim($data['correo_electronico']);
            if($this->evitarCorreoDuplicado($correo)){
                return 'duplicado_correo';
            }
            $campo[] = "correo_electronico = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['correo_electronico']);
        }

        if (array_key_exists('usuario_apellido', $data)) {
            $campo[] = "apellido = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['usuario_apellido']);
        }

        if (array_key_exists('nombreUsuario', $data)) {
            $usuario = trim($data['nombreUsuario']);
            if ($this->evitarUsuarioDuplicado($usuario)) {
                return "duplicado";
            }
            $campo[] = "nombreUsuario = ?";
            $tipoCampo .= "s";
            $valorCampo[] = $usuario;
        }

        if (array_key_exists('estado', $data)) {
            $campo[] = "estado = ?";
            $tipoCampo .= "i";
            $valorCampo[] = intval($data['estado']);
        }

        if (array_key_exists('contraseña', $data) && $data['contraseña'] !== '') {
            $campo[] = "contraseña = ?";
            $tipoCampo .= "s";
            $valorCampo[] = password_hash($data['contraseña'], PASSWORD_DEFAULT);
        }

        $valorCampo[] = $idUsuario;
        $tipoCampo .= "i";

        $query = "UPDATE usuarios SET " . implode(", ", $campo) . " WHERE idUsuario = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param($tipoCampo, ...$valorCampo);

        if ($stmt->execute()) {
            return ($stmt->affected_rows > 0) ? "exito" : "fallo";
        }

        return false;
    }

    private function obtenerIdRol($permiso)
    {
        $stmtRol = $this->db->getConnection()->prepare("SELECT id FROM roles WHERE nombre = ?");
        $stmtRol->bind_param("s", $permiso);
        $stmtRol->execute();
        $stmtRol->bind_result($idRol);
        $stmtRol->fetch();
        $stmtRol->close();

        if (!$idRol) {
            return false;
        }

        return $idRol;
    }

    public function buscarUsuarioFiltro($data)
    {
        $condiciones = [];
        $valorConsulta = [];
        $tipoConsulta = "";

        $sql = "
        SELECT 
            u.idUsuario AS usuario_id,
            u.nombreUsuario AS nombreUsuario,
            u.nombre AS usuario_nombre,
            u.apellido AS usuario_apellido,
            u.fecha_registro AS fecha_registro,
            u.estado AS estado,
            u.idRol AS usuario_idRol,
            r.nombre AS nombre_rol
        FROM usuarios u
        JOIN roles r ON u.idRol = r.id
        WHERE estado != -1
    ";

        if (!empty($data['rolFiltro'])) {
            $idRol = $this->obtenerIdRol($data['rolFiltro']);
            if (!$idRol) {
                return 'No se encontro el nivel de permiso o no es válido';
            }
            $condiciones[] = "r.id = ?";
            $valorConsulta[] = $idRol;
            $tipoConsulta .= 'i';
        }

        if (!empty($data['usuarioFiltro'])) {
            $condiciones[] = "(u.nombreUsuario LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?)";
            $valorConsulta[] = "%" . $data['usuarioFiltro'] . "%";
            $valorConsulta[] = "%" . $data['usuarioFiltro'] . "%";
            $valorConsulta[] = "%" . $data['usuarioFiltro'] . "%";
            $tipoConsulta .= "sss";
        }

        if (!empty($data['estadoFiltro'])) {
            $condiciones[] = "u.estado = ?";
            $valorConsulta[] = ($data['estadoFiltro'] == 'activo') ? 1 : 0;
            $tipoConsulta .= "i";
        }

        if (!empty($condiciones)) {
            $sql .= " AND " . implode(" AND ", $condiciones);
        }

        $stmt = $this->db->getConnection()->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en prepare: " . $this->db->getConnection()->error);
        }

        if (!empty($valorConsulta)) {
            $stmt->bind_param($tipoConsulta, ...$valorConsulta);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();

        $usuarios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $fila;
        }

        return $usuarios;
    }

}
?>