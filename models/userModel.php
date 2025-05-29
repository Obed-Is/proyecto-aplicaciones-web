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
            r.nombre AS nombre_rol,
            ud.dui AS dui,
            ud.direccion_completa AS direccion,
            ud.fecha_nacimiento AS fecha_nacimiento,
            ud.salario AS salario,
            ud.telefono AS telefono,
            ud.tipo_contrato AS tipo_contrato
          FROM usuarios u
          JOIN roles r ON u.idRol = r.id 
          JOIN usuarios_detalles ud ON ud.usuario_id = u.idUsuario
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

    public function agregarUsuario(
        $nombre,
        $correo,
        $apellido,
        $nombreUsuario,
        $contraseña,
        $contrato,
        $permiso,
        $dui,
        $direccion,
        $municipio,
        $fecha_nacimiento,
        $salario,
        $telefono,
        $estado = 1
    ) {
        if ($this->evitarUsuarioDuplicado($nombreUsuario) > 0) {
            return 1;
        }

        if ($this->evitarCorreoDuplicado($correo) > 0) {
            return 2;
        }

        if ($permiso != 'administrador' && $permiso != 'empleado') {
            return "rol invalido";
        }

        if ($this->evitarDuiDuplicado($dui) > 0) {
            return 'dui duplicado';
        }

        if ($this->evitarTelefonoDuplicado($telefono) > 0) {
            return 'telefono duplicado';
        }

        $passwordHash = password_hash($contraseña, PASSWORD_DEFAULT);

        // Obtener idRol
        $idRol = $this->obtenerIdRol($permiso);
        if (!$idRol) {
            return 'rol invalido';
        }

        // Insertar en tabla usuarios
        $queryUsuario = "INSERT INTO usuarios (nombre, apellido, nombreUsuario, contraseña, idRol, estado, correo_electronico)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtUsuario = $this->db->getConnection()->prepare($queryUsuario);
        $stmtUsuario->bind_param('sssssis', $nombre, $apellido, $nombreUsuario, $passwordHash, $idRol, $estado, $correo);

        if (!$stmtUsuario->execute()) {
            return false; // Error al insertar usuario
        }

        // Obtener el ID del usuario insertado
        $idUsuario = $stmtUsuario->insert_id;

        // Concatenar municipio y direccion separados por coma
        $direccionCompleta = $municipio . ', ' . $direccion;

        // Insertar en tabla usuarios_detalles
        $queryDetalles = "INSERT INTO usuarios_detalles (usuario_id, dui, direccion_completa, fecha_nacimiento, salario, telefono, tipo_contrato)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtDetalles = $this->db->getConnection()->prepare($queryDetalles);
        $stmtDetalles->bind_param('isssdss', $idUsuario, $dui, $direccionCompleta, $fecha_nacimiento, $salario, $telefono, $contrato);

        if (!$stmtDetalles->execute()) {
            // Eliminar usuario para mantener consistencia por si ocurrio un error antes
            $this->db->getConnection()->query("DELETE FROM usuarios WHERE idUsuario = $idUsuario");
            return false;
        }

        return true;
    }


    public function evitarDuiDuplicado($dui)
    {
        $query = "SELECT COUNT(*) as total FROM usuarios_detalles ud
                JOIN usuarios u ON u.idUsuario = ud.usuario_id WHERE ud.dui = ? AND u.estado != -1";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('s', $dui);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total']; // Retorna el conteo de filas con ese DUI
    }

    public function evitarTelefonoDuplicado($telefono)
    {
        $query = "SELECT COUNT(*) as total FROM usuarios_detalles ud
                JOIN usuarios u ON u.idUsuario = ud.usuario_id WHERE ud.telefono = ? AND u.estado != -1";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('s', $telefono);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total']; // Retorna el conteo de filas con ese teléfono
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
            $campo[] = "usuarios.idRol = ?";
            $tipoCampo .= "i";
            $valorCampo[] = $idRol;
        }

        if (array_key_exists('usuario_nombre', $data)) {
            $campo[] = "usuarios.nombre = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['usuario_nombre']);
        }

        if (array_key_exists('correo_electronico', $data)) {
            $correo = trim($data['correo_electronico']);
            if ($this->evitarCorreoDuplicado($correo)) {
                return 'duplicado_correo';
            }
            $campo[] = "usuarios.correo_electronico = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['correo_electronico']);
        }

        if (array_key_exists('usuario_apellido', $data)) {
            $campo[] = "usuarios.apellido = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['usuario_apellido']);
        }

        if (array_key_exists('nombreUsuario', $data)) {
            $usuario = trim($data['nombreUsuario']);
            if ($this->evitarUsuarioDuplicado($usuario)) {
                return "duplicado";
            }
            $campo[] = "usuarios.nombreUsuario = ?";
            $tipoCampo .= "s";
            $valorCampo[] = $usuario;
        }

        if (array_key_exists('estado', $data)) {
            $campo[] = "usuarios.estado = ?";
            $tipoCampo .= "i";
            $valorCampo[] = intval($data['estado']);
        }

        if (array_key_exists('contraseña', $data) && $data['contraseña'] !== '') {
            $campo[] = "usuarios.contraseña = ?";
            $tipoCampo .= "s";
            $valorCampo[] = password_hash($data['contraseña'], PASSWORD_DEFAULT);
        }

        if (array_key_exists('dui', $data)) {
            if ($this->evitarDuiDuplicado(dui: $data['dui']) > 0) {
                return 'duplicado_dui';
            }
            $campo[] = "usuarios_detalles.dui = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['dui']);
        }

        if (array_key_exists('direccion', $data)) {
            $campo[] = "usuarios_detalles.direccion_completa = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['direccion']);
        }

        if (array_key_exists('tipo_contrato', $data)) {
            $campo[] = "usuarios_detalles.tipo_contrato = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['tipo_contrato']);
        }

        if (array_key_exists('fecha_nacimiento', $data)) {
            $campo[] = "usuarios_detalles.fecha_nacimiento = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['fecha_nacimiento']);
        }

        if (array_key_exists('salario', $data)) {
            $campo[] = "usuarios_detalles.salario = ?";
            $tipoCampo .= "d";
            $valorCampo[] = floatval($data['salario']);
        }

        if (array_key_exists('telefono', $data)) {
            if ($this->evitarTelefonoDuplicado($data['telefono']) > 0) {
            return 'duplicado_telefono';
        }
            $campo[] = "usuarios_detalles.telefono = ?";
            $tipoCampo .= "s";
            $valorCampo[] = trim($data['telefono']);
        }


        $valorCampo[] = $idUsuario;
        $tipoCampo .= "i";

        $query = "UPDATE usuarios JOIN usuarios_detalles ON usuarios_detalles.usuario_id = usuarios.idUsuario 
                    SET " . implode(", ", $campo) . " WHERE usuarios.idUsuario = ?";
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

        $sql = "SELECT 
            u.idUsuario AS usuario_id,
            u.nombre AS usuario_nombre,
            u.correo_electronico AS correo_electronico,
            u.apellido AS usuario_apellido,
            u.nombreUsuario AS nombreUsuario,
            u.fecha_registro AS fecha_registro,
            u.estado AS estado,
            u.idRol AS usuario_idRol,
            r.nombre AS nombre_rol,
            ud.dui AS dui,
            ud.direccion_completa AS direccion,
            ud.fecha_nacimiento AS fecha_nacimiento,
            ud.salario AS salario,
            ud.telefono AS telefono,
            ud.tipo_contrato AS tipo_contrato
          FROM usuarios u
          JOIN roles r ON u.idRol = r.id 
          JOIN usuarios_detalles ud ON ud.usuario_id = u.idUsuario
          WHERE u.estado != -1";

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


    public function inicioSesion() {
        $query = "INSERT INTO sesiones_usuario (usuario_id, fecha_entrada) VALUES (?, ?)";
        $fecha_entrada = $_SESSION['fecha_entrada'];
        $idUsuario = $_SESSION['idUsuario'];
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('is', $idUsuario, $fecha_entrada);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function salidaSesion() {
        $query = "UPDATE sesiones_usuario SET fecha_salida = ? WHERE usuario_id = ? AND fecha_salida
                IS NULL ORDER BY id DESC LIMIT 1";

        date_default_timezone_set('America/El_Salvador');
        $fecha_salida = date('Y-m-d H:i:s');
        $idUsuario = $_SESSION['idUsuario'];

        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param('si', $fecha_salida, $idUsuario);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function consultarSesiones($idUsuario) {
    $query = "SELECT fecha_entrada AS entrada, fecha_salida AS salida FROM sesiones_usuario WHERE usuario_id = ?";

    $stmt = $this->db->getConnection()->prepare($query);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $sesiones = [];
    while ($fila = $result->fetch_assoc()) {
        $sesiones[] = $fila;
    }

    return $sesiones;
}


}
?>