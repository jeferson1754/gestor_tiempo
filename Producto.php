<?php
require_once 'config.php';

class Producto
{
    private $conn;
    private $table_name = "gestor_tiempos";

    public $id;
    public $nombre;
    public $tipo;
    public $descripcion;
    public $fecha_inicio;
    public $fecha_fin;
    public $diferencia_dias;
    public $diferencia_meses;
    public $created_at;
    public $updated_at;
    public $promedio_dias_mismo_nombre;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Crear nuevo producto
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, tipo=:tipo, descripcion=:descripcion, 
                      fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->fecha_inicio = htmlspecialchars(strip_tags($this->fecha_inicio));
        $this->fecha_fin = htmlspecialchars(strip_tags($this->fecha_fin));

        // Vincular valores
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":fecha_inicio", $this->fecha_inicio);
        $stmt->bindParam(":fecha_fin", $this->fecha_fin);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer todos los productos
    public function read()
    {
        $query = "SELECT 
                p1.id, p1.nombre, p1.tipo, p1.descripcion, p1.fecha_inicio, p1.fecha_fin, 
                created_at, updated_at,
                /* Subconsulta para obtener el promedio de duración por nombre */
                (SELECT AVG(DATEDIFF(p2.fecha_fin, p2.fecha_inicio)) 
                 FROM " . $this->table_name . " p2 
                 WHERE p2.nombre = p1.nombre 
                 AND p2.fecha_fin IS NOT NULL 
                 AND p2.fecha_fin <> '0000-00-00') as promedio_historico
              FROM " . $this->table_name . " p1
              ORDER BY 
                CASE 
                    WHEN p1.fecha_fin IS NULL OR p1.fecha_fin = '0000-00-00' THEN 0 
                    ELSE 1 
                END ASC,
                p1.fecha_inicio DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Leer un producto específico
    public function readOne()
    {
        $query = "SELECT id, nombre, tipo, descripcion, fecha_inicio, fecha_fin,
                         DATEDIFF(fecha_fin, fecha_inicio) as diferencia_dias,
                         TIMESTAMPDIFF(MONTH, fecha_inicio, fecha_fin) as diferencia_meses,
                         created_at, updated_at
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->nombre = $row['nombre'];
            $this->tipo = $row['tipo'];
            $this->descripcion = $row['descripcion'];
            $this->fecha_inicio = $row['fecha_inicio'];
            $this->fecha_fin = $row['fecha_fin'];
            $this->diferencia_dias = $row['diferencia_dias'];
            $this->diferencia_meses = $row['diferencia_meses'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Leer un producto específico con promedio
    // Método para leer un solo registro y calcular el promedio de días para el mismo nombre
    public function readOneDetail()
    {
        // Consulta para obtener los detalles del producto específico
        $query = "SELECT 
            *,
            DATEDIFF(fecha_fin, fecha_inicio) as diferencia_dias,
            TIMESTAMPDIFF(MONTH, fecha_inicio, fecha_fin) as diferencia_meses,
            created_at, updated_at
          FROM " . $this->table_name . "
          WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Asignar los valores del producto a las propiedades del objeto
            $this->nombre = $row['nombre'];
            $this->tipo = $row['tipo'];
            $this->descripcion = $row['descripcion'];
            $this->fecha_inicio = $row['fecha_inicio'];
            $this->fecha_fin = $row['fecha_fin'];
            $this->diferencia_dias = $row['diferencia_dias'];
            $this->diferencia_meses = $row['diferencia_meses'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            // --- Segunda consulta para obtener el promedio de días de productos con el mismo nombre ---
            $avg_query = "SELECT AVG(DATEDIFF(fecha_fin, fecha_inicio)) as promedio_dias
                          FROM " . $this->table_name . "
                          WHERE nombre = ?"; // Usamos 'nombre' para buscar por nombre

            $avg_stmt = $this->conn->prepare($avg_query);
            $avg_stmt->bindParam(1, $this->nombre); // Usamos el nombre del producto actual
            $avg_stmt->execute();
            $avg_row = $avg_stmt->fetch(PDO::FETCH_ASSOC);

            // Asignar el promedio a la nueva propiedad
            if ($avg_row && $avg_row['promedio_dias'] !== null) {
                $this->promedio_dias_mismo_nombre = round($avg_row['promedio_dias'], 2); // Redondear para una mejor visualización
            } else {
                $this->promedio_dias_mismo_nombre = 0; // O un valor predeterminado si no se encuentra un promedio
            }

            return true;
        }
        return false;
    }

    // Actualizar producto
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, tipo = :tipo, descripcion = :descripcion,
                      fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->fecha_inicio = htmlspecialchars(strip_tags($this->fecha_inicio));
        $this->fecha_fin = htmlspecialchars(strip_tags($this->fecha_fin));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular valores
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
        $stmt->bindParam(':fecha_fin', $this->fecha_fin);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar producto
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar productos
    public function search($keywords)
    {
        $query = "SELECT id, nombre, tipo, descripcion, fecha_inicio, fecha_fin,
                         DATEDIFF(fecha_fin, fecha_inicio) as diferencia_dias,
                         TIMESTAMPDIFF(MONTH, fecha_inicio, fecha_fin) as diferencia_meses,
                         created_at, updated_at
                  FROM " . $this->table_name . " 
                  WHERE nombre LIKE ? OR descripcion LIKE ? OR tipo LIKE ?
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        $stmt->execute();
        return $stmt;
    }

    // Calcular diferencias de fechas (método auxiliar)



    // Validar fechas
    public function validarFechas($fecha_inicio, $fecha_fin)
    {
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);

        if ($fin <= $inicio) {
            return false;
        }
        return true;
    }

    public function getEvolutionByName($nombre)
    {
        $query = "SELECT fecha_inicio, 
                     DATEDIFF(fecha_fin, fecha_inicio) as duracion 
              FROM " . $this->table_name . " 
              WHERE nombre = :nombre 
              AND fecha_fin IS NOT NULL 
              AND fecha_fin <> '0000-00-00'
              ORDER BY fecha_inicio ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
function calcularDiferenciasDetalladas($fecha_inicio, $fecha_fin)
{
    try {
        $start_dt = new DateTime($fecha_inicio);

        // Validar fecha_fin: si es null, vacío o '0000-00-00', usar hoy
        if (empty($fecha_fin) || $fecha_fin === '0000-00-00') {
            $end_dt = new DateTime(); // Fecha actual
        } else {
            $end_dt = new DateTime($fecha_fin);
        }

        $interval = $start_dt->diff($end_dt);

        $detalles = [];

        if ($interval->y > 0) {
            $detalles[] = $interval->y . ' año' . ($interval->y > 1 ? 's' : '');
        }
        if ($interval->m > 0) {
            $detalles[] = $interval->m . ' mes' . ($interval->m > 1 ? 'es' : '');
        }
        if ($interval->d > 0) {
            $detalles[] = $interval->d . ' día' . ($interval->d > 1 ? 's' : '');
        }

        $detalle_completo = implode(' ', $detalles);
        if (empty($detalle_completo)) {
            $detalle_completo = '0 días';
        }

        return [
            'dias_totales'     => $interval->days,
            'años'             => $interval->y,
            'meses_restantes'  => $interval->m,
            'dias_restantes'   => $interval->d,
            'detalle_completo' => $detalle_completo
        ];
    } catch (Exception $e) {
        return [
            'dias_totales'     => 0,
            'años'             => 0,
            'meses_restantes'  => 0,
            'dias_restantes'   => 0,
            'detalle_completo' => 'Error: ' . $e->getMessage()
        ];
    }
}



function calcularDuracionUso(DateTime|string $fecha_inicio, DateTime|string|null $fecha_fin = null, ?int $duracion_estimada_dias = null): array
{
    // Convertir fecha_inicio a DateTime si es string
    if (is_string($fecha_inicio)) {
        $fecha_inicio = new DateTime($fecha_inicio);
    }

    // Evaluar fecha_fin: si es null, '0000-00-00' o '', usar fecha actual
    if (empty($fecha_fin) || $fecha_fin === '0000-00-00') {
        $fecha_fin = new DateTime(); // hoy
    } elseif (is_string($fecha_fin)) {
        $fecha_fin = new DateTime($fecha_fin);
    }

    // Calcular la diferencia
    $intervalo = $fecha_inicio->diff($fecha_fin);
    $dias_transcurridos = $intervalo->days;

    $estado = 'Seguimiento sin duración estimada';
    $faltan_dias = null;

    // Si se especificó una duración estimada, calcular cuántos días faltan
    if ($duracion_estimada_dias !== null) {
        $faltan_dias = $duracion_estimada_dias - $dias_transcurridos;

        if ($faltan_dias <= 0) {
            $estado = 'Vencido hace ' . abs($faltan_dias) . ' días';
        } elseif ($faltan_dias <= 3) {
            $estado = 'Por vencer en ' . $faltan_dias . ' días';
        } else {
            $estado = 'Aún quedan ' . $faltan_dias . ' días';
        }
    }

    return [
        'dias_transcurridos' => $dias_transcurridos,
        'duracion_estimada_dias' => $duracion_estimada_dias,
        'faltan_dias' => $faltan_dias,
        'estado' => $estado,
        'detalle' => "{$dias_transcurridos} días desde el inicio",
    ];
}
