<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contraseña = trim($_POST['contraseña'] ?? '');
    $confirm_contraseña = trim($_POST['confirm_contraseña'] ?? '');

    if (empty($nombre) || empty($correo) || empty($contraseña) || empty($confirm_contraseña)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($contraseña !== $confirm_contraseña) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($contraseña) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        include 'config.php';

        try {
            // Verificar si el nombre o correo ya existen
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = :nombre OR correo = :correo");
            $stmt->execute(['nombre' => $nombre, 'correo' => $correo]);
            if ($stmt->rowCount() > 0) {
                $error = "El nombre o correo ya está en uso.";
            } else {
                // Insertar el nuevo usuario
                $hashed_contraseña = password_hash($contraseña, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña) VALUES (:nombre, :correo, :hashed_contrasena)");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':correo' => $correo,
                    ':hashed_contrasena' => $hashed_contraseña
                ]);
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos - Registro</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="register.css">
</head>
<body class="bg-black text-white font-sans min-h-screen flex items-center justify-center bg-cover bg-center bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1519227355458-8a3a8c1e2f2a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
    <div class="absolute inset-0 bg-black opacity-75"></div>
    <div class="relative w-full max-w-md p-8 bg-gray-900 rounded-lg shadow-xl z-10">
        <h2 class="text-3xl font-bold text-yellow-400 text-center mb-6">Registro de Usuario</h2>
        <?php if ($error): ?>
            <div id="error-message" class="error text-center mb-4" data-error="<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div id="error-message" class="error text-center mb-4 hidden" data-error=""></div>
        <?php endif; ?>
        <form id="register-form" method="POST" action="register.php" class="space-y-6">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
            </div>
            <div>
                <label for="correo" class="block text-sm font-medium text-gray-300">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
            </div>
            <div>
                <label for="contraseña" class="block text-sm font-medium text-gray-300">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
            </div>
            <div>
                <label for="confirm_contraseña" class="block text-sm font-medium text-gray-300">Confirmar Contraseña</label>
                <input type="password" id="confirm_contraseña" name="confirm_contraseña" class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300">
            </div>
            <button type="submit" class="w-full bg-yellow-400 text-black font-semibold py-3 rounded-md hover:bg-yellow-500 transition-all duration-300 transform hover:scale-105">Registrarse</button>
        </form>
        <p class="mt-6 text-center text-gray-400">¿Ya tienes una cuenta? <a href="login.php" class="text-yellow-400 hover:underline">Inicia Sesión</a></p>
    </div>
    <script src="register.js"></script>
</body>
</html>