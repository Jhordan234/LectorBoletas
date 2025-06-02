<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Boletas</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-black text-white font-sans">
    <?php session_start(); ?>
    <!-- Navigation Bar -->
    <nav class="bg-gray-900 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-3xl font-bold">SMART-EXPENSE</div>
            <div class="space-x-6">
                <a href="login.php" class="hover:text-yellow-400 transition">Cargar Boleta</a>
                <a href="login.php" class="hover:text-yellow-400 transition">Reportes</a>
                <?php
                if (isset($_SESSION['user_id'])) {
                    echo '<a href="logout.php" class="hover:text-yellow-400 transition">Cerrar Sesión</a>';
                } else {
                    echo '<a href="login.php" class="hover:text-yellow-400 transition">Iniciar Sesión</a>';
                }
                ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Carousel -->
    <section class="carousel relative h-[600px]">
        <div class="carousel-item active">
            <img src="imgenes/imagen3.jpg" alt="Escaneo de Boletas" class="w-full h-full object-cover">
            <div class="carousel-caption absolute bottom-16 left-10 md:left-20 text-white">
                <h1 class="text-5xl font-extrabold mb-4">Gestión Inteligente de Gastos</h1>
                <p class="text-xl mb-6">Automatiza el registro de boletas electrónicas con nuestra avanzada tecnología de IA/ML.</p>
                <a href="upload.html" class="inline-block bg-yellow-400 text-black px-8 py-4 rounded-lg font-semibold hover:bg-yellow-500 transition">Cargar Boleta</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="imgenes/imagen1.jpg" alt="Reportes en Tiempo Real" class="w-full h-full object-cover">
            <div class="carousel-caption absolute bottom-16 left-10 md:left-20 text-white">
                <h1 class="text-5xl font-extrabold mb-4">Reportes en Tiempo Real</h1>
                <p class="text-xl mb-6">Visualiza tus gastos con reportes detallados y personalizados al instante.</p>
                <a href="reports.html" class="inline-block bg-yellow-400 text-black px-8 py-4 rounded-lg font-semibold hover:bg-yellow-500 transition">Ver Reportes</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="imgenes/imagen4.jpg" alt="Almacenamiento Seguro" class="w-full h-full object-cover">
            <div class="carousel-caption absolute bottom-16 left-10 md:left-20 text-white">
                <h1 class="text-5xl font-extrabold mb-4">Seguridad de Alto Nivel</h1>
                <p class="text-xl mb-6">Tus datos están protegidos con almacenamiento en PostgreSQL y cifrado avanzado.</p>
                <a href="#features" class="inline-block bg-yellow-400 text-black px-8 py-4 rounded-lg font-semibold hover:bg-yellow-500 transition">Conoce Más</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center text-yellow-400 mb-12">Características Principales</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-gray-900 rounded-lg shadow-lg feature-card">
                    <i class="fas fa-camera text-5xl text-yellow-400 mb-4"></i>
                    <h3 class="text-2xl font-semibold mb-3">Escaneo Inteligente</h3>
                    <p class="text-gray-300">Nuestra IA/ML extrae datos clave de boletas electrónicas, como tipo de comprobante, serie, número, fecha, moneda, RUC, nombre del cliente, subtotal, IGV y total.</p>
                </div>
                <div class="text-center p-6 bg-gray-900 rounded-lg shadow-lg feature-card">
                    <i class="fas fa-database text-5xl text-yellow-400 mb-4"></i>
                    <h3 class="text-2xl font-semibold mb-3">Almacenamiento Seguro</h3>
                    <p class="text-gray-300">Guarda tus boletas y datos financieros en una base de datos PostgreSQL robusta con backups automáticos.</p>
                </div>
                <div class="text-center p-6 bg-gray-900 rounded-lg shadow-lg feature-card">
                    <i class="fas fa-chart-bar text-5xl text-yellow-400 mb-4"></i>
                    <h3 class="text-2xl font-semibold mb-3">Reportes Avanzados</h3>
                    <p class="text-gray-300">Genera reportes personalizados por fecha, cliente o categoría, con gráficos interactivos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-black">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center text-yellow-400 mb-12">¿Cómo Funciona?</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 how-it-works">
                <div class="text-center">
                    <img src="imgenes/paso1.jpg" alt="Paso 1: Carga tu Boleta" class="mx-auto mb-4 rounded-full w-32 h-32 object-cover">
                    <h3 class="text-xl font-semibold mb-2">Carga tu Boleta</h3>
                    <p class="text-gray-300">Sube una imagen de tu boleta electrónica desde tu dispositivo.</p>
                </div>
                <div class="text-center">
                    <img src="imgenes/paso2.jpg" alt="Paso 2: Análisis con IA" class="mx-auto mb-4 rounded-full w-32 h-32 object-cover">
                    <h3 class="text-xl font-semibold mb-2">Análisis con IA</h3>
                    <p class="text-gray-300">Nuestra IA extrae automáticamente los datos relevantes de la boleta.</p>
                </div>
                <div class="text-center">
                    <img src="imgenes/paso3.jpg" alt="Paso 3: Almacenamiento" class="mx-auto mb-4 rounded-full w-32 h-32 object-cover">
                    <h3 class="text-xl font-semibold mb-2">Almacenamiento Seguro</h3>
                    <p class="text-gray-300">Los datos y la imagen se guardan en nuestra base de datos PostgreSQL.</p>
                </div>
                <div class="text-center">
                    <img src="imgenes/paso4.jpg" alt="Paso 4: Reportes" class="mx-auto mb-4 rounded-full w-32 h-32 object-cover">
                    <h3 class="text-xl font-semibold mb-2">Visualiza Reportes</h3>
                    <p class="text-gray-300">Accede a reportes detallados para gestionar tus gastos eficientemente.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center text-yellow-400 mb-12">Nuestro Impacto</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <h3 class="text-5xl font-bold text-white mb-2">10K+</h3>
                    <p class="text-gray-300">Boletas Procesadas</p>
                </div>
                <div>
                    <h3 class="text-5xl font-bold text-white mb-2">99.9%</h3>
                    <p class="text-gray-300">Precisión en Extracción</p>
                </div>
                <div>
                    <h3 class="text-5xl font-bold text-white mb-2">1M+</h3>
                    <p class="text-gray-300">Datos Almacenados</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-black">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center text-yellow-400 mb-12">Lo que Dicen Nuestros Clientes</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-900 p-6 rounded-lg shadow-lg testimonial-card">
                    <p class="text-gray-300 mb-4">"Esta plataforma ha transformado la forma en que gestionamos nuestros gastos. La IA es increíblemente precisa y los reportes son muy útiles."</p>
                    <p class="font-semibold text-white">Juan Pérez, Gerente Financiero</p>
                </div>
                <div class="bg-gray-900 p-6 rounded-lg shadow-lg testimonial-card">
                    <p class="text-gray-300 mb-4">"El proceso de carga de boletas es súper sencillo, y la seguridad de los datos me da mucha confianza."</p>
                    <p class="font-semibold text-white">María López, Contadora</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-20 bg-gray-900 text-center">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-yellow-400 mb-4">Optimiza tus Finanzas Hoy</h2>
            <p class="text-xl text-gray-300 mb-6">Sube tus boletas, analiza tus datos y toma el control de tus gastos con nuestra plataforma impulsada por IA.</p>
            <a href="login.php" class="inline-block bg-yellow-400 text-black px-8 py-4 rounded-lg font-semibold hover:bg-yellow-500 transition">Comenzar Ahora</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-semibold mb-4">Gestión de Gastos</h3>
                    <p class="text-gray-400">Tu solución integral para la gestión financiera empresarial con tecnología de IA.</p>
                </div>
                <div>
                    <h3 class="text-2xl font-semibold mb-4">Enlaces Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-yellow-400 transition">Inicio</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-yellow-400 transition">Cargar Boleta</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-yellow-400 transition">Reportes</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-2xl font-semibold mb-4">Soporte</h3>
                    <ul class="space-y-2">
                        <li><a href="mailto:jhordangonzalo234@gmail.com" class="text-gray-400 hover:text-yellow-400 transition">jhordangonzalo234@gmail.com</a></li>
                        <li><a href="https://wa.me/51906879929" class="text-gray-400 hover:text-yellow-400 transition">+51 906 879 929</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-2xl font-semibold mb-4">Síguenos</h3>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/profile.php?id=61567210615212" class="text-gray-400 hover:text-yellow-400 transition"><i class="fab fa-facebook-f text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-yellow-400 transition"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="https://www.linkedin.com/in/robyn-gonzalo-tarazona-827031335/" class="text-gray-400 hover:text-yellow-400 transition"><i class="fab fa-linkedin-in text-xl"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-8 text-center text-gray-400">
                © 2025 King Soft. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="script.js"></script>
</body>
</html>