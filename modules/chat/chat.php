<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];
$id_propiedad = intval($_GET['propiedad'] ?? 0);
$id_otro = intval($_GET['usuario'] ?? 0);

if (!$id_propiedad || !$id_otro) {
    header("Location: ../propiedades/listar.php");
    exit();
}

$propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, pub.precio_mensual FROM propiedad p INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad WHERE p.id_propiedad = $id_propiedad"));
$otro_usuario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id_usuario, nombres, apellidos, rol FROM usuario WHERE id_usuario = $id_otro"));

if (!$propiedad || !$otro_usuario) {
    header("Location: ../propiedades/listar.php");
    exit();
}

// Enviar mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['mensaje'])) {
    $contenido = mysqli_real_escape_string($conexion, $_POST['mensaje']);
    mysqli_query($conexion, "INSERT INTO mensaje (contenido, id_emisor, id_receptor, id_propiedad) VALUES ('$contenido', $id_usuario, $id_otro, $id_propiedad)");
    header("Location: chat.php?propiedad=$id_propiedad&usuario=$id_otro");
    exit();
}

// Marcar mensajes como leídos
mysqli_query($conexion, "UPDATE mensaje SET leido=1 WHERE id_receptor=$id_usuario AND id_emisor=$id_otro AND id_propiedad=$id_propiedad");

// Obtener mensajes
$mensajes = mysqli_query($conexion, "SELECT m.*, u.nombres FROM mensaje m INNER JOIN usuario u ON m.id_emisor = u.id_usuario WHERE id_propiedad=$id_propiedad AND ((id_emisor=$id_usuario AND id_receptor=$id_otro) OR (id_emisor=$id_otro AND id_receptor=$id_usuario)) ORDER BY m.fecha ASC");

$iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
$icono = $iconos[$propiedad['tipo_propiedad']] ?? '🏠';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body { background: #f0f4ff; }
        .chat-wrapper {
            max-width: 700px; margin: 24px auto; padding: 0 16px;
        }
        .chat-header {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            border-radius: 16px 16px 0 0;
            padding: 18px 24px;
            display: flex; align-items: center; gap: 16px;
            color: white;
        }
        .chat-header .prop-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .chat-header .prop-info h3 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .chat-header .prop-info p { font-size: 13px; opacity: 0.8; }
        .chat-with {
            background: rgba(255,255,255,0.15);
            border-radius: 20px; padding: 4px 12px;
            font-size: 12px; margin-top: 6px; display: inline-block;
        }
        .chat-body {
            background: white;
            min-height: 400px; max-height: 500px;
            overflow-y: auto; padding: 20px;
            border-left: 1px solid #e8f0fe;
            border-right: 1px solid #e8f0fe;
        }
        .chat-body::-webkit-scrollbar { width: 4px; }
        .chat-body::-webkit-scrollbar-track { background: #f0f4ff; }
        .chat-body::-webkit-scrollbar-thumb { background: #c5d8fb; border-radius: 2px; }
        .msg-group { margin-bottom: 16px; }
        .msg-bubble {
            max-width: 75%; padding: 10px 14px;
            border-radius: 16px; font-size: 14px;
            line-height: 1.5; position: relative;
            word-wrap: break-word;
        }
        .msg-sent {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white; margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        .msg-received {
            background: #f0f4ff; color: #1a1a2e;
            border-bottom-left-radius: 4px;
        }
        .msg-meta {
            font-size: 11px; margin-top: 4px;
            color: #999; text-align: right;
        }
        .msg-meta.received { text-align: left; }
        .msg-name {
            font-size: 11px; font-weight: 600;
            color: #1a73e8; margin-bottom: 4px;
        }
        .chat-footer {
            background: white;
            border: 1px solid #e8f0fe;
            border-top: none;
            border-radius: 0 0 16px 16px;
            padding: 16px;
        }
        .chat-input-row {
            display: flex; gap: 10px; align-items: flex-end;
        }
        .chat-input-row textarea {
            flex: 1; padding: 12px 14px;
            border: 1.5px solid #e0e0e0; border-radius: 12px;
            font-size: 14px; font-family: 'Inter', Arial, sans-serif;
            resize: none; background: #fafafa;
            transition: border-color 0.2s;
            max-height: 100px;
        }
        .chat-input-row textarea:focus {
            outline: none; border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
            background: white;
        }
        .chat-input-row button {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white; border: none; cursor: pointer;
            font-size: 20px; display: flex; align-items: center;
            justify-content: center; flex-shrink: 0;
            transition: opacity 0.2s;
        }
        .chat-input-row button:hover { opacity: 0.9; }
        .empty-chat {
            text-align: center; padding: 60px 20px; color: #888;
        }
        .empty-chat .icon { font-size: 48px; margin-bottom: 12px; }
        .back-btn { margin-bottom: 16px; display: inline-block; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="chat-wrapper">
        <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $id_propiedad ?>" class="btn btn-primary back-btn">← Volver</a>

        <!-- HEADER -->
        <div class="chat-header">
            <div class="prop-icon"><?= $icono ?></div>
            <div class="prop-info">
                <h3><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?></h3>
                <p>$<?= number_format($propiedad['precio_mensual'], 0, ',', '.') ?>/mes</p>
                <span class="chat-with">💬 Conversando con <?= $otro_usuario['nombres'] ?> <?= $otro_usuario['apellidos'] ?> (<?= ucfirst($otro_usuario['rol']) ?>)</span>
            </div>
        </div>

        <!-- MENSAJES -->
        <div class="chat-body" id="chatBody">
            <?php if (mysqli_num_rows($mensajes) === 0): ?>
                <div class="empty-chat">
                    <div class="icon">💬</div>
                    <p>No hay mensajes aún. ¡Sé el primero en escribir!</p>
                </div>
            <?php else: ?>
                <?php while ($m = mysqli_fetch_assoc($mensajes)):
                    $es_mio = $m['id_emisor'] == $id_usuario;
                ?>
                <div class="msg-group" style="display:flex;flex-direction:column;align-items:<?= $es_mio ? 'flex-end' : 'flex-start' ?>">
                    <?php if (!$es_mio): ?>
                        <div class="msg-name"><?= $m['nombres'] ?></div>
                    <?php endif; ?>
                    <div class="msg-bubble <?= $es_mio ? 'msg-sent' : 'msg-received' ?>">
                        <?= htmlspecialchars($m['contenido']) ?>
                    </div>
                    <div class="msg-meta <?= $es_mio ? '' : 'received' ?>">
                        <?= date('d/m H:i', strtotime($m['fecha'])) ?>
                        <?= $es_mio ? ($m['leido'] ? ' ✓✓' : ' ✓') : '' ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- INPUT -->
        <div class="chat-footer">
            <form method="POST" class="chat-input-row">
                <textarea name="mensaje" placeholder="Escribe un mensaje..." rows="1" required
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit()}"></textarea>
                <button type="submit">➤</button>
            </form>
        </div>
    </div>

    <script>
        // Auto scroll al fondo
        const chatBody = document.getElementById('chatBody');
        chatBody.scrollTop = chatBody.scrollHeight;

        // Auto resize textarea
        const textarea = document.querySelector('textarea[name="mensaje"]');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    </script>
</body>
</html>