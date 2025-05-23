<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="ventas-container">
        <h2>Gestion de Ventas</h2>
        <form action="../controllers/ventasController.php" method="POST">
            <label for="cliente">Cliente:</label>
            <input type="text" id="cliente" name="cliente" required>
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required>
            <label for="producto">Producto:</label>
            <input type="text" id="producto" name="producto" required>
            <label for="cantidad">Cantidad:</label>
            <input type="number" id="cantidad" name="cantidad" required>
            <button type="submit">Registrar Venta</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aqui se llenaran las filas dinámicamente -->
            </tbody>
        </table>
    </div>
</body>
</html>