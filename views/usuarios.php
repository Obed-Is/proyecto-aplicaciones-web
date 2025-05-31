<?php
session_start();

//verifica la sesion, en caso de no existir los manda al login
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] != 'administrador')) {
    header('Location: login.php');
    exit();
}
$errores = $_SESSION['errores']['nuevoUsuario'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Oro Verde</title>
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
    <!-- PARA USO DE Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Para USO DE SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../css/usuarios.css">
    <script defer src="../js/usuarios.js"></script>
</head>

<body>

    <?php include 'includes/navbar.php' ?>

    <!-- CONTENIDO PRINCIPAL  -->
    <div class="container">
        <h2 class="text-center dashboard-title mb-4 px-2">Control de usuarios - Oro Verde</h2>

        <div class="search-container mb-3">
            <div id="grupoBuscar" class="input-group">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscarUsuario" class="form-control buscar-input" placeholder="Buscar por nombre o usuario...">
                    <div class="filtro-opciones d-none d-md-flex align-items-center">
                        <label for="rol">Rol:</label>
                        <select class="form-control form-control-sm" id="buscarRol">
                            <option value="">Todos</option>
                            <option value="administrador">Administrador</option>
                            <option value="empleado">Empleado</option>
                        </select>
                        <label for="estado">Estado:</label>
                        <select class="form-control form-control-sm mr-2" id="buscarEstado">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="button" onclick="return validarFormulario()"
                class="btn btn-dark btn-sm">
                <i class="bi bi-person-plus"></i> Agregar Usuario
            </button>
        </div>
    
        <table class="table tabla-usuarios">
            <thead>
                <tr>
                    <th class="responsiv-tabla">Nombre</th>
                    <th class="responsiv-tabla">Correo electronico</th>
                    <th class="responsiv-tabla">Usuario</th>
                    <th class="responsiv-tabla">Rol</th>
                    <th class="responsiv-tabla">Estado</th>
                    <th class="responsiv-tabla">Fecha de Creacion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios">
                <!-- AQUI VAN LOS DATOS DE LOS USUARIOS -->
            </tbody>
        </table>
        <div id="mensajeSinUsuarios" class="w-50 mx-auto text-center bg-light p-2 rounded" style="display: none;">

        </div>
    </div>

</body>

</html>