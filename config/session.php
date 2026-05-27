<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: config/session.php                                ║
// ║  PROPÓSITO: Gestionar las sesiones de usuario               ║
// ║                                                              ║
// ║  Una "sesión" es como una pulsera de acceso en un evento:   ║
// ║  cuando el usuario inicia sesión, el servidor le da una      ║
// ║  pulsera única (cookie) que guarda en su navegador.          ║
// ║  En cada página, el servidor lee esa pulsera para saber      ║
// ║  quién es el usuario sin pedirle la contraseña cada vez.     ║
// ║                                                              ║
// ║  También aquí están los "flash messages": mensajes de        ║
// ║  confirmación o error que aparecen UNA sola vez.             ║
// ║                                                              ║
// ║  Y la protección CSRF: un código secreto que se pone en      ║
// ║  todos los formularios para evitar que sitios externos       ║
// ║  puedan enviar formularios falsos en nombre del usuario.     ║
// ╚══════════════════════════════════════════════════════════════╝

// ── Iniciar la sesión ────────────────────────────────────────────
// PHP_SESSION_NONE significa que todavía NO hay sesión activa.
// Solo iniciamos una si no existe ya (evita iniciarla dos veces).
if (session_status() === PHP_SESSION_NONE) {
    // Configuramos cómo se maneja la cookie de sesión en el navegador del usuario.
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 7, // La sesión dura 7 días (en segundos)
        'path'     => '/',               // La cookie vale para todo el sitio
        'secure'   => false,             // true en producción con HTTPS (conexión cifrada)
        'httponly' => true,              // JavaScript NO puede leer esta cookie (protección XSS)
        'samesite' => 'Strict',          // La cookie solo se envía desde el mismo sitio (protección CSRF)
    ]);
    // Iniciamos la sesión: PHP crea un ID único y lo guarda en una cookie en el navegador.
    session_start();
}

// ═══════════════════════════════════════════════════════════════
//  FUNCIONES DE AUTENTICACIÓN
//  Responden preguntas clave: ¿Está logueado? ¿Es admin?
// ═══════════════════════════════════════════════════════════════

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: estaLogueado()
// ──────────────────────────────────────────────────────────────
// Revisa si hay un "usuario_id" guardado en la sesión actual.
// Si existe, significa que el usuario inició sesión correctamente.
// Devuelve: true (sí está logueado) o false (no lo está).
function estaLogueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: esAdmin()
// ──────────────────────────────────────────────────────────────
// Verifica si el usuario logueado tiene rol de administrador.
// El rol se guarda en la sesión al hacer login (viene de la BD).
// Devuelve: true si el rol guardado es exactamente 'admin'.
function esAdmin(): bool
{
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: usuarioActual()
// ──────────────────────────────────────────────────────────────
// Devuelve un array (diccionario) con los datos del usuario logueado,
// o null si nadie está logueado.
// Se usa en el header para mostrar el nombre del usuario, por ejemplo.
function usuarioActual(): ?array
{
    // Si no está logueado, no hay datos que devolver.
    if (!estaLogueado()) return null;

    // Devolvemos los datos que guardamos en la sesión al hacer login.
    return [
        'id'       => $_SESSION['usuario_id'],
        'nombre'   => $_SESSION['usuario_nombre'],
        'apellido' => $_SESSION['usuario_apellido'],
        'email'    => $_SESSION['usuario_email'],
        'rol'      => $_SESSION['usuario_rol'],
    ];
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: requiereLogin()
// ──────────────────────────────────────────────────────────────
// Si el usuario NO está logueado, lo manda a la página de login
// y detiene la ejecución del resto de la página (exit).
// Se usa al inicio de páginas privadas como carrito o checkout.
function requiereLogin(string $redirect = 'auth.php'): void
{
    if (!estaLogueado()) {
        header("Location: $redirect");  // Redirigir al navegador
        exit;                           // Detener la ejecución de PHP
    }
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: requiereAdmin()
// ──────────────────────────────────────────────────────────────
// Si el usuario NO es administrador, lo manda al inicio.
// Se usa al inicio del panel de administración.
function requiereAdmin(string $redirect = '../index.php'): void
{
    if (!esAdmin()) {
        header("Location: $redirect");
        exit;
    }
}

// ═══════════════════════════════════════════════════════════════
//  PROTECCIÓN CSRF (Cross-Site Request Forgery)
//  ¿Qué es? Un ataque donde un sitio malicioso intenta hacer que
//  el navegador del usuario envíe formularios sin que él lo sepa.
//  La solución: cada formulario lleva un código secreto único que
//  el sitio verifica antes de procesar cualquier acción.
// ═══════════════════════════════════════════════════════════════

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: generarCSRF()
// ──────────────────────────────────────────────────────────────
// Genera un token (código secreto) aleatorio de 64 caracteres
// y lo guarda en la sesión. Si ya existe uno, lo devuelve tal cual.
// Este token se pone como campo oculto en todos los formularios.
function generarCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes(32) genera 32 bytes aleatorios seguros.
        // bin2hex los convierte a texto hexadecimal (64 caracteres).
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: validarCSRF()
// ──────────────────────────────────────────────────────────────
// Compara el token que llegó con el formulario contra el que está
// guardado en la sesión. Si coinciden, el formulario es legítimo.
// hash_equals evita ataques de "timing" (comparación a tiempo constante).
function validarCSRF(string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: rotarCSRF()
// ──────────────────────────────────────────────────────────────
// Genera un token nuevo después de cada acción exitosa.
// Así un token usado no puede reutilizarse para otra acción.
function rotarCSRF(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ═══════════════════════════════════════════════════════════════
//  FLASH MESSAGES (Mensajes de un solo uso)
//  Son mensajes de éxito, error o advertencia que se muestran
//  una sola vez al usuario (por ejemplo: "Producto agregado al carrito").
//  Después de mostrarse, desaparecen automáticamente.
//  Funcionan guardando el mensaje en sesión, mostrándolo en la
//  siguiente página, y borrándolo de inmediato.
// ═══════════════════════════════════════════════════════════════

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: flash()
// ──────────────────────────────────────────────────────────────
// Guarda un mensaje en la sesión para mostrarlo en la próxima página.
// $tipo puede ser: 'success' (verde), 'error' (rojo), 'warning' (amarillo), 'info' (azul)
// $mensaje es el texto que verá el usuario.
function flash(string $tipo, string $mensaje): void
{
    // $_SESSION['flash'] es un array donde acumulamos mensajes pendientes.
    // El [] al final añade un nuevo elemento al array (no sobreescribe los anteriores).
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: obtenerFlash()
// ──────────────────────────────────────────────────────────────
// Devuelve todos los mensajes flash pendientes y los BORRA de la sesión.
// Así cada mensaje se muestra exactamente una vez.
// El header.php llama a esta función para mostrar los mensajes.
function obtenerFlash(): array
{
    $mensajes = $_SESSION['flash'] ?? [];  // ?? [] = si no existe, usar array vacío
    unset($_SESSION['flash']);             // Borramos los mensajes de la sesión
    return $mensajes;
}

// ═══════════════════════════════════════════════════════════════
//  LOGIN Y LOGOUT
// ═══════════════════════════════════════════════════════════════

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: iniciarSesionUsuario()
// ──────────────────────────────────────────────────────────────
// Se llama cuando el usuario inicia sesión exitosamente.
// Guarda los datos del usuario en la sesión del servidor.
// También regenera el ID de sesión para mayor seguridad
// (evita que alguien que tenga el ID antiguo pueda "robar" la sesión).
function iniciarSesionUsuario(array $usuario): void
{
    // Cambia el ID de sesión por uno nuevo (protección contra session fixation).
    session_regenerate_id(true);

    // Guardamos los datos del usuario en la sesión.
    // Estos datos estarán disponibles en TODAS las páginas mientras el usuario no cierre sesión.
    $_SESSION['usuario_id']       = (int) $usuario['id'];
    $_SESSION['usuario_nombre']   = $usuario['nombre'];
    $_SESSION['usuario_apellido'] = $usuario['apellido'];
    $_SESSION['usuario_email']    = $usuario['email'];
    $_SESSION['usuario_rol']      = $usuario['rol'];  // 'cliente' o 'admin'
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: cerrarSesion()
// ──────────────────────────────────────────────────────────────
// Se llama cuando el usuario presiona "Salir" o cuando expira la sesión.
// Borra TODOS los datos de la sesión y destruye la cookie del navegador.
// Después de esto, el usuario es un visitante anónimo nuevamente.
function cerrarSesion(): void
{
    // Vaciamos el array de sesión (borramos todos los datos guardados).
    $_SESSION = [];

    // Si hay una cookie de sesión activa en el navegador, la eliminamos.
    // Esto se hace poniendo la fecha de expiración en el pasado.
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(), '',      // Nombre de la cookie, valor vacío
            time() - 42000,          // Fecha en el pasado = la cookie expira de inmediato
            $p['path'], $p['domain'],
            $p['secure'], $p['httponly']
        );
    }

    // Destruimos los datos de sesión en el servidor.
    session_destroy();
}
