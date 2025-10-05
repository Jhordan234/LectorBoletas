<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

// Total de TODOS los gastos (sin filtro de mes)
$sql_total = "SELECT SUM(importe_total) AS total_gastos FROM gastos";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->execute();
$total_gastos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_gastos'] ?? 0;

// Total del mes anterior para comparación
$sql_prev = "SELECT SUM(importe_total) AS total_gastos_prev
             FROM gastos
             WHERE EXTRACT(MONTH FROM fecha) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')
             AND EXTRACT(YEAR FROM fecha) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month')";
$stmt_prev = $conn->prepare($sql_prev);
$stmt_prev->execute();
$total_gastos_prev = $stmt_prev->fetch(PDO::FETCH_ASSOC)['total_gastos_prev'] ?? 0;
$porcentaje_cambio = $total_gastos_prev > 0 ? (($total_gastos - $total_gastos_prev) / $total_gastos_prev) * 100 : 0;

// Estadísticas de gastos por tipo_comprobante (TODOS los registros)
$sql_estadisticas = "SELECT tipo_comprobante, COUNT(*) AS cantidad
                     FROM gastos
                     GROUP BY tipo_comprobante
                     ORDER BY cantidad DESC LIMIT 3";
$stmt_estadisticas = $conn->prepare($sql_estadisticas);
$stmt_estadisticas->execute();
$estadisticas = $stmt_estadisticas->fetchAll(PDO::FETCH_ASSOC);
$total_gastos_mes = array_sum(array_column($estadisticas, 'cantidad'));
$estadisticas = array_map(function($item) use ($total_gastos_mes) {
    $item['porcentaje'] = $total_gastos_mes > 0 ? ($item['cantidad'] / $total_gastos_mes) * 100 : 0;
    return $item;
}, $estadisticas);

// Datos para el gráfico principal (por mes del año actual)
$sql_chart = "SELECT TO_CHAR(fecha, 'Mon') AS mes, SUM(importe_total) AS total
              FROM gastos
              WHERE EXTRACT(YEAR FROM fecha) = EXTRACT(YEAR FROM CURRENT_DATE)
              GROUP BY TO_CHAR(fecha, 'Mon'), EXTRACT(MONTH FROM fecha)
              ORDER BY EXTRACT(MONTH FROM fecha)";
$stmt_chart = $conn->prepare($sql_chart);
$stmt_chart->execute();
$gastos_por_mes = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);
$labels = [];
$data = [];
foreach ($gastos_por_mes as $row) {
    $labels[] = $row['mes'];
    $data[] = (float)$row['total'];
}
$labels_json = json_encode($labels);
$data_json = json_encode($data);

// Datos para el mini gráfico
$sql_mini_chart = "SELECT TO_CHAR(fecha, 'IW') AS semana, SUM(importe_total) AS total
                   FROM gastos
                   WHERE EXTRACT(MONTH FROM fecha) = EXTRACT(MONTH FROM CURRENT_DATE)
                   GROUP BY TO_CHAR(fecha, 'IW'), EXTRACT(WEEK FROM fecha)
                   ORDER BY EXTRACT(WEEK FROM fecha)";
$stmt_mini_chart = $conn->prepare($sql_mini_chart);
$stmt_mini_chart->execute();
$gastos_por_semana = $stmt_mini_chart->fetchAll(PDO::FETCH_ASSOC);
$mini_labels = [];
$mini_data = [];
foreach ($gastos_por_semana as $row) {
    $mini_labels[] = "Sem " . $row['semana'];
    $mini_data[] = (float)$row['total'];
}
$mini_labels_json = json_encode($mini_labels);
$mini_data_json = json_encode($mini_data);

// Gastos por categoría
$sql_categorias = "SELECT tipo_comprobante, COUNT(*) AS cantidad, SUM(importe_total) AS total
                   FROM gastos
                   WHERE EXTRACT(MONTH FROM fecha) = EXTRACT(MONTH FROM CURRENT_DATE)
                   GROUP BY tipo_comprobante";
$stmt_categorias = $conn->prepare($sql_categorias);
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Actividad reciente
$sql_actividad = "SELECT tipo_comprobante, fecha
                  FROM gastos
                  ORDER BY fecha DESC LIMIT 3";
$stmt_actividad = $conn->prepare($sql_actividad);
$stmt_actividad->execute();
$actividad_reciente = $stmt_actividad->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART-EXPENSE Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 135, 135, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 90%, rgba(168, 239, 255, 0.3) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        .card-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        .card-hover:hover::before {
            left: 100%;
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
            border-color: rgba(139, 92, 246, 0.3);
        }
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .glow-text {
            text-shadow: 0 0 20px rgba(251, 191, 36, 0.5),
                         0 0 40px rgba(251, 191, 36, 0.3);
        }
        .neon-border {
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.5),
                        0 0 20px rgba(139, 92, 246, 0.3),
                        inset 0 0 10px rgba(139, 92, 246, 0.2);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            backdrop-filter: blur(10px);
        }
        .modal-content {
            background: linear-gradient(135deg, rgba(15, 12, 41, 0.95) 0%, rgba(48, 43, 99, 0.95) 100%);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(139, 92, 246, 0.3);
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 80%;
            max-width: 600px;
            position: relative;
            box-shadow: 0 0 40px rgba(139, 92, 246, 0.4);
        }
        .close-modal {
            color: #fff;
            float: right;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 15px;
            transition: all 0.3s;
        }
        .close-modal:hover {
            color: #fbbf24;
            transform: rotate(90deg);
        }
        #modalImage {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .7; }
        }
        .stat-icon {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(59, 130, 246, 0.2) 100%);
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }
        nav {
            position: relative;
            z-index: 100;
        }
        .container-wrapper {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="min-h-screen text-white">
    <!-- Header -->
    <nav class="glass border-b border-white/10 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="text-2xl font-bold text-white glow-text">
                    <i class="fas fa-chart-line mr-2 text-yellow-400"></i>
                    SMART-EXPENSE
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <a href="sesion.php" class="flex items-center space-x-2 hover:text-yellow-400 transition">
                    <i class="fas fa-plus"></i>
                    <span>Registrar Boleta</span>
                </a>
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center neon-border">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></span>
                </div>
                <a href="logout.php" class="hover:text-red-400 transition">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard -->
    <div class="max-w-7xl mx-auto p-6 space-y-6 container-wrapper">
        
        <!-- Top Row - Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 fade-in">
            <!-- Total Revenue Card -->
            <div class="glass rounded-2xl p-6 card-hover neon-border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-300 text-sm font-medium">Ingresos Totales</h3>
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up text-green-400 text-xl"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white mb-2 glow-text">S/<?php echo number_format($total_gastos, 2); ?></div>
                <div class="flex items-center text-sm">
                    <span class="<?php echo $porcentaje_cambio >= 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?> px-3 py-1 rounded-full text-xs font-semibold"><?php echo number_format($porcentaje_cambio, 1); ?>%</span>
                    <span class="text-gray-400 ml-2">vs mes anterior</span>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Ganancia Neta: S/<?php echo number_format($total_gastos * 0.37, 2); ?></span>
                        <span>Ingreso Neto: S/<?php echo number_format($total_gastos * 0.56, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Orders Stats -->
            <div class="glass rounded-2xl p-6 card-hover neon-border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-300 text-sm font-medium">Estadísticas de Gastos</h3>
                    <div class="stat-icon">
                        <i class="fas fa-chart-pie text-blue-400 text-xl"></i>
                    </div>
                </div>
                <div class="space-y-3">
                    <?php foreach ($estadisticas as $index => $stat): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 <?php echo $index === 0 ? 'bg-blue-500' : ($index === 1 ? 'bg-green-500' : 'bg-purple-500'); ?> rounded-full pulse"></div>
                            <span class="text-sm text-gray-300"><?php echo htmlspecialchars($stat['tipo_comprobante']); ?></span>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-semibold"><?php echo number_format($stat['cantidad']); ?></div>
                            <div class="text-blue-400 text-xs"><?php echo number_format($stat['porcentaje'], 1); ?>%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-2xl p-6 card-hover neon-border">
                <h3 class="text-gray-300 text-sm font-medium mb-4">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <button onclick="window.location.href='sesion.php'" class="w-full bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-black font-medium py-2 px-4 rounded-lg transition flex items-center justify-center space-x-2 shadow-lg">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Gasto</span>
                    </button>
                    <button onclick="exportData()" class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center space-x-2 shadow-lg">
                        <i class="fas fa-download"></i>
                        <span>Exportar</span>
                    </button>
                    <button onclick="generateReport()" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center space-x-2 shadow-lg">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="glass rounded-2xl p-6 card-hover neon-border">
                <h3 class="text-gray-300 text-sm font-medium mb-4">Actividad Reciente</h3>
                <div class="space-y-3">
                    <?php foreach ($actividad_reciente as $index => $actividad): ?>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 <?php echo $index === 0 ? 'bg-gradient-to-r from-green-500 to-emerald-500' : ($index === 1 ? 'bg-gradient-to-r from-blue-500 to-cyan-500' : 'bg-gradient-to-r from-purple-500 to-pink-500'); ?> rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-receipt text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white font-medium"><?php echo htmlspecialchars($actividad['tipo_comprobante']); ?> registrado</p>
                            <p class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($actividad['fecha'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Chart Section -->
            <div class="lg:col-span-2 glass rounded-2xl p-6 card-hover fade-in neon-border">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white glow-text">Resumen de Gastos</h3>
                    <div class="flex items-center space-x-4">
                        <select id="chartPeriod" class="bg-gray-800/50 border border-purple-500/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option>Anual</option>
                            <option selected>Mensual</option>
                            <option>Semanal</option>
                        </select>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="expensesChart"></canvas>
                </div>
            </div>

            <!-- Social Media Stats / Categories -->
            <div class="glass rounded-2xl p-6 card-hover fade-in neon-border">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white glow-text">Gastos por Categoría</h3>
                    <select class="bg-gray-800/50 border border-purple-500/30 rounded-lg px-3 py-2 text-white text-sm">
                        <option selected>Mensual</option>
                        <option>Semanal</option>
                        <option>Anual</option>
                    </select>
                </div>
                <div class="space-y-4">
                    <?php foreach ($categorias as $categoria): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-800/30 rounded-lg border border-purple-500/20 hover:border-purple-500/40 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-receipt text-white"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium"><?php echo htmlspecialchars($categoria['tipo_comprobante']); ?></p>
                                <p class="text-gray-400 text-sm"><?php echo number_format($categoria['cantidad']); ?> gastos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">S/<?php echo number_format($categoria['total'], 2); ?></p>
                            <p class="text-green-400 text-sm">+0%</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Third Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Product Tracking / Recent Expenses -->
            <div class="glass rounded-2xl p-6 card-hover fade-in neon-border">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white glow-text">Seguimiento de Gastos</h3>
                    <button class="text-purple-400 hover:text-purple-300 text-sm">Ver Todo</button>
                </div>
                <div class="space-y-4">
                    <?php
                    $sql_seguimiento = "SELECT tipo_comprobante, fecha FROM gastos ORDER BY fecha DESC LIMIT 4";
                    $stmt_seguimiento = $conn->prepare($sql_seguimiento);
                    $stmt_seguimiento->execute();
                    $seguimiento = $stmt_seguimiento->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($seguimiento as $index => $gasto):
                        $icono = $index === 0 ? 'fa-receipt' : ($index === 1 ? 'fa-file-invoice' : ($index === 2 ? 'fa-money-bill' : 'fa-clock'));
                        $color = $index === 0 ? 'bg-gradient-to-r from-purple-600 to-pink-600' : ($index === 1 ? 'bg-gradient-to-r from-blue-500 to-cyan-500' : ($index === 2 ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-red-500 to-orange-500'));
                    ?>
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 <?php echo $color; ?> rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas <?php echo $icono; ?> text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white font-medium"><?php echo htmlspecialchars($gasto['tipo_comprobante']); ?></p>
                                <span class="text-gray-400 text-sm"><?php echo date('M d', strtotime($gasto['fecha'])); ?></span>
                            </div>
                            <p class="text-gray-400 text-sm">Registrado • <?php echo date('H:i', strtotime($gasto['fecha'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 h-24">
                    <canvas id="miniChart"></canvas>
                </div>
            </div>

            <!-- Best Selling Products / Top Expenses -->
            <div class="glass rounded-2xl p-6 card-hover fade-in neon-border">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white glow-text">Gastos Principales</h3>
                    <button class="text-purple-400 hover:text-purple-300">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <?php
                    $sql_top_gastos = "SELECT tipo_comprobante, SUM(importe_total) AS total
                                       FROM gastos
                                       GROUP BY tipo_comprobante
                                       ORDER BY total DESC LIMIT 2";
                    $stmt_top_gastos = $conn->prepare($sql_top_gastos);
                    $stmt_top_gastos->execute();
                    $top_gastos = $stmt_top_gastos->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($top_gastos as $index => $gasto):
                        $color = $index === 0 ? 'from-cyan-500 to-blue-600' : 'from-purple-500 to-pink-600';
                        $icono = $index === 0 ? 'fa-receipt' : 'fa-file-invoice-dollar';
                    ?>
                    <div class="relative group">
                        <div class="bg-gradient-to-br <?php echo $color; ?> rounded-2xl p-4 h-48 flex flex-col justify-between overflow-hidden shadow-xl">
                            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-semibold">
                                -15%
                            </div>
                            <div class="flex-1 flex items-center justify-center">
                                <i class="fas <?php echo $icono; ?> text-white text-4xl opacity-20"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1"><?php echo htmlspecialchars($gasto['tipo_comprobante']); ?></h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-200 line-through text-xs">S/<?php echo number_format($gasto['total'] * 1.15, 2); ?></span>
                                    <span class="text-white font-bold">S/<?php echo number_format($gasto['total'], 2); ?></span>
                                </div>
                                <div class="flex text-yellow-400 text-xs mt-1">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="<?php echo $index === 0 ? 'fas fa-star' : 'far fa-star'; ?>"></i>
                                </div>
                                <button class="w-full bg-white/20 hover:bg-white/30 text-white text-xs py-2 rounded-lg mt-2 transition font-semibold">
                                    <i class="fas fa-shopping-cart mr-1"></i> Ver Ahora
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="glass rounded-2xl p-6 card-hover fade-in neon-border">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white glow-text">Lista de Gastos Recientes</h3>
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Buscar gastos..." class="bg-gray-800/50 border border-purple-500/30 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <button onclick="exportData()" class="bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-black px-4 py-2 rounded-lg transition font-semibold shadow-lg">
                        <i class="fas fa-download mr-2"></i>Exportar
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-purple-500/30">
                            <th class="text-left text-gray-400 font-medium py-3 px-4">ID</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Tipo</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Serie</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Cliente</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Fecha</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Moneda</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Subtotal</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">IGV</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Total</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Imagen</th>
                            <th class="text-left text-gray-400 font-medium py-3 px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-500/20" id="expensesTableBody">
                        <?php
                        $sql = "SELECT id, tipo_comprobante, serie_numero, nombre_cliente, fecha, moneda, subtotal, igv, importe_total, imagen_ruta 
                                FROM gastos ORDER BY fecha DESC LIMIT 20";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($gastos) > 0):
                            foreach ($gastos as $row):
                        ?>
                        <tr class="hover:bg-purple-500/10 transition">
                            <td class="py-3 px-4 text-white">#<?php echo $row['id']; ?></td>
                            <td class="py-3 px-4">
                                <span class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-3 py-1 rounded-full text-xs font-semibold"><?php echo htmlspecialchars($row['tipo_comprobante']); ?></span>
                            </td>
                            <td class="py-3 px-4 text-gray-300"><?php echo htmlspecialchars($row['serie_numero']); ?></td>
                            <td class="py-3 px-4 text-white font-medium"><?php echo htmlspecialchars($row['nombre_cliente']); ?></td>
                            <td class="py-3 px-4 text-gray-300"><?php echo $row['fecha']; ?></td>
                            <td class="py-3 px-4 text-gray-300"><?php echo htmlspecialchars($row['moneda']); ?></td>
                            <td class="py-3 px-4 text-white">S/<?php echo number_format($row['subtotal'], 2); ?></td>
                            <td class="py-3 px-4 text-white">S/<?php echo number_format($row['igv'], 2); ?></td>
                            <td class="py-3 px-4 text-white font-semibold">S/<?php echo number_format($row['importe_total'], 2); ?></td>
                            <td class="py-3 px-4">
                                <button onclick="showImage('<?php echo htmlspecialchars($row['imagen_ruta']); ?>')" class="text-yellow-400 hover:text-yellow-300 transition">
                                    <i class="fas fa-image"></i> Ver
                                </button>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewExpense(<?php echo $row['id']; ?>)" class="text-yellow-400 hover:text-yellow-300 transition" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editExpense(<?php echo $row['id']; ?>)" class="text-blue-400 hover:text-blue-300 transition" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteExpense(<?php echo $row['id']; ?>)" class="text-red-400 hover:text-red-300 transition" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="11" class="text-center text-gray-400 py-4">No hay gastos registrados</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar imágenes -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeImageModal()">&times;</span>
            <div class="text-center">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4 glow-text">Vista Previa del Comprobante</h3>
                <img id="modalImage" src="" alt="Comprobante" class="max-w-full h-auto">
            </div>
        </div>
    </div>

    <!-- Modal para detalles -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeDetailModal()">&times;</span>
            <h3 id="detailTitle" class="text-lg font-semibold text-yellow-400 mb-4 glow-text">Detalles del Gasto</h3>
            <div id="detailValues" class="text-white"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Data for charts
        const expensesData = {
            labels: <?php echo $labels_json; ?>,
            datasets: [{
                label: 'Gastos 2025',
                data: <?php echo $data_json; ?>,
                backgroundColor: [
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(244, 63, 94, 0.8)'
                ],
                borderColor: [
                    'rgb(139, 92, 246)',
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(251, 191, 36)',
                    'rgb(239, 68, 68)',
                    'rgb(236, 72, 153)',
                    'rgb(20, 184, 166)',
                    'rgb(168, 85, 247)',
                    'rgb(249, 115, 22)',
                    'rgb(34, 197, 94)',
                    'rgb(99, 102, 241)',
                    'rgb(244, 63, 94)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        };

        const miniChartData = {
            labels: <?php echo $mini_labels_json; ?>,
            datasets: [{
                data: <?php echo $mini_data_json; ?>,
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        };

        // Main Chart Configuration (BARRAS)
        const ctx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(ctx, {
            type: 'bar',
            data: expensesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 12, 41, 0.95)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgb(139, 92, 246)',
                        borderWidth: 2,
                        cornerRadius: 10,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return 'Total: S/' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(139, 92, 246, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return 'S/' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Mini Chart Configuration
        const miniCtx = document.getElementById('miniChart').getContext('2d');
        const miniChart = new Chart(miniCtx, {
            type: 'line',
            data: miniChartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                scales: {
                    y: {
                        display: false
                    },
                    x: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });

        // Actualizar gráfico según período
        document.getElementById('chartPeriod').addEventListener('change', function(e) {
            const period = e.target.value.toLowerCase();
            fetch(`acciones_gasto.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=chart_data&period=${period}`
            })
            .then(res => res.json())
            .then(data => {
                expensesChart.data.labels = data.labels;
                expensesChart.data.datasets[0].data = data.data;
                expensesChart.update();
                showNotification('Gráfico actualizado', 'success');
            })
            .catch(error => {
                showNotification('Error al actualizar el gráfico', 'error');
            });
        });

        // Modal Functions
        function showImage(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImage.src = imageSrc;
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const imageModal = document.getElementById('imageModal');
            const detailModal = document.getElementById('detailModal');
            if (event.target === imageModal) {
                closeImageModal();
            }
            if (event.target === detailModal) {
                closeDetailModal();
            }
        }

        // View Expense
        function viewExpense(id) {
            fetch("acciones_gasto.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: `action=view&id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    showNotification(data.error, 'error');
                } else {
                    const modal = document.getElementById('detailModal');
                    const detailValues = document.getElementById('detailValues');
                    detailValues.innerHTML = `
                        <div class="space-y-3">
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">ID:</strong> <span>${data.id}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">Tipo:</strong> <span>${data.tipo_comprobante}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">Serie:</strong> <span>${data.serie_numero}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">Cliente:</strong> <span>${data.nombre_cliente}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">Fecha:</strong> <span>${data.fecha}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">Moneda:</strong> <span>${data.moneda}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">Subtotal:</strong> <span class="text-green-400">S/${parseFloat(data.subtotal).toFixed(2)}</span></p>
                            <p class="flex justify-between border-b border-purple-500/20 pb-2"><strong class="text-purple-400">IGV:</strong> <span class="text-yellow-400">S/${parseFloat(data.igv).toFixed(2)}</span></p>
                            <p class="flex justify-between"><strong class="text-purple-400">Total:</strong> <span class="text-xl font-bold text-green-400">S/${parseFloat(data.importe_total).toFixed(2)}</span></p>
                        </div>
                    `;
                    modal.style.display = 'block';
                }
            })
            .catch(error => {
                showNotification('Error al cargar los detalles', 'error');
            });
        }

        // Delete Expense
        function deleteExpense(id) {
            if (confirm("¿Seguro que quieres eliminar este gasto?")) {
                fetch("acciones_gasto.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: `action=delete&id=${id}`
                })
                .then(res => res.text())
                .then(data => {
                    if (data === "ok") {
                        showNotification("Gasto eliminado correctamente", "success");
                        location.reload();
                    } else {
                        showNotification("Error al eliminar: " + data, "error");
                    }
                });
            }
        }

        // Edit Expense
        function editExpense(id) {
            let nombre = prompt("Nuevo nombre del cliente:");
            let total = prompt("Nuevo importe total:");
            if (nombre && total) {
                fetch("acciones_gasto.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: `action=edit&id=${id}&nombre_cliente=${encodeURIComponent(nombre)}&importe_total=${total}`
                })
                .then(res => res.text())
                .then(data => {
                    if (data === "ok") {
                        showNotification("Gasto actualizado correctamente", "success");
                        location.reload();
                    } else {
                        showNotification("Error al actualizar: " + data, "error");
                    }
                });
            }
        }

        // Export function
        function exportData() {
            fetch('acciones_gasto.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=export'
            })
            .then(res => res.text())
            .then(data => {
                const link = document.createElement('a');
                link.href = 'data:text/csv;charset=utf-8,' + encodeURI(data);
                link.download = 'gastos_reporte.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                showNotification('Datos exportados correctamente', 'success');
            })
            .catch(error => {
                showNotification('Error al exportar datos', 'error');
            });
        }

        // Generate Report function
        function generateReport() {
            showNotification('Generando reporte...', 'info');
            setTimeout(() => {
                showNotification('Reporte generado exitosamente', 'success');
            }, 2000);
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg glass fade-in max-w-sm border-2`;
            const colors = {
                success: 'border-green-500 text-green-400',
                error: 'border-red-500 text-red-400',
                info: 'border-blue-500 text-blue-400',
                warning: 'border-yellow-500 text-yellow-400'
            };
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-times-circle',
                info: 'fas fa-info-circle',
                warning: 'fas fa-exclamation-triangle'
            };
            notification.innerHTML = `
                <div class="flex items-center space-x-3 ${colors[type]}">
                    <i class="${icons[type]} text-xl"></i>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add hover effects to cards
        document.querySelectorAll('.card-hover').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Search functionality
        const searchInput = document.querySelector('input[placeholder="Buscar gastos..."]');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const tableRows = document.querySelectorAll('#expensesTableBody tr');
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Animate numbers on load
        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.innerHTML = 'S/' + value.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Initialize animations when page loads
        window.addEventListener('load', function() {
            const revenueElement = document.querySelector('.text-3xl.font-bold.text-white.mb-2');
            if (revenueElement) {
                const totalValue = <?php echo (int)$total_gastos; ?>;
                animateValue(revenueElement, 0, totalValue, 2000);
            }
            console.log('Dashboard cargado exitosamente!');
            showNotification('Dashboard cargado correctamente', 'success');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'sesion.php';
            }
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportData();
            }
            if (e.key === 'Escape') {
                closeImageModal();
                closeDetailModal();
            }
        });
    </script>
</body>
</html>