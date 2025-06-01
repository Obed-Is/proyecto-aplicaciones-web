<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2c3e50 !important;">
    <div class="container-fluid">
        <a class="navbar-brand text-lime" href="../views/dashboard.php" style="font-weight: bold;">Oro Verde - Panel Administrativo</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="d-flex align-items-center flex-wrap text-end">
                <span id="current-date" class="me-3 text-light small"></span>
                <span class="me-2 text-light fw-semibold text-truncate d-inline-block" style="max-width: 150px;">
                    <?php echo (isset($_SESSION['usuario'])) ? $_SESSION['usuario'] : ''; ?>
                </span>
                <?php if (isset($_SESSION['rol'])): ?>
                    <span class="me-3 text-light small">| <?php echo ucfirst($_SESSION['rol']); ?></span>
                <?php endif; ?>
                <button class="btn btn-outline-light btn-sm" id="logout-btn">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesion
                </button>
            </div>
        </div>
    </div>
</nav>