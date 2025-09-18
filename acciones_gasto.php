<?php
include 'config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // 🔹 ELIMINAR
    if ($action == 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM gasto WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        echo $stmt->execute() ? "ok" : "error";
    }

    // 🔹 EDITAR (simplificado: solo cliente y total, puedes ampliar)
    if ($action == 'edit' && isset($_POST['id'], $_POST['nombre_cliente'], $_POST['importe_total'])) {
        $id = intval($_POST['id']);
        $nombre = $_POST['nombre_cliente'];
        $total = floatval($_POST['importe_total']);
        $sql = "UPDATE gasto SET nombre_cliente = :nombre, importe_total = :total WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        echo $stmt->execute() ? "ok" : "error";
    }
}
