<?php
require_once 'config.php';
require_once 'Producto.php';

$database = new Database();
$db = $database->getConnection();

$producto = new Producto($db);
$stmt = $producto->read();
$num = $stmt->rowCount();

// Manejar búsqueda
$search_keywords = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_keywords = $_GET['search'];
    $stmt = $producto->search($search_keywords);
    $num = $stmt->rowCount();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tiempo entre Productos y Servicios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Badges base */
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.875em;
            font-weight: 500;
            border-radius: 0.25rem;
            color: #fff;
        }

        .badge-producto {
            background-color: #28a745;
        }

        .badge-servicio {
            background-color: #007bff;
        }


        /* Estados */
        .badge-estado-verde {
            background-color: #28a745;
            /* Verde - Aún quedan días */
        }

        .badge-estado-naranja {
            background-color: #fd7e14;
            /* Naranja - Por vencer pronto */
        }

        .badge-estado-rojo {
            background-color: #dc3545;
            /* Rojo - Vencido */
        }

        .badge-estado-secondary {
            background-color: #6c757d;
            /* Gris - Sin duración estimada u otros */
        }


        @media (max-width: 576px) {
            table thead {
                display: none;
            }

            table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.5rem;
                padding: 0.75rem;
                background-color: white;
            }

            table tbody tr td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
                border: none;
            }

            table tbody tr td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #6c757d;
            }


            .diff {
                text-align: center;
                justify-content: center;
            }

            .btn-group {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .btn-group .btn, .btn-primary {
                width: 100%;
                margin-bottom: 0.2rem;
            }

            .btn-group .btn:last-child {
                margin-bottom: 0;
            }


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
                            <i class="fas fa-box-open me-2"></i>
                            Gestión de Tiempo entre Productos y Servicios
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Barra de herramientas -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <a href="create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Agregar Nuevo
                                </a>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2"
                                        placeholder="Buscar productos o servicios..."
                                        value="<?php echo htmlspecialchars($search_keywords); ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_keywords)): ?>
                                        <a href="index.php" class="btn btn-outline-secondary ms-2">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <?php if ($num > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th class="d-none d-md-table-cell">Descripción</th>
                                            <th>Fecha Inicio</th>
                                            <th class="d-none d-md-table-cell">Fecha Fin</th>
                                            <th class="d-none d-md-table-cell">Diferencia</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                            $diferencias = calcularDiferenciasDetalladas($row['fecha_inicio'], $row['fecha_fin']);

                                            $fecha_inicio = new DateTime($row['fecha_inicio']);
                                            $fecha_fin = new DateTime($row['fecha_fin']);

                                            $resultado = calcularDuracionUso($fecha_inicio, $fecha_fin);
                                        ?>
                                            <tr>
                                                <td data-label="ID"><?php echo $row['id']; ?></td>
                                                <td data-label="Nombre"><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                                <td data-label="Tipo">
                                                    <span class="badge badge-<?php echo $row['tipo']; ?>">
                                                        <?php echo ucfirst($row['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td data-label="Descripción" class="d-none d-md-table-cell"><?php echo htmlspecialchars(substr($row['descripcion'], 0, 50)) . (strlen($row['descripcion']) > 50 ? '...' : ''); ?></td>
                                                <td data-label="Fecha Inicio"><?php echo date('d/m/Y', strtotime($row['fecha_inicio'])); ?></td>
                                                <td data-label="Fecha Fin" class="d-none d-md-table-cell">
                                                    <?php
                                                    echo (!empty($row['fecha_fin']) && $row['fecha_fin'] !== '0000-00-00')
                                                        ? date('d/m/Y', strtotime($row['fecha_fin']))
                                                        : '';
                                                    ?>
                                                </td>

                                                <td class="diff">
                                                    <small class="text-muted">
                                                        <strong><?php echo htmlspecialchars($diferencias['detalle_completo']); ?></strong><br>
                                                        <?php
                                                        // Determinar el color según el estado
                                                        $estado = $resultado['estado'] ?? '';
                                                        $color = 'badge-estado-secondary'; // default gray

                                                        if (stripos($estado, 'Vencido') !== false) {
                                                            $color = 'badge-estado-rojo'; // rojo
                                                        } elseif (stripos($estado, 'Por vencer') !== false) {
                                                            $color = 'badge-estado-naranja'; // naranja
                                                        } elseif (stripos($estado, 'Aún quedan') !== false) {
                                                            $color = 'badge-estado-verde'; // verde
                                                        }

                                                        echo '<span class="badge ' . $color . '"><em>' . htmlspecialchars($estado) . '</em></span>';
                                                        ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="view.php?id=<?php echo $row['id']; ?>"
                                                            class="btn btn-sm btn-outline-info" title="Ver">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?php echo $row['id']; ?>"
                                                            class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $row['id']; ?>"
                                                            class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                            onclick="return confirm('¿Está seguro de eliminar este elemento?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <?php if (!empty($search_keywords)): ?>
                                    No se encontraron resultados para "<?php echo htmlspecialchars($search_keywords); ?>"
                                <?php else: ?>
                                    No hay productos o servicios registrados. <a href="create.php">Agregar el primero</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>