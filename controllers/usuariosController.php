<?php
require_once '../models/db.php';
require_once '../models/userModel.php';

header('Content-Type: application/json');
$userModel = new UserModel();
$data = json_decode(file_get_contents("php://input"), true);

# RECIBE LA PETICION DE BUSCAR UN USUARIO POR MEDIO DE LOS FILTROS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['usuarioFiltro'])) {

    $peticion = $userModel->buscarUsuarioFiltro($data);

    if ($peticion) {
        echo json_encode($peticion);
        exit();
    }

    echo json_encode(0);
    exit();
}
# ES EL GET INICIAL PARA OBTENER LOS USUARIOS DE LA DB
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // si llega a dar aviso o error, es un falso negativo ya que cambia aveces por el valor retornado
    $usuarios = $userModel->obtenerUsuarios();

    echo json_encode($usuarios);
    exit();
}
# AQUI SE VALIDA NUEVAMENTE Y SE HACE LA PETICION PARA AGREGAR UN NUEVO USUARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_nombre'])) {
    $nombre = trim($_POST['usuario_nombre']);
    $apellido = trim($_POST['usuario_apellido']);
    $dui = trim($_POST['dui']);
    $correo = trim($_POST['correo_electronico']);
    $direccion = trim($_POST['direccion']);
    $municipio = trim($_POST['municipio']);
    $nombreUsuario = trim($_POST['nombreUsuario']);
    $contraseña = trim($_POST['contraseña']);
    $contrato = trim($_POST['contrato']);
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
    $salario = trim($_POST['salario']);
    $telefono = trim($_POST['telefono']);
    $permiso = $_POST['nombre_rol'] ?? '';
    $estado = $_POST['estado'] ?? 1;

    if (
        empty($nombre) || empty($apellido) || empty($dui) || empty($correo) || empty($direccion) ||
        empty($municipio) || empty($nombreUsuario) || empty($contraseña) || empty($fechaNacimiento) ||
        empty($salario) || empty($telefono) || empty($permiso)
    ) {
        echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
        exit();
    }

    $insercionUsuario = $userModel->agregarUsuario(
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
        $fechaNacimiento,
        $salario,
        $telefono,
        $estado
    );

    if ($insercionUsuario === 'dui duplicado') {
        echo json_encode(['success' => false, 'message' => 'El DUI ya esta registrado']);
        exit();
    }

    if ($insercionUsuario === 'telefono duplicado') {
        echo json_encode(['success' => false, 'message' => 'El teléfono ya esta registrado']);
        exit();
    }

    if ($insercionUsuario === 'rol invalido') {
        echo json_encode(['success' => false, 'message' => 'No se pudo encontrar el permiso o es invalido']);
        exit();
    }
    if ($insercionUsuario === 1) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe, ingrese un valor único']);
        exit();
    }
    if ($insercionUsuario === 2) {
        echo json_encode(['success' => false, 'message' => 'El correo electronico ya existe, ingrese un valor único']);
        exit();
    }
    if ($insercionUsuario === true) {
        echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Ocurrio un error al intentar agregar el usuario']);
    exit();
}
# ESTO ES PARA ELIMINAR UN USUARIO
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);

    //en este caso al enviar las respuestas envio el succes con el mismo nombre porque asi toma la alerta el icono que corresponde 
    if (!isset($data['idUsuario']) || !is_numeric($data['idUsuario']) || intval($data['idUsuario']) <= 0) {
        echo json_encode(["success" => 'success', "message" => "No se encontro el identificador del usuario"]);
        exit();
    }
    if ($userModel->eliminarUsuario($data['idUsuario'])) {
        echo json_encode(['success' => 'success', "message" => "El usuario ha sido eliminado correctamente"]);
        exit();
    }
    echo json_encode(["success" => 'error', "message" => "Ocurrio un error al intentar eliminar el usuario"]);
    exit();
}
# AQUI SE VALIDA PARA ACTUALIZAR LOS DATOS DEL USUARIO Y TAMBIEN HACER LA PETICION PARA ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $usuarioData = json_decode(file_get_contents("php://input"), true);

    if (empty($usuarioData['usuario_id'])) {
        echo json_encode(['success' => "error", 'message' => 'No se encontro el identificador del usuario']);
        exit();
    }

    $camposModificables = [
        'usuario_nombre',
        'usuario_apellido',
        'dui',
        'correo_electronico',
        'direccion',
        'nombreUsuario',
        'contraseña',
        'tipo_contrato',
        'fecha_nacimiento',
        'salario',
        'telefono',
        'nombre_rol',
        'estado'
    ];

    $camposPresentes = array_intersect_key($usuarioData, array_flip($camposModificables));

    if (count($camposPresentes) === 0) {
        echo json_encode(['success' => "info", 'message' => 'No se enviaron datos para actualizar']);
        exit();
    }

    if (isset($usuarioData['usuario_nombre'])) {
        if (strlen($usuarioData['usuario_nombre']) < 3 || strlen($usuarioData['usuario_nombre']) > 35) {
            echo json_encode(['success' => "error", 'message' => 'El nombre debe tener entre 3 y 35 caracteres']);
            exit();
        }
    }

    if (isset($usuarioData['usuario_apellido'])) {
        if (strlen($usuarioData['usuario_apellido']) < 3 || strlen($usuarioData['usuario_apellido']) > 35) {
            echo json_encode(['success' => "error", 'message' => 'El apellido debe tener entre 3 y 35 caracteres']);
            exit();
        }
    }

    if (isset($usuarioData['correo_electronico'])) {
        if (strlen($usuarioData['correo_electronico']) < 6 || strlen($usuarioData['correo_electronico']) > 255) {
            echo json_encode(['success' => "error", 'message' => 'El apellido debe tener entre 6 y 255 caracteres']);
            exit();
        }
    }

    if (isset($usuarioData['nombreUsuario'])) {
        if (strlen($usuarioData['nombreUsuario']) < 4 || strlen($usuarioData['nombreUsuario']) > 15) {
            echo json_encode(['success' => "error", 'message' => 'El nombre de usuario debe tener entre 4 y 15 caracteres']);
            exit();
        }
    }

    if (isset($usuarioData['contraseña']) && $usuarioData['contraseña'] !== "") {
        if (strlen($usuarioData['contraseña']) < 4 || strlen($usuarioData['contraseña']) > 8) {
            echo json_encode(['success' => "error", 'message' => 'La contraseña debe tener entre 4 y 8 caracteres']);
            exit();
        }
    }

    if (isset($usuarioData['estado'])) {
        if ($usuarioData['estado'] != 0 && $usuarioData['estado'] != 1) {
            echo json_encode(['success' => "error", 'message' => 'El estado debe ser 0 o 1']);
            exit();
        }
    }

    $peti = $userModel->actualizarUsuario($usuarioData);


    if ($peti === "idenficador") {
        echo json_encode(['success' => "error", 'message' => 'No se encontro el identificador del usuario']);
        exit();
    }
    if ($peti === 'rol invalido') {
        echo json_encode(['success' => "error", 'message' => 'No se pudo encontrar el permiso o es invalido']);
        exit();
    }
    if ($peti === "duplicado") {
        echo json_encode(['success' => "warning", 'message' => 'El nombre de usuario ya existe, ingrese un valor unico']);
        exit();
    }
    if ($peti === "duplicado_correo") {
        echo json_encode(['success' => "warning", 'message' => 'El correo electronico ya existe, ingrese un valor unico']);
        exit();
    }
    if ($peti === "duplicado_dui") {
        echo json_encode(['success' => "warning", 'message' => 'El DUI ya existe, ingrese un valor unico']);
        exit();
    }
    if ($peti === "duplicado_telefono") {
        echo json_encode(['success' => "warning", 'message' => 'El telefono ya existe, ingrese un valor unico']);
        exit();
    }
    if ($peti === "exito") {
        echo json_encode(["success" => "success", "message" => "El usuario ha sido actualizado correctamente"]);
        exit();
    }
    if ($peti === "fallo") {
        echo json_encode(["success" => "error", "message" => "Ocurrio un error al intentar actualizar el usuario"]);
        exit();
    }

    echo json_encode(["success" => "error", "message" => "No se pudo actualizar el usuario, error al consultar"]);
    exit();

}

?>