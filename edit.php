<?php
require_once 'config.php';
require_once 'Producto.php';

$database = new Database();
$db = $database->getConnection();

$producto = new Producto($db);

$message = "";
$message_type = "";

// Obtener ID del producto
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

$producto->id = $id;

if(!$producto->readOne()) {
    die('ERROR: Producto no encontrado.');
}

if($_POST) {
    $producto->nombre = $_POST['nombre'];
    $producto->tipo = $_POST['tipo'];
    $producto->descripcion = $_POST['descripcion'];
    $producto->fecha_inicio = $_POST['fecha_inicio'];
    $producto->fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if(!$producto->validarFechas($producto->fecha_inicio, $producto->fecha_fin)) {
        $message = "Error: La fecha de fin debe ser posterior a la fecha de inicio.";
        $message_type = "danger";
    } else {
        if($producto->update()) {
            $message = "Producto/Servicio actualizado exitosamente.";
            $message_type = "success";
        } else {
            $message = "Error al actualizar el producto/servicio.";
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto/Servicio</title>
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
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                            <i class="fas fa-edit me-2"></i>
                            Editar Producto/Servicio
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <a href="index.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver al listado
                            </a>
                            <a href="view.php?id=<?php echo $producto->id; ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>
                                Ver detalles
                            </a>
                        </div>

                        <?php if(!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="productForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">
                                            <i class="fas fa-tag me-1"></i>
                                            Nombre *
                                        </label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo htmlspecialchars($producto->nombre); ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label">
                                            <i class="fas fa-list me-1"></i>
                                            Tipo *
                                        </label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="">Seleccionar tipo</option>
                                            <option value="producto" <?php echo ($producto->tipo == 'producto') ? 'selected' : ''; ?>>
                                                Producto
                                            </option>
                                            <option value="servicio" <?php echo ($producto->tipo == 'servicio') ? 'selected' : ''; ?>>
                                                Servicio
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    Descripción
                                </label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                          placeholder="Descripción detallada del producto o servicio"><?php echo htmlspecialchars($producto->descripcion); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_inicio" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Fecha de Inicio *
                                        </label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                               value="<?php echo $producto->fecha_inicio; ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_fin" class="form-label">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Fecha de Fin *
                                        </label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                               value="<?php echo $producto->fecha_fin; ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div id="diferencia-preview" class="alert alert-info">
                                <i class="fas fa-clock me-2"></i>
                                <span id="diferencia-text">
                                    <strong>Diferencia actual:</strong> <?php echo $producto->diferencia_dias; ?> días 
                                    (<?php echo $producto->diferencia_meses; ?> meses)
                                </span>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-undo me-1"></i>
                                    Restaurar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Actualizar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calcular diferencia de fechas en tiempo real
        function calcularDiferencia() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            if (fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);
                
                if (fin > inicio) {
                    const diferenciaTiempo = fin.getTime() - inicio.getTime();
                    const diferenciaDias = Math.ceil(diferenciaTiempo / (1000 * 3600 * 24));
                    
                    const diffYears = fin.getFullYear() - inicio.getFullYear();
                    const diffMonths = fin.getMonth() - inicio.getMonth();
                    const diffDays = fin.getDate() - inicio.getDate();

                    let years = diffYears;
                    let months = diffMonths;
                    let days = diffDays;

                    if (days < 0) {
                        months--;
                        days += new Date(fin.getFullYear(), fin.getMonth(), 0).getDate();
                    }
                    if (months < 0) {
                        years--;
                        months += 12;
                    }

                    let diffString = "";
                    if (years > 0) diffString += `${years} año${years > 1 ? "s" : ""}, `;
                    if (months > 0) diffString += `${months} mes${months > 1 ? "es" : ""}, `;
                    if (days > 0) diffString += `${days} día${days > 1 ? "s" : ""}`;

                    if (diffString.endsWith(", ")) {
                        diffString = diffString.slice(0, -2);
                    }

                    document.getElementById("diferencia-text").innerHTML = 
                        `<strong>Nueva diferencia:</strong> ${diffString || "0 días"}`;
                } else {
                    document.getElementById("diferencia-text").innerHTML = 
                        "<strong>Error:</strong> La fecha de fin debe ser posterior a la fecha de inicio";
                }
            }
        }

        document.getElementById('fecha_inicio').addEventListener('change', calcularDiferencia);
        document.getElementById('fecha_fin').addEventListener('change', calcularDiferencia);

        // Validación del formulario
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaFin = new Date(document.getElementById('fecha_fin').value);
            
            if (fechaFin <= fechaInicio) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio.');
                return false;
            }
        });
    </script>
</body>
</html>

