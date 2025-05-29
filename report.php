<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Obtener los datos de la tabla gastos para el usuario actual
try {
    $stmt = $conn->prepare("SELECT id, tipo_comprobante, serie_numero, fecha, moneda, documento_identidad, nombre_cliente, subtotal, igv, importe_total, imagen_ruta, created_at FROM gastos WHERE usuario_id = :usuario_id ORDER BY created_at DESC");
    $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener los datos: " . $e->getMessage();
}

// Definir los campos que queremos mostrar como tarjetas (máximo 10)
$campos = [
    'tipo_comprobante' => ['label' => 'Tipo', 'icon' => 'fa-file-invoice'],
    'serie_numero' => ['label' => 'Serie y Número', 'icon' => 'fa-barcode'],
    'fecha' => ['label' => 'Fecha', 'icon' => 'fa-calendar-alt'],
    'moneda' => ['label' => 'Moneda', 'icon' => 'fa-coins'],
    'documento_identidad' => ['label' => 'Documento', 'icon' => 'fa-id-card'],
    'nombre_cliente' => ['label' => 'Cliente', 'icon' => 'fa-user'],
    'subtotal' => ['label' => 'Subtotal', 'icon' => 'fa-money-bill'],
    'igv' => ['label' => 'IGV', 'icon' => 'fa-percent'],
    'importe_total' => ['label' => 'Total', 'icon' => 'fa-wallet'],
    'imagen_ruta' => ['label' => 'Imagen', 'icon' => 'fa-image'],
];

// Preparar datos para el modal (todos los valores por campo)
$datos_por_campo = [];
foreach ($campos as $campo => $info) {
    $datos_por_campo[$campo] = [];
    foreach ($gastos as $gasto) {
        $valor = $gasto[$campo];
        if ($campo === 'subtotal' || $campo === 'igv' || $campo === 'importe_total') {
            $valor = number_format($valor, 2);
        }
        $datos_por_campo[$campo][] = [
            'id' => $gasto['id'],
            'valor' => $valor,
        ];
    }
}
$datos_por_campo_json = json_encode($datos_por_campo);

// Generar datos para el gráfico (suma de todos los gastos)
$total_subtotal = 0;
$total_igv = 0;
$total_importe = 0;
foreach ($gastos as $gasto) {
    $total_subtotal += floatval($gasto['subtotal']);
    $total_igv += floatval($gasto['igv']);
    $total_importe += floatval($gasto['importe_total']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos - Reportes</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="report.css">
</head>
<body class="text-white font-sans min-h-screen">
    <!-- Header -->
    <nav class="bg-gray-900 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-3xl font-bold text-yellow-400">
                <a href="index.php">King Soft</a>
            </div>
            <div class="space-x-6">
                <a href="sesion.php" class="hover:text-yellow-400 transition">Registrar Boleta</a>
                <a href="logout.php" class="hover:text-yellow-400 transition">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center text-yellow-400 mb-12 fade-in">Reportes de Gastos</h2>
            <?php if (isset($error)): ?>
                <div class="text-center mb-4 text-red-500"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($gastos)): ?>
                <div class="text-center mb-4 text-gray-300">No hay gastos registrados.</div>
            <?php else: ?>
                <div class="text-center mb-8">
                    <button id="flipAllCards" class="bg-yellow-400 text-black font-semibold py-3 px-6 rounded-md hover:bg-yellow-500 transition-all duration-300 transform hover:scale-105">Ver Datos</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 fade-in">
                    <?php foreach ($campos as $campo => $info): ?>
                        <div class="card-wrapper">
                            <div class="card" data-field="<?php echo $campo; ?>">
                                <!-- Parte frontal -->
                                <div class="card-front bg-gray-800 rounded-lg shadow-lg flex flex-col items-center justify-center">
                                    <i class="fas <?php echo $info['icon']; ?> text-yellow-400 text-4xl mb-2"></i>
                                    <p class="text-white font-semibold"><?php echo htmlspecialchars($info['label']); ?></p>
                                </div>
                                <!-- Parte trasera -->
                                <div class="card-back bg-gray-800 rounded-lg shadow-lg flex items-center justify-center p-4">
                                    <button class="view-detail text-yellow-400 hover:underline" data-field="<?php echo $campo; ?>">Ver</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tabla debajo de las tarjetas -->
                <div class="mt-12 fade-in">
                    <h3 class="text-2xl font-semibold text-yellow-400 mb-4">Lista de Gastos</h3>
                    <div class="table-container bg-gray-800 rounded-lg shadow-lg p-6">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-900 text-yellow-400">
                                    <th class="p-3">ID</th>
                                    <th class="p-3">Tipo</th>
                                    <th class="p-3">Serie y Número</th>
                                    <th class="p-3">Fecha</th>
                                    <th class="p-3">Moneda</th>
                                    <th class="p-3">Documento</th>
                                    <th class="p-3">Cliente</th>
                                    <th class="p-3">Subtotal</th>
                                    <th class="p-3">IGV</th>
                                    <th class="p-3">Total</th>
                                    <th class="p-3">Imagen</th>
                                    <th class="p-3">Creado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $gasto): ?>
                                    <tr class="border-b border-gray-700">
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['id']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['tipo_comprobante']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['serie_numero']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['fecha']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['moneda']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['documento_identidad']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['nombre_cliente']); ?></td>
                                        <td class="p-3"><?php echo number_format($gasto['subtotal'], 2); ?></td>
                                        <td class="p-3"><?php echo number_format($gasto['igv'], 2); ?></td>
                                        <td class="p-3"><?php echo number_format($gasto['importe_total'], 2); ?></td>
                                        <td class="p-3">
                                            <button class="view-image text-yellow-400 hover:underline" data-image="<?php echo htmlspecialchars($gasto['imagen_ruta']); ?>">
                                                <i class="fas fa-image"></i> Ver
                                            </button>
                                        </td>
                                        <td class="p-3"><?php echo htmlspecialchars($gasto['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Gráfico de barras -->
                <div class="mt-12 fade-in">
                    <h3 class="text-2xl font-semibold text-yellow-400 mb-4">Reporte de Gastos (Totales)</h3>
                    <div id="chart-container" class="bg-gray-800 rounded-lg shadow-lg p-6">
                        <img src="chart.png" alt="Reporte de Gastos" class="w-full max-w-2xl mx-auto">
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal para mostrar imágenes -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <img id="modalImage" src="" alt="Boleta">
        </div>
    </div>

    <!-- Modal para detalles -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal-detail">×</span>
            <h3 id="detailTitle" class="text-lg font-semibold text-yellow-400 mb-2"></h3>
            <div id="detailValues" class="text-white text-center"></div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script>
        const datosPorCampo = <?php echo $datos_por_campo_json; ?>;
    </script>
    <script src="report.js"></script>
</body>
</html>
<?php
// Generar gráfico con matplotlib (se generará después de instalar matplotlib)
if (!empty($gastos)) {
    $script = <<<EOD
import matplotlib.pyplot as plt
import numpy as np

labels = ['Subtotal', 'IGV', 'Total']
values = [$total_subtotal, $total_igv, $total_importe]

plt.figure(figsize=(8, 6))
plt.bar(labels, values, color=['#f4b400', '#f4a400', '#f48000'])
plt.title('Reporte de Gastos (Totales)')
plt.xlabel('Categoría')
plt.ylabel('Monto (PEN)')
plt.grid(True, axis='y', linestyle='--', alpha=0.7)

plt.savefig('chart.png')
plt.close()
EOD;

    file_put_contents('generate_chart.py', $script);
    // No ejecutamos exec aquí hasta que instalemos matplotlib
}
ob_end_flush();
?>