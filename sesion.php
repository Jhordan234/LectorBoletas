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
$extracted_data_json = ''; // Para pasar datos al JavaScript

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
    } elseif (isset($_POST['confirm_save']) && isset($_SESSION['pending_data'])) {
        // Confirmación de guardado
        $params = $_SESSION['pending_data'];

        // Actualizar los datos con los valores editados si existen
        if (isset($_POST['edited_data'])) {
            $edited_data = json_decode($_POST['edited_data'], true);
            if ($edited_data) {
                $params['tipo_comprobante'] = $edited_data['tipo_comprobante'] ?? $params['tipo_comprobante'];
                $params['serie_numero'] = $edited_data['serie_y_numero'] ?? $params['serie_numero'];
                $fecha = $edited_data['fecha'] ?? $params['fecha'];
                if ($fecha && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) {
                    $fecha = "$matches[3]-$matches[2]-$matches[1]"; // Convertir a YYYY-MM-DD
                }
                $params['fecha'] = $fecha;
                $params['moneda'] = $edited_data['moneda'] ?? $params['moneda'];
                $params['documento_identidad'] = $edited_data['documento_de_identidad'] ?? $params['documento_identidad'];
                $params['nombre_cliente'] = $edited_data['nombre_del_cliente'] ?? $params['nombre_cliente'];
                $params['subtotal'] = floatval($edited_data['subtotal'] ?? $params['subtotal']);
                $params['igv'] = floatval($edited_data['igv'] ?? $params['igv']);
                $params['importe_total'] = floatval($edited_data['importe_total'] ?? $params['importe_total']);
            }
        }

        try {
            $stmt = $conn->prepare("INSERT INTO gastos (usuario_id, tipo_comprobante, serie_numero, fecha, moneda, documento_identidad, nombre_cliente, subtotal, igv, importe_total, imagen_ruta) VALUES (:usuario_id, :tipo_comprobante, :serie_numero, :fecha, :moneda, :documento_identidad, :nombre_cliente, :subtotal, :igv, :importe_total, :imagen_ruta)");
            $stmt->execute($params);
            $_SESSION['pending_data'] = null; // Limpiar datos temporales
            // Redirigir a report.php después de guardar
            header("Location: report.php");
            exit;
        } catch (Exception $e) {
            $error = "Error al guardar los datos: " . $e->getMessage();
            echo json_encode(['error' => $error]);
            exit;
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
                    // Convertir datos extraídos a JSON para pasarlos al JavaScript
                    $extracted_data_json = json_encode($extracted_data);

                    // Mapear y validar datos para almacenar temporalmente
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

                    // Mostrar el modal (se manejará en JavaScript)
                    echo "<script>document.getElementById('jsonModal').style.display = 'flex';</script>";
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
                <button id="openJsonModal" class="json-modal-open">Ver Datos Extraídos</button>
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

    <!-- Modal para mostrar datos extraídos -->
    <div id="jsonModal" class="json-modal">
        <div class="json-modal-content">
            <span class="json-modal-close">×</span>
            <h3 class="text-lg font-semibold text-yellow-400 mb-4">Datos Extraídos</h3>
            <pre id="jsonData"><?php echo htmlspecialchars($extracted_data_raw); ?></pre>
            <div class="mt-4 text-center">
                <button id="confirmYes" class="bg-green-500 text-white px-4 py-2 rounded-md mr-2 hover:bg-green-600">Sí</button>
                <button id="confirmNo" class="bg-red-500 text-white px-4 py-2 rounded-md mr-2 hover:bg-red-600">No</button>
                <button id="editData" class="bg-yellow-400 text-black px-4 py-2 rounded-md hover:bg-yellow-500">Editar</button>
            </div>
        </div>
    </div>

    <!-- Modal para editar datos -->
    <div id="editModal" class="json-modal hidden">
        <div class="json-modal-content">
            <span class="edit-modal-close">×</span>
            <h3 class="text-lg font-semibold text-yellow-400 mb-4">Editar Datos</h3>
            <form id="editDataForm" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-300">Tipo de Comprobante</label>
                    <input type="text" name="tipo_comprobante" id="edit_tipo_comprobante" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Serie y Número</label>
                    <input type="text" name="serie_y_numero" id="edit_serie_y_numero" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Fecha (DD/MM/YYYY)</label>
                    <input type="text" name="fecha" id="edit_fecha" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Moneda</label>
                    <input type="text" name="moneda" id="edit_moneda" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Documento de Identidad</label>
                    <input type="text" name="documento_de_identidad" id="edit_documento_de_identidad" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Nombre del Cliente</label>
                    <input type="text" name="nombre_del_cliente" id="edit_nombre_del_cliente" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Subtotal</label>
                    <input type="number" name="subtotal" id="edit_subtotal" step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">IGV</label>
                    <input type="number" name="igv" id="edit_igv" step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300">Importe Total</label>
                    <input type="number" name="importe_total" id="edit_importe_total" step="0.01" class="w-full bg-gray-800 border border-gray-700 rounded-md p-2 text-white">
                </div>
                <div class="mt-4 text-center">
                    <button type="button" id="editConfirmYes" class="bg-green-500 text-white px-4 py-2 rounded-md mr-2 hover:bg-green-600">Sí</button>
                    <button type="button" id="editConfirmNo" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">No</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const extractedData = <?php echo $extracted_data_json ?: 'null'; ?>;
    </script>
    <script src="sesion.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>