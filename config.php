<?php
$host = "localhost";
$db_name = "registro_gastos";
$username_db = "postgres";
$password_db = "jhordancito234"; // Cambia la contraseña por seguridad
try {
    $conn = new PDO("pgsql:host=$host;dbname=$db_name", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>