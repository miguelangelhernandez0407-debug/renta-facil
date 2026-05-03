<?php if (!isset($_SESSION)) session_start(); ?>
<?php
$no_leidas = 0;
if (isset($_SESSION['usuario'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/renta-facil/conexion.php';
    $id_usuario = $_SESSION['usuario'];
    $res = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM notificacion WHERE id_usuario=$id_usuario AND leida=0"));
    $no_leidas = $res['total'];
}
?>
<style>
.navbar { position: relative; z-index: 1000; }
.nav-menu { display: flex; align-items: center; gap: 4px; }
.nav-item { position: relative; }
.nav-item > a, .nav-trigger {
    color: white; text-decoration: none;
    padding: 8px 14px; border-radius: 6px;
    font-size: 14px; font-weight: 500;
    display: flex; align-items: center; gap: 6px;
    cursor: pointer; background: none; border: none;
    transition: background 0.2s;
}
.nav-item > a:hover, .nav-trigger:hover { background: rgba(255,255,255,0.15); }
.nav-trigger::after { content: '▾'; font-size: 11px; opacity: 0.8; }
.dropdown {
    display: none; position: absolute; top: calc(100% + 8px); right: 0;
    background: white; border-radius: 10px; min-width: 200px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    overflow: hidden; z-index: 999;
}
.dropdown.open { display: block; }
.dropdown a {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; color: #333; text-decoration: none;
    font-size: 14px; transition: background 0.15s;
    border-bottom: 1px solid #f5f5f5;
}
.dropdown a:last-child { border-bottom: none; }
.dropdown a:hover { background: #f0f4ff; color: #1a73e8; }
.dropdown .divider { height: 1px; background: #eee; margin: 4px 0; }
.notif-badge {
    background: #e53935; color: white;
    font-size: 10px; padding: 1px 6px;
    border-radius: 10px; font-weight: 700;
}
.nav-user {
    color: white; font-size: 14px; font-weight: 500;
    padding: 8px 14px;
}
.nav-trigger.active { background: rgba(255,255,255,0.2); }
</style>

<nav class="navbar">
    <a href="/renta-facil/modules/propiedades/listar.php" style="color:white;text-decoration:none">
        <h1 style="display:inline">Renta Fácil</h1>
    </a>
    <div class="nav-menu">
        <?php if (isset($_SESSION['usuario'])): ?>

            <?php if ($_SESSION['rol'] === 'administrador'): ?>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Gestión</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/gestion/dashboard.php">📊 Dashboard</a>
                        <a href="/renta-facil/modules/gestion/panel.php">🏠 Propiedades</a>
                        <a href="/renta-facil/modules/gestion/usuarios.php">👥 Usuarios</a>
                        <a href="/renta-facil/modules/gestion/crear_contrato.php">📄 Contratos</a>
                    </div>
                </div>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Mi cuenta</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/auth/perfil.php">👤 Mi perfil</a>
                        <a href="/renta-facil/modules/notificaciones/notificaciones.php">🔔 Notificaciones <?php if ($no_leidas > 0): ?><span class="notif-badge"><?= $no_leidas ?></span><?php endif; ?></a>
                        <div class="divider"></div>
                        <a href="/renta-facil/modules/auth/cerrar_sesion.php" style="color:#e53935">🚪 Cerrar sesión</a>
                    </div>
                </div>

            <?php elseif ($_SESSION['rol'] === 'arrendador'): ?>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Propiedades</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/propiedades/listar.php">🏘️ Inicio</a>
                        <a href="/renta-facil/modules/propiedades/mis_propiedades.php">📋 Mis propiedades</a>
                        <a href="/renta-facil/modules/propiedades/crear.php">➕ Publicar propiedad</a>
                        <a href="/renta-facil/modules/verificacion/verificar.php">✅ Verificacion</a>
                    </div>
                </div>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Buscar</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/busqueda/buscar.php">🔎 Buscar propiedades</a>
                    </div>
                </div>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Mi cuenta</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/auth/perfil.php">👤 Mi perfil</a>
                        <a href="/renta-facil/modules/notificaciones/notificaciones.php">🔔 Notificaciones <?php if ($no_leidas > 0): ?><span class="notif-badge"><?= $no_leidas ?></span><?php endif; ?></a>
                        <div class="divider"></div>
                        <a href="/renta-facil/modules/auth/cerrar_sesion.php" style="color:#e53935">🚪 Cerrar sesión</a>
                    </div>
                </div>

            <?php elseif ($_SESSION['rol'] === 'arrendatario'): ?>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Propiedades</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/propiedades/listar.php">🏘️ Inicio</a>
                        <a href="/renta-facil/modules/busqueda/buscar.php">🔎 Buscar</a>
                    </div>
                </div>
                <div class="nav-item">
                    <span class="nav-trigger" onclick="toggleMenu(this)">Mi cuenta</span>
                    <div class="dropdown">
                        <a href="/renta-facil/modules/arrendatarios/validar.php">📋 Validacion</a>
                        <a href="/renta-facil/modules/pagos/pagar.php">💳 Pagos</a>
                        <a href="/renta-facil/modules/seguridad/reportar.php">🚨 Reportar</a>
                        <a href="/renta-facil/modules/auth/perfil.php">👤 Mi perfil</a>
                        <a href="/renta-facil/modules/notificaciones/notificaciones.php">🔔 Notificaciones <?php if ($no_leidas > 0): ?><span class="notif-badge"><?= $no_leidas ?></span><?php endif; ?></a>
                        <div class="divider"></div>
                        <a href="/renta-facil/modules/auth/cerrar_sesion.php" style="color:#e53935">🚪 Cerrar sesión</a>
                    </div>
                </div>
            <?php endif; ?>

            <span class="nav-user">Hola, <?= $_SESSION['nombre'] ?></span>

        <?php endif; ?>
    </div>
</nav>

<script>
function toggleMenu(trigger) {
    const dropdown = trigger.nextElementSibling;
    const isOpen = dropdown.classList.contains('open');
    
    // Cerrar todos los dropdowns
    document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
    document.querySelectorAll('.nav-trigger').forEach(t => t.classList.remove('active'));
    
    // Abrir el clickeado si estaba cerrado
    if (!isOpen) {
        dropdown.classList.add('open');
        trigger.classList.add('active');
    }
}

// Cerrar al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.nav-item')) {
        document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
        document.querySelectorAll('.nav-trigger').forEach(t => t.classList.remove('active'));
    }
});
</script>