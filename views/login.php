<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesion - Oro Verde</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>

    <div class="card p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-3">
            <img src="../logo.png" alt="Oro Verde" class="logo mb-2">
            <h4 class="fw-bold text-success">Oro Verde</h4>
            <p class="text-muted">Sistema de Acceso</p>
        </div>


        <form action="../controllers/loginController.php" method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control" required 
                autocomplete="username" value="<?php echo (isset($_SESSION['credenciales']['usuario'])) ? $_SESSION['credenciales']['usuario'] : '' ?>" autofocus>
            </div>
            <div class="mb-3 position-relative">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" class="form-control" required
                    autocomplete="current-password" value="<?php echo (isset($_SESSION['credenciales']['contraseña'])) ? $_SESSION['credenciales']['contraseña'] : '' ?>">
                <i class="bi pt-4 mt-1 bi-eye-fill show-password" onclick="viewPassForm()"></i>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-green text-white">Ingresar</button>
            </div>
            <!-- MENSAJE DE ERROR DE LAS CREDENCIALES -->
            <?php if (isset($_SESSION['errores']['login'])): ?>
                <div
                    class="text-center my-3 px-3 py-2 bg-danger bg-opacity-10 text-danger rounded-2 fw-semibold">
                    <?= $_SESSION['errores']['login'] ?>
                </div>
                <?php unset($_SESSION['errores']['login']); // se borra el mensaje de un solo para que no se repita ?>
            <?php endif; ?>
        </form>

        <div class="text-center mt-3">
            <a href="#" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
        </div>
    </div>

    <script>
        function viewPassForm() {
            const password = document.getElementById('contraseña');
            const icon = document.querySelector('.show-password');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>