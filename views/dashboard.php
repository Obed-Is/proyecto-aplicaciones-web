<?php
session_start();

//con esto se verifica la sesion, en caso de no existir los manda al login
if (!isset($_SESSION['usuario'])) {
  header('Location: login.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel administrativo - Oro Verde</title>
  <link rel="shortcut icon" href="../logo.webp" type="image/x-icon" />
  <!-- PARA USO DE Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Para USO DE SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
  <link rel="stylesheet" href="../css/dashboard.css" />
  <script defer src="../js/dashboard.js"></script>

</head>

<body>
  <!-- Navbar fija superior -->
  <?php include 'includes/navbar.php' ?>

  <!-- Contenido principal -->
  <div class="container py-5 mt-5">
    <h2 class="text-center dashboard-title mb-4 px-2">Panel administrativo - Oro Verde</h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">

      <!-- Boton de Usuarios -->
      <div class="col">
        <button type="button" id="btnUsuarios" name="btnUsuarios"
          class="glass-card w-100 text-dark text-decoration-none <?php echo ($_SESSION['rol'] == 'administrador') ? '' : 'disableCard' ?>"
          <?php echo ($_SESSION['rol'] == 'administrador') ? 'data-permiso="true"' : '' ?>>
          <i class="bi bi-people-fill glass-icon"></i>
          <h5>Usuarios</h5>
        </button>
      </div>

      <!-- Enlace a Productos -->
      <div class="col">
        <a href="productos.php" class="glass-card w-100 text-dark text-decoration-none d-block">
          <i class="bi bi-box-seam glass-icon"></i>
          <h5>Productos</h5>
        </a>
      </div>

      <!-- Enlace a Categorias -->
      <div class="col">
        <a href="categorias.php" class="glass-card w-100 text-dark text-decoration-none d-block">
          <i class="bi bi-sliders glass-icon"></i>
          <h5>Categorias</h5>
        </a>
      </div>

      <!-- Enlace a Proveedores -->
      <div class="col">
        <a href="proveedores.php" class="glass-card w-100 text-dark text-decoration-none d-block">
          <i class="bi bi-box-seam glass-icon"></i>
          <h5>Proveedores</h5>
        </a>
      </div>

      <!-- Enlace a Crear Venta -->
      <div class="col">
        <a href="ventas.php" class="glass-card w-100 text-dark text-decoration-none d-block">
          <i class="bi bi-cart-plus-fill glass-icon"></i>
          <h5>Crear Venta</h5>
        </a>
      </div>

      <!-- Enlace a Reportes -->
      <div class="col">
        <a href="<?php echo ($_SESSION['rol'] == 'administrador') ? 'reportes.php' : '#' ?>"
          class="glass-card w-100 text-dark text-decoration-none d-block <?php echo ($_SESSION['rol'] == 'administrador') ? '' : 'disableCard' ?>">
          <i class="bi bi-file-earmark-bar-graph glass-icon"></i>
          <h5>Reportes</h5>
        </a>
      </div>

      <!-- Enlace a Reportar Problemas -->
      <div class="col">
        <a href="reportarProblema.php" class="glass-card w-100 text-dark text-decoration-none d-block">
          <i class="bi bi-exclamation-triangle-fill glass-icon"></i>
          <h5>Reportar Problema</h5>
        </a>
      </div>

      <!-- Enlace a reporte de cortes de cajas -->
      <div class="col">
        <a href="cortes_caja.php"
          class="glass-card w-100 text-dark text-decoration-none d-block ">
          <i class="bi bi-cash-coin glass-icon"></i>
          <h5>Reporte de cortes de caja</h5>
        </a>
      </div>

      <!-- Enlace a inicio de cortes de cajas -->
      <div class="col">
        <a href="inicio_cortes_caja.php"
          class="glass-card w-100 text-dark text-decoration-none d-block">
          <i class="bi bi-cash-stack glass-icon"></i>
          <h5>Control corte de caja</h5>
        </a>
      </div>
    </div>
  </div>




</body>

</html>