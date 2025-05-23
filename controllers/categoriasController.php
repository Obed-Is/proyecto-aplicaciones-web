<?php

require_once '../models/db.php';
require_once '../models/categoriasModel.php';

session_start();

header('Content-Type: application/json');
$categoriasModel = new categoriasModel();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['nombre_categoriaModificar'])) {
    $nombre_categoriaModificada = trim($data['nombre_categoriaModificar']);
    $descripcion_categoriaModificada = trim($data['descripcion_categoriaModificar']);
    $id_categoria = trim($data['id_categoria']);


    if (empty($nombre_categoriaModificada) || empty($descripcion_categoriaModificada)) {
        echo json_encode(["success" => false, "message" => "Debe completar todos los campos correctamente", "icon" => "warning"]);
        exit();
    }
    // aqui puede dar alerta de error, pero es un falso positivo osea q esta bien, se quita por ratos y vuelve
    $peticion = $categoriasModel->editarCategoria($nombre_categoriaModificada, $descripcion_categoriaModificada, $id_categoria);

    if ($peticion === 'duplicado') {
        echo json_encode(["success" => "warning", "message" => "Ya existe el nombre de la categoria, ingresa una distinta"]);
        exit();
    }

    if ($peticion) {
        echo json_encode(["success" => "success", "message" => "La categoria ha sido actualizada correctamente"]);
        exit();
    }

    echo json_encode(["success" => "error", "message" => "No se pudo actualizar la categoria, ocurrio un error al consultar"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_categoria'])) {
    $nombre_categoria = trim($_POST['nombre_categoria']);
    $descripcion_categoria = trim($_POST['descripcion_categoria']);

    if (empty($nombre_categoria) || empty($descripcion_categoria)) {
        echo json_encode(["success" => false, "message" => "Debe completar todos los campos correctamente"]);
        exit();
    }

    $peticion = $categoriasModel->agregarCategoria($nombre_categoria, $descripcion_categoria);

    if ($peticion === 'sin datos') {
        echo json_encode(["success" => false, "message" => "No se enviaron datos o ocurrio un error al recibirlos correctamente", "icon" => "warning"]);
        exit();
    }

    if ($peticion === 'duplicado') {
        echo json_encode(["success" => false, "message" => "Ya existe una categoria con el mismo nombre", "icon" => "warning"]);
        exit();
    }

    if ($peticion === true) {
        echo json_encode(['success' => true, 'message' => 'La categoria se creo correctamente', "icon" => "success"]);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Ocurrio un error al intentar crear la categoria', "icon" => "error"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['filtroBusqueda'])) {

    if (trim(empty($data['filtroBusqueda']))) {
        echo json_encode(['success' => false, 'message' => 'No se encontro el filtro a buscar']);
        exit();
    }
    $nombre_categoria = "%" . $data['filtroBusqueda'] . "%";
    // aqui puede dar error pero es falso negativo ya q toma como si la funcion no devolviera nada aun que si devuelve xd
    $peticion = $categoriasModel->buscarCategoria($nombre_categoria);

    echo json_encode($peticion);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $peticion = $categoriasModel->obtenerCategorias();

    if ($peticion) {
        echo json_encode($peticion);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Ocurrio un error al encontrar la categoria']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (!isset($data['idCategoria'])) {
        echo json_encode(['success' => "error", 'message' => 'No se encontro el identificador de la categoria']);
        exit();
    }

    $peticion = $categoriasModel->eliminarCategoria($data['idCategoria']);

    if ($peticion === 'Productos en categoria') {
        echo json_encode(['success' => "warning", 
        'message' => 'No se pudo eliminar la categoria, existen productos asignados a esta categoria, se recomienda mover los productos a una categoria diferente y luego eliminar la categoria',
        'titulo' => "No se puede eliminar"]);
        exit();
    }

    if ($peticion === true) {
        echo json_encode(['success' => "success", 'message' => 'Se elimino la categoria correctamente', 'titulo' => "Eliminacion exitosa"]);
        exit();
    }

    echo json_encode(['success' => "error", 'message' => 'Ocurrio un error al intentar eliminar la categoria', "titulo" => "Ocurrio un error"]);
    exit();
}


?>