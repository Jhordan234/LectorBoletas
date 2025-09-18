<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'functions.php';

$error = '';
$success = '';
$extracted_data_json = '';

// Inicializar datos pendientes
if (!isset($_SESSION['pending_data'])) {
    $_SESSION['pending_data'] = null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // 1. Procesar subida o captura
        $file_path = handleImageUpload($_FILES, $_POST);
        if (!$file_path && !isset($_POST['confirm_save'])) {
            throw new Exception("No se recibió ninguna imagen.");
        }

        // 2. Guardar confirmación
        if (isset($_POST['confirm_save'], $_SESSION['pending_data'])) {
            savePendingData($conn, $_SESSION['pending_data'], $_POST['edited_data'] ?? null);
            $_SESSION['pending_data'] = null;
            header("Location: report.php");
            exit;
        }

        if (isset($_POST['clear_pending'])) {
            $_SESSION['pending_data'] = null;
            exit;
        }

        // 3. Llamar a API y procesar JSON
        if ($file_path) {
            $api_data = callBoletaAPI($file_path);
            $mapped_data = mapApiData($api_data, $_SESSION['user_id'], $file_path);

            if (!$mapped_data) {
                throw new Exception("No se pudieron extraer datos válidos de la boleta.");
            }

            $_SESSION['pending_data'] = $mapped_data;
            $extracted_data_json = json_encode($api_data);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>