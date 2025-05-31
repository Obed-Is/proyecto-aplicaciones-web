<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Enviar Reporte a Administracion - Oro Verde</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="../logo.webp" type="image/x-icon">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SWEET ALERT -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
    <!-- ARCHIVOS BASE -->
    <link rel="stylesheet" href="../css/reportarProblema.css" />
    <script defer src="../js/reportarProblema.js"></script>
</head>

<body>

    <?php include 'includes/navbar.php' ?>

    <div class="container-fluid">
        <div class="report-container">
            <div class="report-header text-center mb-4">
                <img src="../logo.webp" alt="Logo Oro Verde" class="img-fluid" style="max-height: 80px;">
                <h2 class="mt-3">Enviar Reporte</h2>
                <p class="text-muted">Envia un informe o reporta un problema a la administracion</p>
            </div>

            <!-- FORMULARIO -->
            <form enctype="multipart/form-data" method="POST" action="../controllers/enviarCorreoReporte.php">
                <div class="mb-3">
                    <label for="asunto" class="form-label">Asunto</label>
                    <input type="text" class="form-control" id="asunto" name="asunto" required maxlength="50"
                        minlength="3"
                        value="<?php (isset($_SESSION["emailAsunto"])) ? $_SESSION["emailAsunto"] : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="mensaje" class="form-label">Mensaje del Reporte</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required minlength="3"
                        value="<?php (isset($_SESSION["emailMensaje"])) ? $_SESSION["emailAsunto"] : '' ?>"></textarea>
                </div>

                <div class="mb-3">
                    <label for="adjunto" class="form-label">Adjuntar archivo (opcional)</label>
                    <input type="file" class="form-control" id="adjunto" name="adjunto" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                </div>

                <button type="submit" class="btn btn-success btn-send">Enviar Reporte</button>
            </form>
        </div>
    </div>


</body>

</html>