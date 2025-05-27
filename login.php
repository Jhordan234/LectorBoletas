<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    header("Location: sesion.php");
    exit();
}

require 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $contraseña = trim($_POST['contraseña'] ?? '');

    if (empty($nombre) || empty($contraseña)) {
        $error = "Por favor, ingresa tu nombre y contraseña.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, nombre, contraseña FROM usuarios WHERE nombre = :nombre");
            $stmt->execute(['nombre' => $nombre]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($contraseña, $user['contraseña'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                header("Location: sesion.php");
                exit();
            } else {
                $error = "Nombre de usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error de base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos - Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="login.css">
</head>
<body class="bg-black text-white font-sans min-h-screen flex items-center justify-center bg-cover bg-center bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1519227355458-8a3a8c1e2f2a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
    <div class="absolute inset-0 bg-black opacity-75"></div>
    <div class="relative w-full max-w-md p-8 bg-gray-900 rounded-lg shadow-xl z-10">
        <h2 class="text-3xl font-bold text-yellow-400 text-center mb-6">Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div id="error-message" class="error text-center mb-4 shake" data-error="<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div id="error-message" class="error text-center mb-4 hidden" data-error=""></div>
        <?php endif; ?>
        <form id="login-form" method="POST" action="login.php" class="space-y-6">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
            </div>
            <div>
                <label for="contraseña" class="block text-sm font-medium text-gray-300">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
            </div>
            <button type="submit" class="w-full bg-yellow-400 text-black font-semibold py-3 rounded-md hover:bg-yellow-500 transition-all duration-300 transform hover:scale-105">Iniciar Sesión</button>
        </form>
        <p class="mt-6 text-center text-gray-400">¿No tienes una cuenta? <a href="register.php" class="text-yellow-400 hover:underline">Regístrate</a></p>
    </div>
    <script src="login.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>