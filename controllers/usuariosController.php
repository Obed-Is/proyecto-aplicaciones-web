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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $nombreUsuario = trim($_POST['nombreUsuario']);
    $contraseña = trim($_POST['contraseña']);
    $permiso = $_POST['permiso'] ?? '';

    if (empty($nombre) || empty($nombre) || empty($apellido) || empty($nombreUsuario) || empty($contraseña) || empty($permiso)) {
        echo json_encode(['success' => false, 'message' => 'Datos no validos']);
        exit();
    }

    if (strlen($nombre) < 3 || strlen($nombre) > 35) {
        echo json_encode(['success' => false, 'message' => 'Datos no validos']);
        exit();
    }

    if (strlen($apellido) < 3 || strlen($apellido) > 35) {
        echo json_encode(['success' => false, 'message' => 'Datos no validos']);
        exit();
    }

    if (strlen($correo) < 6 || strlen($correo) > 255) {
        echo json_encode(['success' => false, 'message' => 'Datos no validos']);
        exit();
    }

    if (strlen($nombreUsuario) < 4 || strlen($nombreUsuario) > 15) {
        echo json_encode(['success' => false, 'message' => 'Datos no validos']);
        exit();
    }

    if (strlen($contraseña) < 4 || strlen($contraseña) > 8) {
        echo json_encode(['success' => false, 'message' => 'Datos no validos']);
        exit();
    }

    $insercionUsuario = $userModel->agregarUsuario($nombre,$correo, $apellido, $nombreUsuario, $contraseña, $permiso);

    if ($insercionUsuario === 'rol invalido') {
        echo json_encode(['success' => false, 'message' => 'No se pudo encontrar el permiso o es invalido']);
        exit();
    } elseif ($insercionUsuario === 1) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe, ingrese un valor único']);
        exit();
    } elseif ($insercionUsuario === 2) {
        echo json_encode(['success' => false, 'message' => 'El correo electronico ya existe, ingrese un valor único']);
        exit();
    } elseif ($insercionUsuario) {
        echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Ocurrio un error al intentar agregar el usuario']);
        exit();
    }
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

    $camposModificables = ['usuario_nombre', 'usuario_apellido', 'correo_electronico', 'nombreUsuario', 'contraseña', 'nombre_rol', 'estado'];
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


    if ($peti == "idenficador") {
        echo json_encode(['success' => "error", 'message' => 'No se encontro el identificador del usuario']);
        exit();
    } elseif ($peti == 'rol invalido') {
        echo json_encode(['success' => "error", 'message' => 'No se pudo encontrar el permiso o es invalido']);
        exit();
    } elseif ($peti == "duplicado") {
        echo json_encode(['success' => "warning", 'message' => 'El nombre de usuario ya existe, ingrese un valor unico']);
        exit();
    } elseif ($peti == "duplicado_correo") {
        echo json_encode(['success' => "warning", 'message' => 'El correo electronico ya existe, ingrese un valor unico']);
        exit();
    } elseif ($peti == "exito") {
        echo json_encode(["success" => "success", "message" => "El usuario ha sido actualizado correctamente"]);
        exit();
    } elseif ($peti == "fallo") {
        echo json_encode(["success" => "error", "message" => "Ocurrio un error al intentar actualizar el usuario"]);
        exit();
    } else {
        echo json_encode(["success" => "error", "message" => "No se pudo actualizar el usuario, error al consultar"]);
        exit();
    }

}

?>