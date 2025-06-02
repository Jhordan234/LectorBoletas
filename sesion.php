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

$error = '';
$success = '';
$api_response = ''; // Para depuración
$extracted_data_raw = ''; // Para mostrar datos extraídos

// Inicializar variable de sesión para datos temporales
if (!isset($_SESSION['pending_data'])) {
    $_SESSION['pending_data'] = null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upload_dir = 'Uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = '';
    $file_path = '';

    if (isset($_FILES['boleta']) && $_FILES['boleta']['error'] == UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['boleta']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'];
        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Formato de imagen no permitido. Usa PNG, JPG, JPEG, GIF, BMP o WEBP.";
        } else {
            $file_name = uniqid('boleta_') . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            if (!move_uploaded_file($_FILES['boleta']['tmp_name'], $file_path)) {
                $error = "Error al subir la imagen.";
            }
        }
    } elseif (isset($_POST['captured_image'])) {
        $file_name = uniqid('boleta_') . '.png';
        $file_path = $upload_dir . $file_name;
        $image_data = $_POST['captured_image'];
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        $image_data = str_replace(' ', '+', $image_data);
        $decoded_image = base64_decode($image_data);
        if (!file_put_contents($file_path, $decoded_image)) {
            $error = "Error al guardar la imagen capturada.";
        }
    } elseif (isset($_POST['confirm_save'])) {
        // Guardar datos editados
        $params = [
            'usuario_id' => $_SESSION['user_id'],
            'tipo_comprobante' => $_POST['tipo_comprobante'] ?? 'Boleta',
            'serie_numero' => $_POST['serie_numero'] ?? '',
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'moneda' => $_POST['moneda'] ?? 'PEN',
            'documento_identidad' => $_POST['documento_identidad'] ?? '',
            'nombre_cliente' => $_POST['nombre_cliente'] ?? '',
            'subtotal' => floatval($_POST['subtotal'] ?? 0.00),
            'igv' => floatval($_POST['igv'] ?? 0.00),
            'importe_total' => floatval($_POST['importe_total'] ?? 0.00),
            'imagen_ruta' => $_SESSION['pending_data']['imagen_ruta'] ?? ''
        ];
        try {
            $stmt = $conn->prepare("INSERT INTO gastos (usuario_id, tipo_comprobante, serie_numero, fecha, moneda, documento_identidad, nombre_cliente, subtotal, igv, importe_total, imagen_ruta) VALUES (:usuario_id, :tipo_comprobante, :serie_numero, :fecha, :moneda, :documento_identidad, :nombre_cliente, :subtotal, :igv, :importe_total, :imagen_ruta)");
            $stmt->execute($params);
            $_SESSION['pending_data'] = null; // Limpiar datos temporales
            header("Location: report.php");
            exit;
        } catch (Exception $e) {
            $error = "Error al guardar los datos: " . $e->getMessage();
        }
    } elseif (isset($_POST['clear_pending'])) {
        // Limpiar datos temporales si se cancela
        $_SESSION['pending_data'] = null;
        exit;
    } else {
        $error = "No se recibió ninguna imagen.";
    }

    if (!$error && ($file_path || isset($_POST['captured_image']))) {
        try {
            $base64_image = base64_encode(file_get_contents($file_path));
            $api_key = "4b3b890b3d2e4c06a892674f5660f7a6";
            $base_url = "https://api.aimlapi.com/v1/chat/completions";

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $base_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer $api_key",
                    "Content-Type: application/json"
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    "model" => "gpt-4o",
                    "messages" => [
                        [
                            "role" => "user",
                            "content" => [
                                [
                                    "type" => "text",
                                    "text" => "Analiza esta boleta electrónica peruana y extrae los siguientes datos en formato JSON. Las boletas electrónicas peruanas suelen tener etiquetas específicas antes de los valores. Busca el texto exacto después de estas etiquetas y mapea los datos a los campos correspondientes. Ejemplos de etiquetas y valores esperados: 'BOLETA DE VENTA ELECTRÓNICA' o 'BOLETA ELECTRÓNICA' para 'tipo_comprobante', 'B995-00010549' después de 'Nro:' o sin etiqueta para 'serie_y_numero', 'Fecha de Emisión: 31/03/2019' para 'fecha' (formato DD/MM/YYYY), 'Tipo de Moneda: PEN' para 'moneda', 'DNI: 70295669' o 'RUC: 12345678901' para 'documento_de_identidad', 'Cliente: RAUL ALONSO MERZTHAL MANRIQUE' para 'nombre_del_cliente', 'Op. Gravada: S/ 18.64' o 'Op. Exonerada: S/ 22.00' para 'subtotal', 'IGV: S/ 3.36' para 'igv' (si no se encuentra, usa 0.00), 'Importe Total: S/ 22.00' o 'SON: VEINTE Y DOS 00/100 SOLES' para 'importe_total' (convierte el texto a número si es necesario). Si un campo no se encuentra, usa null. Devuelve el resultado en formato JSON con los nombres exactos de los campos: 'tipo_comprobante', 'serie_y_numero', 'fecha', 'moneda', 'documento_de_identidad', 'nombre_del_cliente', 'subtotal', 'igv', 'importe_total'."
                                ],
                                ["type" => "image_url", "image_url" => ["url" => "data:image/png;base64,$base64_image"]]
                            ]
                        ]
                    ],
                    "max_tokens" => 512
                ])
            ]);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                throw new Exception("Error en la API: " . curl_error($curl));
            }
            curl_close($curl);

            $api_response = $response;
            $response_data = json_decode($response, true);
            if (isset($response_data['choices'][0]['message']['content'])) {
                $raw_content = $response_data['choices'][0]['message']['content'];
                error_log("Respuesta cruda de la API: " . $raw_content); // Depuración

                // Extraer el JSON puro entre los marcadores ```json
                preg_match('/```json\s*([\s\S]*?)\s*```/', $raw_content, $matches);
                $extracted_data_raw = isset($matches[1]) ? trim($matches[1]) : trim($raw_content);

                // Intentar parsear el JSON
                $extracted_data = json_decode($extracted_data_raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $error = "La API no devolvió un JSON válido. Error: " . json_last_error_msg() . ". Respuesta: " . htmlspecialchars($extracted_data_raw);
                } else {
                    // Mapear y validar datos
                    $tipo_comprobante = $extracted_data['tipo_comprobante'] ?? null;
                    $serie_numero = $extracted_data['serie_y_numero'] ?? null;
                    $fecha = $extracted_data['fecha'] ?? null;
                    if ($fecha && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) {
                        $fecha = "$matches[3]-$matches[2]-$matches[1]"; // Convertir a YYYY-MM-DD
                    }
                    $moneda = $extracted_data['moneda'] ?? null;
                    $documento_identidad = $extracted_data['documento_de_identidad'] ?? null;
                    $nombre_cliente = $extracted_data['nombre_del_cliente'] ?? null;
                    $subtotal = floatval($extracted_data['subtotal'] ?? 0.00);
                    $igv = floatval($extracted_data['igv'] ?? 0.00);
                    $importe_total = floatval($extracted_data['importe_total'] ?? 0.00);

                    // Almacenar datos temporalmente en la sesión
                    $_SESSION['pending_data'] = [
                        'usuario_id' => $_SESSION['user_id'],
                        'tipo_comprobante' => $tipo_comprobante ?? 'Boleta',
                        'serie_numero' => $serie_numero ?? '',
                        'fecha' => $fecha ?? date('Y-m-d'),
                        'moneda' => $moneda ?? 'PEN',
                        'documento_identidad' => $documento_identidad ?? '',
                        'nombre_cliente' => $nombre_cliente ?? '',
                        'subtotal' => $subtotal,
                        'igv' => $igv,
                        'importe_total' => $importe_total,
                        'imagen_ruta' => $file_path
                    ];
                    error_log("Datos temporales almacenados: " . print_r($_SESSION['pending_data'], true));
                }
            } else {
                $error = "No se pudieron extraer los datos de la boleta. Respuesta API: " . htmlspecialchars($response_data['error'] ?? 'Sin detalles');
            }
        } catch (Exception $e) {
            $error = "Error al procesar la imagen: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos - Procesar Boleta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="sesion.css">
</head>
<body class="bg-black text-white font-sans min-h-screen flex items-center justify-center bg-cover bg-center bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1519227355458-8a3a8c1e2f2a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
    <div class="absolute inset-0 bg-black opacity-75"></div>
    <div class="relative w-full max-w-md p-8 bg-gray-900 rounded-lg shadow-xl z-10">
        <h2 class="text-3xl font-bold text-yellow-400 text-center mb-6">Procesar Boleta</h2>
        <?php if ($error): ?>
            <div id="error-message" class="error text-center mb-4 shake" data-error="<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div id="success-message" class="text-green-500 text-center mb-4"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($_SESSION['pending_data']): ?>
            <div class="text-center mb-4">
                <button id="openJsonModal" class="json-modal-open">Ver y Editar Datos Extraídos</button>
            </div>
        <?php endif; ?>
        <div class="space-y-6">
            <!-- Captura con cámara -->
            <div>
                <label class="block text-sm font-medium text-gray-300">Capturar Boleta</label>
                <video id="video" class="w-full mt-2 rounded-md" autoplay></video>
                <canvas id="canvas" class="hidden"></canvas>
                <button id="capture" class="w-full mt-2 bg-yellow-400 text-black font-semibold py-3 rounded-md hover:bg-yellow-500 transition-all duration-300 transform hover:scale-105">Tomar Foto</button>
            </div>
            <!-- Subida de archivo -->
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="boleta" class="block text-sm font-medium text-gray-300">Subir Boleta (Imagen)</label>
                    <input type="file" id="boleta" name="boleta" accept="image/*" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
                </div>
                <button type="submit" class="w-full bg-yellow-400 text-black font-semibold py-3 rounded-md hover:bg-yellow-500 transition-all duration-300 transform hover:scale-105">Procesar y Guardar</button>
            </form>
            <p class="mt-6 text-center text-gray-400"><a href="report.php" class="text-yellow-400 hover:underline">Ver Reporte de Gastos</a></p>
            <p class="mt-2 text-center text-gray-400"><a href="logout.php" class="text-yellow-400 hover:underline">Cerrar Sesión</a></p>
        </div>
    </div>

    <!-- Modal para editar datos extraídos -->
    <div id="jsonModal" class="json-modal">
        <div class="json-modal-content">
            <span class="json-modal-close">×</span>
            <h3 class="text-lg font-semibold text-yellow-400 mb-4">Editar Datos Extraídos</h3>
            <?php if ($_SESSION['pending_data']): ?>
                <form id="editForm" method="POST" action="sesion.php" class="space-y-4">
                    <div>
                        <label for="tipo_comprobante" class="block text-sm font-medium text-gray-300">Tipo de Comprobante</label>
                        <input type="text" id="tipo_comprobante" name="tipo_comprobante" value="<?php echo htmlspecialchars($_SESSION['pending_data']['tipo_comprobante']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="serie_numero" class="block text-sm font-medium text-gray-300">Serie y Número</label>
                        <input type="text" id="serie_numero" name="serie_numero" value="<?php echo htmlspecialchars($_SESSION['pending_data']['serie_numero']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-300">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($_SESSION['pending_data']['fecha']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="moneda" class="block text-sm font-medium text-gray-300">Moneda</label>
                        <input type="text" id="moneda" name="moneda" value="<?php echo htmlspecialchars($_SESSION['pending_data']['moneda']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="documento_identidad" class="block text-sm font-medium text-gray-300">Documento de Identidad</label>
                        <input type="text" id="documento_identidad" name="documento_identidad" value="<?php echo htmlspecialchars($_SESSION['pending_data']['documento_identidad']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="nombre_cliente" class="block text-sm font-medium text-gray-300">Nombre del Cliente</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" value="<?php echo htmlspecialchars($_SESSION['pending_data']['nombre_cliente']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="subtotal" class="block text-sm font-medium text-gray-300">Subtotal</label>
                        <input type="number" step="0.01" id="subtotal" name="subtotal" value="<?php echo htmlspecialchars($_SESSION['pending_data']['subtotal']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="igv" class="block text-sm font-medium text-gray-300">IGV</label>
                        <input type="number" step="0.01" id="igv" name="igv" value="<?php echo htmlspecialchars($_SESSION['pending_data']['igv']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div>
                        <label for="importe_total" class="block text-sm font-medium text-gray-300">Importe Total</label>
                        <input type="number" step="0.01" id="importe_total" name="importe_total" value="<?php echo htmlspecialchars($_SESSION['pending_data']['importe_total']); ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div class="mt-4 text-center">
                        <button type="submit" name="confirm_save" value="yes" class="bg-green-500 text-white px-4 py-2 rounded-md mr-2 hover:bg-green-600">Sí</button>
                        <button type="button" id="confirmNo" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">No</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="sesion.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>