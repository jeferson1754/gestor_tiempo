<?php
require_once 'config.php';
require_once 'Producto.php';

$database = new Database();
$db = $database->getConnection();

$producto = new Producto($db);

// Obtener ID del producto
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

$producto->id = $id;

// Usamos readOneDetail para obtener el producto y el promedio
// ... (código anterior igual)

if (!$producto->readOneDetail()) {
    die('ERROR: Producto no encontrado.');
}

// Las diferencias ya están disponibles en las propiedades del objeto $producto
// $producto->diferencia_dias
// $producto->diferencia_meses
// $producto->promedio_dias_mismo_nombre

// Puedes calcular las diferencias detalladas aquí si lo necesitas,
// pero las propiedades del objeto ya contienen los valores principales.
// Si tenías una función calcularDiferencias() externa, deberías definirla aquí
// o usar las propiedades ya calculadas por readOneDetail().
// Por ejemplo, para un detalle más granular (años, meses, días restantes):
$diferencias = calcularDiferenciasDetalladas($producto->fecha_inicio, $producto->fecha_fin);



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto/Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Inter", sans-serif;
            background-color: #f8f9fa;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .info-card {
            border-left: 4px solid #667eea;
            background: #f8f9fa;
        }

        .badge-producto {
            background-color: #28a745;
            color: white;
        }

        .badge-servicio {
            background-color: #007bff;
            color: white;
        }

        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
        }

        .stat-card h5,
        .stat-card h2,
        .stat-card h3,
        .stat-card p {
            color: white;
            /* Asegura que el texto dentro de stat-card sea blanco */
        }

        .avg-stat-card {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
            /* Nuevo gradiente para el promedio */
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            /* Espacio entre las tarjetas de estadísticas */
        }

        .avg-stat-card h5,
        .avg-stat-card h2,
        .avg-stat-card p {
            color: white;
        }

        .badge-producto {
            background-color: #28a745 !important;
        }

        .badge-servicio {
            background-color: #007bff !important;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Detalles del <?php echo ucfirst(htmlspecialchars($producto->tipo)); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <a href="index.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo htmlspecialchars($producto->id); ?>" class="btn btn-outline-warning me-2">
                                <i class="fas fa-edit me-1"></i>
                                Editar
                            </a>
                            <a href="delete.php?id=<?php echo htmlspecialchars($producto->id); ?>" class="btn btn-outline-danger"
                                onclick="return confirm('¿Está seguro de eliminar este elemento?')">
                                <i class="fas fa-trash me-1"></i>
                                Eliminar
                            </a>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card info-card">
                                    <div class="card-body">
                                        <h4 class="card-title">
                                            <?php echo htmlspecialchars($producto->nombre); ?>
                                            <span class="badge badge-<?php echo htmlspecialchars($producto->tipo); ?> ms-2">
                                                <?php echo ucfirst(htmlspecialchars($producto->tipo)); ?>
                                            </span>
                                        </h4>

                                        <?php if (!empty($producto->descripcion)): ?>
                                            <p class="card-text mt-3">
                                                <strong>Descripción:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($producto->descripcion)); ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-calendar-alt text-primary me-2"></i>Fecha de Inicio:</strong></p>
                                                <p class="ms-4"><?php echo date('d/m/Y', strtotime($producto->fecha_inicio)); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-calendar-check text-success me-2"></i>Fecha de Fin:</strong></p>
                                                <p class="ms-4"><?php echo date('d/m/Y', strtotime($producto->fecha_fin)); ?></p>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-clock text-info me-2"></i>Creado:</strong></p>
                                                <p class="ms-4"><?php echo date('d/m/Y H:i', strtotime($producto->created_at)); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-edit text-warning me-2"></i>Última actualización:</strong></p>
                                                <p class="ms-4"><?php echo date('d/m/Y H:i', strtotime($producto->updated_at)); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card stat-card">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">
                                            <i class="fas fa-calculator me-2"></i>
                                            Calculo <?php echo (empty($producto->fecha_fin) || $producto->fecha_fin == '0000-00-00') ? 'Tiempo Transcurrido' : 'Duración Total'; ?>
                                        </h5>
                                        <div class="mt-4">
                                            <div class="mb-3">
                                                <h2 class="display-5"> <?php echo htmlspecialchars($diferencias['detalle_completo']); ?></h2>

                                            </div>


                                            <?php if ($diferencias['años'] > 0): ?>
                                                <div class="mb-3">
                                                    <h3><?php echo htmlspecialchars($diferencias['años']); ?></h3>
                                                    <p class="mb-0">Años</p>
                                                </div>
                                            <?php endif; ?>



                                        </div>

                                    </div>
                                </div>




                            </div>
                            <!-- Nueva tarjeta para el promedio de días -->
                            <div class="card avg-stat-card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Promedio de Duración (<?php echo htmlspecialchars($producto->nombre); ?>)
                                    </h5>
                                    <div class="mt-4">
                                        <h2 class="display-4"><?php echo htmlspecialchars($producto->promedio_dias_mismo_nombre); ?></h2>
                                        <p class="mb-0">Días en promedio</p>
                                    </div>
                                    <p class="small mt-2">
                                        Este promedio incluye todos los registros con el nombre "<?php echo htmlspecialchars($producto->nombre); ?>".
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>