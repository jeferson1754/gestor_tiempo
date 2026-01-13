<?php
require_once 'config.php';
require_once 'Producto.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

$nombre = $_GET['nombre'] ?? '';
$datos_evolucion = $producto->getEvolutionByName($nombre);

// Preparar datos para ECharts
$fechas = [];
$duraciones = [];
$promedio_total = 0;

if (count($datos_evolucion) > 0) {
    foreach ($datos_evolucion as $dato) {
        $fechas[] = date('d/m/Y', strtotime($dato['fecha_inicio']));
        $duraciones[] = (int)$dato['duracion'];
    }
    $promedio_total = array_sum($duraciones) / count($duraciones);
}

$datos_evolucion = $producto->getEvolutionByName($nombre);

$fechas = [];
$duraciones = [];
$total_dias = 0;
$max = 0;
$min = 999999;
// Calcular tendencia (comparar último vs penúltimo)
$tendencia = 0;

if (count($datos_evolucion) > 0) {
    foreach ($datos_evolucion as $dato) {
        $d = (int)$dato['duracion'];
        $fechas[] = date('d/m/Y', strtotime($dato['fecha_inicio']));
        $duraciones[] = $d;
        $total_dias += $d;
        if ($d > $max) $max = $d;
        if ($d < $min) $min = $d;
    }
    $total_registros = count($datos_evolucion);
    $promedio_total = round($total_dias / $total_registros, 1);


    if ($total_registros > 1) {
        $ultimo = $duraciones[$total_registros - 1];
        $penultimo = $duraciones[$total_registros - 2];
        $tendencia = $ultimo - $penultimo;
    }
}

// Lógica de colores basada en el promedio
// Para la Máxima: Si es mayor al promedio, es una "alerta" (danger), si no, es normal (success)
$color_max = ($max > $promedio_total) ? 'danger' : 'success';

// Para la Mínima: Si es menor al promedio, es un "logro" (success), si no, es advertencia (warning)
$color_min = ($min < $promedio_total) ? 'success' : 'warning';
$color_tendencia = ($tendencia <= 0) ? 'info' : 'warning';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Análisis ECharts - <?php echo htmlspecialchars($nombre); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        #chart-container {
            width: 100%;
            height: 300px;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-4">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Análisis Histórico: <?php echo htmlspecialchars($nombre); ?></h5>
            <a href="index.php" class="btn btn-sm btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i>
                Volver al Listado</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-2">
                <div class="card border-start border-secondary border-4 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted small uppercase">Promedio Histórico</h6>
                        <h3 class="mb-0"><?php echo $promedio_total; ?> <small class="fs-6 text-muted">días</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-start border-<?php echo $color_max; ?> border-4 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted small uppercase">Máxima Duración</h6>
                        <h3 class="mb-0"><?php echo $max; ?> <small class="fs-6 text-muted">días</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-start border-<?php echo $color_min; ?> border-4 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted small uppercase">Mínima Duración</h6>
                        <h3 class="mb-0"><?php echo $min; ?> <small class="fs-6 text-muted">días</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-start border-<?php echo $color_tendencia; ?> border-4 shadow-sm">

                    <div class="card-body">
                        <h6 class="text-muted small uppercase">Tendencia Actual</h6>
                        <h3 class="mb-0 <?php echo $tendencia <= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($tendencia > 0 ? '+' : '') . $tendencia; ?>
                            <small class="fs-6 text-muted">días vs anterior</small>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div id="chart-container"></div>
            </div>
        </div>
    </div>

    <script>
        var chartDom = document.getElementById('chart-container');
        var myChart = echarts.init(chartDom);

        option = {
            backgroundColor: '#fff',
            tooltip: {
                trigger: 'axis'
            },
            grid: {
                left: '3%',
                right: '10%',
                bottom: '10%',
                containLabel: true
            },
            legend: {
                data: ['Duración Real', 'Línea de Promedio'],
                bottom: 10
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($fechas); ?>,
                axisLine: {
                    lineStyle: {
                        color: '#999'
                    }
                }
            },
            yAxis: {
                type: 'value',
                splitLine: {
                    lineStyle: {
                        type: 'dashed'
                    }
                }
            },
            visualMap: { // Colorea el gráfico según la duración
                show: false,
                dimension: 1,
                pieces: [{
                        gt: 0,
                        lte: <?php echo $promedio_total; ?>,
                        color: '#28a745'
                    }, // Verde si es menor al promedio
                    {
                        gt: <?php echo $promedio_total; ?>,
                        color: '#dc3545'
                    } // Rojo si supera el promedio
                ]
            },
            series: [{
                name: 'Duración Real',
                data: <?php echo json_encode($duraciones); ?>,
                type: 'line',
                smooth: true,
                symbol: 'circle',
                symbolSize: 12,
                lineStyle: {
                    width: 4
                },
                markArea: { // Sombreado de fondo para zona "segura"
                    itemStyle: {
                        color: 'rgba(40, 167, 69, 0.05)'
                    },
                    data: [
                        [{
                            yAxis: 0
                        }, {
                            yAxis: <?php echo $promedio_total; ?>
                        }]
                    ]
                },
                markLine: {
                    silent: true,
                    lineStyle: {
                        color: '#666',
                        type: 'dashed'
                    },
                    data: [{
                        yAxis: <?php echo $promedio_total; ?>,
                        label: {
                            position: 'end',
                            formatter: 'Meta (Promedio)'
                        }
                    }]
                },
                markPoint: {
                    data: [{
                            type: 'max',
                            name: 'Peor caso',
                            itemStyle: {
                                color: '#dc3545'
                            }
                        },
                        {
                            type: 'min',
                            name: 'Mejor caso',
                            itemStyle: {
                                color: '#28a745'
                            }
                        }
                    ]
                }
            }]
        };

        myChart.setOption(option);
        window.onresize = myChart.resize;
    </script>

</body>

</html>