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
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .progress-ring {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .metric-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1);
            margin: 5% auto;
            padding: 20px;
            border-radius: 15px;
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        .close-modal {
            color: #fff;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        .close-modal:hover {
            color: #fbbf24;
        }
        #modalImage {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 10px;
        }
    </style>
</head>
<body class="min-h-screen text-white">
    <!-- Header -->
    <nav class="glass border-b border-white/10 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="text-2xl font-bold text-white">
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
                    <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-black text-sm"></i>
                    </div>
                    <span class="text-sm">Usuario</span>
                </div>
                <a href="logout.php" class="hover:text-yellow-400 transition">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard -->
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        
        <!-- Top Row - Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 fade-in">
            <!-- Total Revenue Card -->
            <div class="glass rounded-2xl p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-300 text-sm font-medium">Ingresos Totales</h3>
                    <i class="fas fa-arrow-up text-green-400"></i>
                </div>
                <div class="text-3xl font-bold text-white mb-2">S/9,542.00</div>
                <div class="flex items-center text-sm">
                    <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded-full text-xs">-5.1%</span>
                    <span class="text-gray-400 ml-2">vs mes anterior</span>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Ganancia Neta: S/3,526.56</span>
                        <span>Ingreso Neto: S/5,324.85</span>
                    </div>
                </div>
            </div>

            <!-- Orders Stats -->
            <div class="glass rounded-2xl p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-300 text-sm font-medium">Estadísticas de Gastos</h3>
                    <i class="fas fa-chart-pie text-blue-400"></i>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm text-gray-300">Completados</span>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-semibold">56,236</div>
                            <div class="text-blue-400 text-xs">90.2%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-300">En Proceso</span>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-semibold">12,596</div>
                            <div class="text-green-400 text-xs">6.7%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-sm text-gray-300">Cancelados</span>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-semibold">1,568</div>
                            <div class="text-red-400 text-xs">3.0%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-2xl p-6 card-hover">
                <h3 class="text-gray-300 text-sm font-medium mb-4">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <button onclick="window.location.href='sesion.php'" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-medium py-2 px-4 rounded-lg transition flex items-center justify-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Gasto</span>
                    </button>
                    <button onclick="exportData()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Exportar</span>
                    </button>
                    <button onclick="generateReport()" class="w-full bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center space-x-2">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="glass rounded-2xl p-6 card-hover">
                <h3 class="text-gray-300 text-sm font-medium mb-4">Actividad Reciente</h3>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white">Gasto registrado</p>
                            <p class="text-xs text-gray-400">hace 2 horas</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-invoice text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white">Boleta procesada</p>
                            <p class="text-xs text-gray-400">hace 4 horas</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white">Reporte generado</p>
                            <p class="text-xs text-gray-400">ayer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Chart Section -->
            <div class="lg:col-span-2 glass rounded-2xl p-6 card-hover fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">Resumen de Gastos</h3>
                    <div class="flex items-center space-x-4">
                        <select class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                            <option>Anual</option>
                            <option>Mensual</option>
                            <option>Semanal</option>
                        </select>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="expensesChart"></canvas>
                </div>
            </div>

            <!-- Social Media Stats / Categories -->
            <div class="glass rounded-2xl p-6 card-hover fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">Gastos por Categoría</h3>
                    <select class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm">
                        <option>Mensual</option>
                        <option>Semanal</option>
                        <option>Anual</option>
                    </select>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-white"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Boletas</p>
                                <p class="text-gray-400 text-sm">4.2k gastos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">4,562</p>
                            <p class="text-green-400 text-sm">+20%</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-cyan-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice text-white"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Facturas</p>
                                <p class="text-gray-400 text-sm">3.7k gastos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">1,652</p>
                            <p class="text-green-400 text-sm">+5%</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-check text-white"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Recibos</p>
                                <p class="text-gray-400 text-sm">3.3k gastos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">5,256</p>
                            <p class="text-red-400 text-sm">-8%</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-white"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Notas de Crédito</p>
                                <p class="text-gray-400 text-sm">3.7k gastos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">6,965</p>
                            <p class="text-green-400 text-sm">+15%</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-ellipsis-h text-white"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium">Otros</p>
                                <p class="text-gray-400 text-sm">4.2k gastos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">8,532</p>
                            <p class="text-green-400 text-sm">+25%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Product Tracking / Recent Expenses -->
            <div class="glass rounded-2xl p-6 card-hover fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">Seguimiento de Gastos</h3>
                    <button class="text-purple-400 hover:text-purple-300 text-sm">Ver Todo</button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white font-medium">5 gastos pendientes</p>
                                <span class="text-gray-400 text-sm">Nov 02</span>
                            </div>
                            <p class="text-gray-400 text-sm">Entregado • hace 6 horas</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white font-medium">Nueva orden recibida</p>
                                <span class="text-gray-400 text-sm">Nov 03</span>
                            </div>
                            <p class="text-gray-400 text-sm">Recoger • hace 1 día</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-tie text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white font-medium">Manager Posteado</p>
                                <span class="text-gray-400 text-sm">Nov 03</span>
                            </div>
                            <p class="text-gray-400 text-sm">En Tránsito • ayer</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-white font-medium">1 gasto pendiente</p>
                                <span class="text-gray-400 text-sm">Nov 04</span>
                            </div>
                            <p class="text-gray-400 text-sm">hace 6 horas</p>
                        </div>
                    </div>
                </div>

                <!-- Mini Chart -->
                <div class="mt-6 h-24">
                    <canvas id="miniChart"></canvas>
                </div>
            </div>

            <!-- Best Selling Products / Top Expenses -->
            <div class="glass rounded-2xl p-6 card-hover fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">Gastos Principales</h3>
                    <button class="text-purple-400 hover:text-purple-300">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="relative group">
                        <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 h-48 flex flex-col justify-between overflow-hidden">
                            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                -15%
                            </div>
                            <div class="flex-1 flex items-center justify-center">
                                <i class="fas fa-receipt text-white text-4xl opacity-20"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1">Boletas & Comprobantes</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-200 line-through text-xs">S/200</span>
                                    <span class="text-white font-bold">S/140.00</span>
                                </div>
                                <div class="flex text-yellow-400 text-xs mt-1">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <button class="w-full bg-white/20 hover:bg-white/30 text-white text-xs py-2 rounded-lg mt-2 transition">
                                    <i class="fas fa-shopping-cart mr-1"></i> Ver Ahora
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="relative group">
                        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-4 h-48 flex flex-col justify-between overflow-hidden">
                            <div class="flex-1 flex items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-white text-4xl opacity-20"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm mb-1">Facturas de Servicios</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-200 line-through text-xs">S/320</span>
                                    <span class="text-white font-bold">S/280.00</span>
                                </div>
                                <div class="flex text-yellow-400 text-xs mt-1">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <button class="w-full bg-white/20 hover:bg-white/30 text-white text-xs py-2 rounded-lg mt-2 transition">
                                    <i class="fas fa-shopping-cart mr-1"></i> Ver Ahora
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Data Table -->
<div class="glass rounded-2xl p-6 card-hover fade-in">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-white">Lista de Gastos Recientes</h3>
        <div class="flex items-center space-x-4">
            <input type="text" placeholder="Buscar gastos..." class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <button onclick="exportData()" class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded-lg transition">
                <i class="fas fa-download mr-2"></i>Exportar
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
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
            <tbody class="divide-y divide-gray-700" id="expensesTableBody">
                <?php
                include 'config.php';

                $sql = "SELECT id, tipo_comprobante, serie_numero, nombre_cliente, fecha, moneda, subtotal, igv, importe_total, imagen_ruta 
                        FROM gastos ORDER BY fecha DESC LIMIT 20";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($gastos) > 0):
                    foreach ($gastos as $row):
                ?>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="py-3 px-4 text-white">#<?= $row['id'] ?></td>
                    <td class="py-3 px-4">
                        <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded-full text-xs"><?= $row['tipo_comprobante'] ?></span>
                    </td>
                    <td class="py-3 px-4 text-gray-300"><?= $row['serie_numero'] ?></td>
                    <td class="py-3 px-4 text-white"><?= $row['nombre_cliente'] ?></td>
                    <td class="py-3 px-4 text-gray-300"><?= $row['fecha'] ?></td>
                    <td class="py-3 px-4 text-gray-300"><?= $row['moneda'] ?></td>
                    <td class="py-3 px-4 text-white">S/<?= number_format($row['subtotal'],2) ?></td>
                    <td class="py-3 px-4 text-white">S/<?= number_format($row['igv'],2) ?></td>
                    <td class="py-3 px-4 text-white font-semibold">S/<?= number_format($row['importe_total'],2) ?></td>
                    <td class="py-3 px-4">
                        <button onclick="showImage('<?= $row['imagen_ruta'] ?>')" class="text-yellow-400 hover:text-yellow-300">
                            <i class="fas fa-image"></i> Ver
                        </button>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-yellow-400 hover:text-yellow-300" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="text-blue-400 hover:text-blue-300" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-400 hover:text-red-300" title="Eliminar">
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

    <!-- Modal para mostrar imágenes -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeImageModal()">&times;</span>
            <div class="text-center">
                <h3 class="text-lg font-semibold text-yellow-400 mb-4">Vista Previa del Comprobante</h3>
                <img id="modalImage" src="" alt="Comprobante" class="max-w-full h-auto">
            </div>
        </div>
    </div>

    <!-- Modal para detalles -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeDetailModal()">&times;</span>
            <h3 id="detailTitle" class="text-lg font-semibold text-yellow-400 mb-4">Detalles del Gasto</h3>
            <div id="detailValues" class="text-white"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Data for charts - Replace with your PHP data
        const expensesData = {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Gastos 2024',
                data: [9542, 8200, 7800, 9100, 8500, 7200, 6800],
                backgroundColor: 'rgba(251, 191, 36, 0.1)',
                borderColor: 'rgb(251, 191, 36)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(251, 191, 36)',
                pointBorderColor: 'rgb(251, 191, 36)',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(251, 191, 36)'
            }]
        };

        const miniChartData = {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                data: [30, 25, 36, 30, 45, 35],
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        };

        // Main Chart Configuration
        const ctx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(ctx, {
            type: 'line',
            data: expensesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgb(251, 191, 36)',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            callback: function(value) {
                                return 'S/' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 8
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

        // Export function
        function exportData() {
            // Simulate export functionality
            const csvContent = "data:text/csv;charset=utf-8,";
            const headers = ["ID", "Tipo", "Serie", "Cliente", "Fecha", "Moneda", "Subtotal", "IGV", "Total"];
            const rows = [
                ["#001", "Boleta", "B001-123", "Juan Pérez", "2024-01-15", "PEN", "106.36", "19.14", "125.50"],
                ["#002", "Factura", "F001-456", "María García", "2024-01-14", "PEN", "76.26", "13.73", "89.99"],
                ["#003", "Recibo", "R001-789", "Carlos López", "2024-01-13", "USD", "169.49", "30.51", "200.00"]
            ];
            
            let csv = csvContent + headers.join(",") + "\n";
            rows.forEach(row => {
                csv += row.join(",") + "\n";
            });
            
            const encodedUri = encodeURI(csv);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "gastos_reporte.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success message
            showNotification('Datos exportados correctamente', 'success');
        }

        // Generate Report function
        function generateReport() {
            showNotification('Generando reporte...', 'info');
            // Simulate report generation
            setTimeout(() => {
                showNotification('Reporte generado exitosamente', 'success');
            }, 2000);
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg glass fade-in max-w-sm`;
            
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
                    <i class="${icons[type]}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add hover effects to cards
        document.querySelectorAll('.card-hover').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
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
                element.innerHTML = 'S/' + value.toLocaleString() + '.00';
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Initialize animations when page loads
        window.addEventListener('load', function() {
            // Animate the main revenue number
            const revenueElement = document.querySelector('.text-3xl.font-bold.text-white.mb-2');
            if (revenueElement) {
                animateValue(revenueElement, 0, 9542, 2000);
            }
            
            console.log('Dashboard cargado exitosamente!');
            showNotification('Dashboard cargado correctamente', 'success');
        });

        // Update chart data (you can call this function to update with real PHP data)
        function updateChartData(newLabels, newData) {
            expensesChart.data.labels = newLabels;
            expensesChart.data.datasets[0].data = newData;
            expensesChart.update();
        }

        // Function to add new expense to table (you can use this with PHP)
        function addExpenseToTable(expense) {
            const tableBody = document.getElementById('expensesTableBody');
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-800/30 transition';
            
            row.innerHTML = `
                <td class="py-3 px-4 text-white">#${expense.id}</td>
                <td class="py-3 px-4">
                    <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded-full text-xs">${expense.tipo}</span>
                </td>
                <td class="py-3 px-4 text-gray-300">${expense.serie}</td>
                <td class="py-3 px-4 text-white">${expense.cliente}</td>
                <td class="py-3 px-4 text-gray-300">${expense.fecha}</td>
                <td class="py-3 px-4 text-gray-300">${expense.moneda}</td>
                <td class="py-3 px-4 text-white">S/${expense.subtotal}</td>
                <td class="py-3 px-4 text-white">S/${expense.igv}</td>
                <td class="py-3 px-4 text-white font-semibold">S/${expense.total}</td>
                <td class="py-3 px-4">
                    <button onclick="showImage('${expense.imagen}')" class="text-yellow-400 hover:text-yellow-300">
                        <i class="fas fa-image"></i> Ver
                    </button>
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center space-x-2">
                        <button class="text-yellow-400 hover:text-yellow-300" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-blue-400 hover:text-blue-300" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-400 hover:text-red-300" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            tableBody.appendChild(row);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + N for new expense
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'sesion.php';
            }
            
            // Ctrl + E for export
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportData();
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                closeImageModal();
                closeDetailModal();
            }
        });

        // Auto-refresh data every 5 minutes (optional)
        setInterval(function() {
            // You can uncomment this to enable auto-refresh
            // location.reload();
            console.log('Auto-refresh check...');
        }, 300000); // 5 minutes
    </script>
</body>
</html>