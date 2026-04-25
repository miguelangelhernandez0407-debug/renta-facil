<?php if (!isset($_SESSION)) session_start(); ?>
<nav class="navbar">
    <a href="/renta-facil/modules/propiedades/listar.php" style="color:white;text-decoration:none">
        <h1 style="display:inline">Renta Fácil</h1>
    </a>
    <div>
        <?php if (isset($_SESSION['usuario'])): ?>
            <span style="color:white;margin-right:16px">Hola, <?= $_SESSION['nombre'] ?></span>

            <?php if ($_SESSION['rol'] === 'administrador'): ?>
                <a href="/renta-facil/modules/gestion/panel.php">Propiedades</a>
                <a href="/renta-facil/modules/gestion/usuarios.php">Usuarios</a>

            <?php elseif ($_SESSION['rol'] === 'arrendador'): ?>
                <a href="/renta-facil/modules/propiedades/listar.php">Inicio</a>
                <a href="/renta-facil/modules/propiedades/mis_propiedades.php">Mis propiedades</a>
                <a href="/renta-facil/modules/propiedades/crear.php">Publicar</a>
                <a href="/renta-facil/modules/busqueda/buscar.php">Buscar</a>
                <a href="/renta-facil/modules/verificacion/verificar.php">Verificacion</a>

            <?php elseif ($_SESSION['rol'] === 'arrendatario'): ?>
                <a href="/renta-facil/modules/propiedades/listar.php">Inicio</a>
                <a href="/renta-facil/modules/busqueda/buscar.php">Buscar</a>
                <a href="/renta-facil/modules/arrendatarios/validar.php">Mi perfil</a>
                <a href="/renta-facil/modules/pagos/pagar.php">Pagos</a>
                <a href="/renta-facil/modules/seguridad/reportar.php">Reportar</a>
            <?php endif; ?>

            <a href="/renta-facil/modules/auth/cerrar_sesion.php">Cerrar sesion</a>
        <?php endif; ?>
    </div>
</nav>