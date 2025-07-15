<?php
require_once 'config.php';
require_once 'Producto.php';

$database = new Database();
$db = $database->getConnection();

$producto = new Producto($db);

// Obtener ID del producto desde GET (cuando la página carga)
// O desde POST (cuando se envía el formulario de eliminación)
$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : die('ERROR: ID no encontrado.'));

$producto->id = $id;

// Obtener información del producto antes de eliminarlo
// Esto es importante para mostrar los detalles del producto y para la confirmación
if (!$producto->readOne()) {
    die('ERROR: Producto no encontrado.');
}

$nombre_producto = $producto->nombre;
$tipo_producto = $producto->tipo; // Asumiendo que 'tipo' es una propiedad del objeto Producto

$message = "";
$message_type = "";
$producto_eliminado = false;

// Si se ha enviado el formulario por POST (es decir, se hizo clic en "Confirmar eliminación")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) { // Asegurarse de que el ID esté presente en POST
    if ($producto->delete()) {
        $message = "El {$tipo_producto} '{$nombre_producto}' ha sido eliminado exitosamente.";
        $message_type = "success";
        $producto_eliminado = true;
    } else {
        $message = "Error al eliminar el {$tipo_producto}.";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Producto/Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Inter", sans-serif;
        }

        .card-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        }

        .warning-card {
            border-left: 4px solid #dc3545;
            background: #f8d7da;
        }

        /* Asegúrate de que estos badges estén definidos en tu CSS principal o aquí */
        .badge-producto {
            background-color: #28a745;
            /* Ejemplo de color para producto */
            color: white;
        }

        .badge-servicio {
            background-color: #007bff;
            /* Ejemplo de color para servicio */
            color: white;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-trash me-2"></i>
                            Eliminar <?php echo ucfirst($tipo_producto); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <a href="index.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver al listado
                            </a>
                            <?php if (!$producto_eliminado): ?>
                                <a href="view.php?id=<?php echo htmlspecialchars($producto->id); ?>" class="btn btn-outline-info me-2">
                                    <i class="fas fa-eye me-1"></i>
                                    Ver detalles
                                </a>
                                <a href="edit.php?id=<?php echo htmlspecialchars($producto->id); ?>" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-1"></i>
                                    Editar en su lugar
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!$producto_eliminado): ?>
                            <div class="card warning-card">
                                <div class="card-body">
                                    <h5 class="card-title text-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        ¡Atención! Esta acción no se puede deshacer
                                    </h5>
                                    <p class="card-text">
                                        Está a punto de eliminar permanentemente el siguiente elemento:
                                    </p>

                                    <div class="mt-3 p-3 bg-white rounded">
                                        <h6>
                                            <?php echo htmlspecialchars($producto->nombre); ?>
                                            <span class="badge badge-<?php echo htmlspecialchars($producto->tipo); ?> ms-2">
                                                <?php echo ucfirst(htmlspecialchars($producto->tipo)); ?>
                                            </span>
                                        </h6>

                                        <?php if (!empty($producto->descripcion)): ?>
                                            <p class="text-muted mb-2">
                                                <?php echo htmlspecialchars(substr($producto->descripcion, 0, 100)) . (strlen($producto->descripcion) > 100 ? '...' : ''); ?>
                                            </p>
                                        <?php endif; ?>

                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($producto->fecha_inicio)); ?> -
                                            <?php echo date('d/m/Y', strtotime($producto->fecha_fin)); ?>
                                            (<?php echo htmlspecialchars($producto->diferencia_dias); ?> días)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" class="mt-4">
                                <!-- Campo oculto para enviar el ID del producto con la solicitud POST -->
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto->id); ?>">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-times me-1"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-danger" id="deleteBtn">
                                        <i class="fas fa-trash me-1"></i>
                                        Confirmar eliminación
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center mt-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Elemento eliminado exitosamente</h4>
                                <p class="text-muted">El elemento ha sido removido permanentemente de la base de datos.</p>

                                <div class="mt-4">
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="fas fa-list me-1"></i>
                                        Ver todos los elementos
                                    </a>
                                    <a href="create.php" class="btn btn-outline-primary ms-2">
                                        <i class="fas fa-plus me-1"></i>
                                        Agregar nuevo
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmación adicional antes de eliminar
        document.getElementById('deleteBtn')?.addEventListener('click', function(e) {
            if (!confirm('¿Está completamente seguro de que desea eliminar este elemento? Esta acción NO se puede deshacer.')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>

</html>