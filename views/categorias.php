<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
$esAdmin = ($_SESSION['rol'] == 'administrador');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Oro Verde</title>
  <link rel="shortcut icon" href="../logo.png" type="image/x-icon" />
    <!-- PARA USO DE Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../css/categorias.css" />
    <script defer src="../js/categorias.js"></script>
</head>

<body <?php if ($esAdmin) echo 'data-admin="1"'; ?>>
    <?php include 'includes/navbar.php' ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4 panel-title">Categorias</h2>

        <div class="row mb-4 align-items-center">
            <div class="d-flex col-md-6">
                <div class="input-group search-bar">
                    <input type="text" class="form-control rounded-start" id="buscarCategoria"
                        placeholder="Buscar categorias...">
                    </div>
                    <button id="btnBuscar" class="btn btn-light ms-2">Buscar</button>
            </div>
            <?php if ($esAdmin): ?>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button onclick="return validarFormulario()" class="btn btn-primary btn-agregar-categoria" id="btnAddCategoria">
                    <i class="bi bi-plus-circle pe-3"></i>Agregar Categoria
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div class="card p-4 shadow-sm category-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaCategorias">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombre de la Categoria</th>
                            <th scope="col">Descripcion</th>
                            <?php if ($esAdmin): ?>
                            <th scope="col" class="text-center">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <p id="mensajeSinCategoria" class="text-center text-muted mt-3 d-none">No hay categorias para mostrar. Presionar el boton '<i class="bi bi-plus-circle"></i> Agregar categoria' para crear una nueva</p>
        </div>
    </div>

</body>
</html>