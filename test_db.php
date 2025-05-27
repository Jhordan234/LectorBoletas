<?php
include 'config.php';
try {
    $stmt = $conn->query("SELECT 1");
    echo "Conexión exitosa a la base de datos!";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>