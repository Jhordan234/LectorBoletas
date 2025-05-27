<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = [];
session_unset();
session_destroy();

// Redirigir a index.php
header("Location: index.php");
exit();
?>