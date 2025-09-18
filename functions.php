<?php
function handleImageUpload($files, $post) {
    $upload_dir = 'Uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (!empty($files['boleta']['name'])) {
        $ext = strtolower(pathinfo($files['boleta']['name'], PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','gif','bmp','webp'];
        if (!in_array($ext, $allowed)) throw new Exception("Formato no permitido.");
        $file_name = uniqid('boleta_') . ".$ext";
        $path = $upload_dir . $file_name;
        if (!move_uploaded_file($files['boleta']['tmp_name'], $path)) {
            throw new Exception("Error al subir la imagen.");
        }
        return $path;
    }

    if (!empty($post['captured_image'])) {
        $file_name = uniqid('boleta_') . '.png';
        $path = $upload_dir . $file_name;
        $decoded = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/\w+;base64,#i', '', $post['captured_image'])));
        if (!file_put_contents($path, $decoded)) throw new Exception("Error al guardar captura.");
        return $path;
    }
    return null;
}

function callBoletaAPI($file_path) {
    $base64_image = base64_encode(file_get_contents($file_path));
    $api_key = "4b3b890b3d2e4c06a892674f5660f7a6";
    $url = "https://api.aimlapi.com/v1/chat/completions";

    $payload = [
        "model" => "gpt-4o",
        "messages" => [[
            "role" => "user",
            "content" => [
                ["type" => "text", "text" => "Analiza esta boleta electrónica peruana..."],
                ["type" => "image_url", "image_url" => ["url" => "data:image/png;base64,$base64_image"]]
            ]
        ]],
        "max_tokens" => 512
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $api_key", "Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    if (!$response) throw new Exception("Error en la API: " . curl_error($ch));
    curl_close($ch);

    $data = json_decode($response, true);
    $raw = $data['choices'][0]['message']['content'] ?? null;

    if (!$raw) throw new Exception("API no devolvió contenido válido.");

    preg_match('/```json\s*([\s\S]*?)\s*```/', $raw, $matches);
    $json_data = json_decode($matches[1] ?? $raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }
    return $json_data;
}

function mapApiData($data, $user_id, $file_path) {
    if (!$data) return null;

    // Conversión de fecha
    $fecha = $data['fecha'] ?? date('Y-m-d');
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $m)) {
        $fecha = "$m[3]-$m[2]-$m[1]";
    }

    return [
        'usuario_id' => $user_id,
        'tipo_comprobante' => $data['tipo_comprobante'] ?? 'Boleta',
        'serie_numero' => $data['serie_y_numero'] ?? '',
        'fecha' => $fecha,
        'moneda' => $data['moneda'] ?? 'PEN',
        'documento_identidad' => $data['documento_de_identidad'] ?? '',
        'nombre_cliente' => $data['nombre_del_cliente'] ?? '',
        'subtotal' => floatval($data['subtotal'] ?? 0),
        'igv' => floatval($data['igv'] ?? 0),
        'importe_total' => floatval($data['importe_total'] ?? 0),
        'imagen_ruta' => $file_path
    ];
}

function savePendingData($conn, $params, $edited = null) {
    if ($edited) {
        $edit = json_decode($edited, true);
        $params = array_merge($params, $edit);
    }

    $sql = "INSERT INTO gastos (usuario_id, tipo_comprobante, serie_numero, fecha, moneda, documento_identidad, nombre_cliente, subtotal, igv, importe_total, imagen_ruta)
            VALUES (:usuario_id,:tipo_comprobante,:serie_numero,:fecha,:moneda,:documento_identidad,:nombre_cliente,:subtotal,:igv,:importe_total,:imagen_ruta)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
}