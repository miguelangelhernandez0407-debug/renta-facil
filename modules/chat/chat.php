<?php
session_start();
require_once '../../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];
$id_propiedad = intval($_GET['propiedad'] ?? $_POST['id_propiedad'] ?? 0);

if (!$id_propiedad) {
    header("Location: ../propiedades/listar.php");
    exit();
}

$propiedad = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT p.*, pub.precio_mensual, pub.descripcion, u.nombres, u.apellidos, u.correo, u.telefono
    FROM propiedad p 
    INNER JOIN publicacion pub ON p.id_propiedad = pub.id_propiedad
    LEFT JOIN usuario u ON u.rol = 'arrendador'
    WHERE p.id_propiedad = $id_propiedad LIMIT 1"));

if (!$propiedad) {
    header("Location: ../propiedades/listar.php");
    exit();
}

function responderBot($pregunta, $prop) {
    $p = strtolower($pregunta);
    $precio = '$' . number_format($prop['precio_mensual'], 0, ',', '.');

    if (preg_match('/^(hola|buenas|buenos|hey|hi|saludos)/i', $p))
        return "¡Hola! 👋 Soy el asistente de Renta Fácil. Puedo ayudarte con información sobre este {$prop['tipo_propiedad']} en {$prop['barrio']}. ¿Qué quieres saber?";

    if (preg_match('/precio|valor|costo|cuanto|cuánto|arriendo|mensual/i', $p))
        return "💰 El precio mensual es de **{$precio}**. ¿Tienes alguna otra pregunta?";

    if (preg_match('/habitacion|habitaciones|cuartos|cuarto|dormitorio/i', $p))
        return "🛏 Este {$prop['tipo_propiedad']} tiene **{$prop['habitaciones']} habitación(es)**. ¿Quieres saber algo más?";

    if (preg_match('/baño|baños|sanitario|wc/i', $p))
        return "🚿 El inmueble cuenta con **{$prop['baños']} baño(s)**. ¿Necesitas más información?";

    if (preg_match('/area|área|metros|m2|tamaño|espacio/i', $p))
        return "📐 El área total es de **{$prop['area_m2']} m²**. Es un espacio " . ($prop['area_m2'] > 80 ? 'amplio' : 'acogedor') . ".";

    if (preg_match('/donde|dónde|ubicacion|ubicación|direccion|dirección|barrio|sector/i', $p))
        return "📍 Está en el barrio **{$prop['barrio']}**, {$prop['ciudad']}. Dirección: {$prop['direcion']}.";

    if (preg_match('/parqueadero|garaje|garage|carro|parking/i', $p))
        return $prop['parqueadero'] === 'si'
            ? "🚗 ¡Sí! Este inmueble **incluye parqueadero**."
            : "❌ Este inmueble **no incluye parqueadero**.";

    if (preg_match('/estrato/i', $p))
        return "🏙 El inmueble es de **estrato {$prop['estrato']}**.";

    if (preg_match('/descripcion|descripción|caracteristicas|detalles/i', $p))
        return "📝 " . $prop['descripcion'] . "\n\n¿Hay algo específico que quieras saber?";

    if (preg_match('/contacto|contactar|arrendador|dueño|propietario|telefono|teléfono|correo|email/i', $p))
        return "📞 Puedes contactar al arrendador:\n\n👤 **{$prop['nombres']} {$prop['apellidos']}**\n📧 {$prop['correo']}\n📱 {$prop['telefono']}";

    if (preg_match('/disponible|disponibilidad|libre/i', $p))
        return "✅ Esta propiedad está **disponible** para arrendar. Contacta al arrendador para coordinar una visita.";

    if (preg_match('/mascota|mascotas|perro|gato/i', $p))
        return "🐾 Para consultar la política de mascotas, contacta al arrendador: **{$prop['nombres']}** al {$prop['telefono']}.";

    if (preg_match('/visita|visitar|ver|conocer/i', $p))
        return "🏠 Para agendar una visita, contacta a **{$prop['nombres']}** al {$prop['telefono']} o al correo {$prop['correo']}.";

    if (preg_match('/servicios|agua|luz|internet|gas|incluye/i', $p))
        return "💡 Para saber qué servicios están incluidos, consulta con el arrendador al {$prop['telefono']}.";

    if (preg_match('/negociar|negociable|descuento|rebaja/i', $p))
        return "💬 El precio es de {$precio}/mes. Para negociaciones, contacta directamente al arrendador.";

    $respuestas = [
        "Esa es una buena pregunta 🤔 Te recomiendo contactar al arrendador **{$prop['nombres']}** al {$prop['telefono']}.",
        "Para esa información específica, habla con el arrendador al 📱 {$prop['telefono']}.",
        "Puedo ayudarte con información básica. Para más detalles, contacta a **{$prop['nombres']}** al {$prop['correo']}."
    ];
    return $respuestas[array_rand($respuestas)];
}

// Procesar AJAX — responder solo JSON y salir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $respuesta = $mensaje ? responderBot($mensaje, $propiedad) : 'Por favor escribe una pregunta.';
    header('Content-Type: application/json');
    echo json_encode(['respuesta' => $respuesta]);
    exit();
}

$iconos = ['casa'=>'🏠','apartamento'=>'🏢','habitacion'=>'🛏','local'=>'🏪'];
$icono = $iconos[$propiedad['tipo_propiedad']] ?? '🏠';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistente IA - Renta Fácil</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body { background: #f0f4ff; }
        .chat-wrapper { max-width: 700px; margin: 24px auto; padding: 0 16px; }
        .chat-header {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            border-radius: 16px 16px 0 0;
            padding: 18px 24px;
            display: flex; align-items: center; gap: 16px; color: white;
        }
        .chat-header .prop-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .chat-header .prop-info h3 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .chat-header .prop-info p { font-size: 13px; opacity: 0.8; }
        .bot-badge {
            background: rgba(255,255,255,0.2); border-radius: 20px;
            padding: 4px 12px; font-size: 12px; margin-top: 6px; display: inline-block;
        }
        .online-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #4caf50; display: inline-block; margin-right: 4px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
        .chat-body {
            background: white; min-height: 450px; max-height: 500px;
            overflow-y: auto; padding: 20px;
            border-left: 1px solid #e8f0fe; border-right: 1px solid #e8f0fe;
        }
        .chat-body::-webkit-scrollbar { width: 4px; }
        .chat-body::-webkit-scrollbar-thumb { background: #c5d8fb; border-radius: 2px; }
        .msg-group { margin-bottom: 16px; display: flex; flex-direction: column; }
        .msg-bubble {
            max-width: 78%; padding: 11px 15px; border-radius: 16px;
            font-size: 14px; line-height: 1.6; word-wrap: break-word;
        }
        .msg-sent {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white; align-self: flex-end; border-bottom-right-radius: 4px;
        }
        .msg-received {
            background: #f0f4ff; color: #1a1a2e;
            align-self: flex-start; border-bottom-left-radius: 4px;
        }
        .msg-meta { font-size: 11px; color: #999; margin-top: 4px; }
        .msg-meta.sent { text-align: right; align-self: flex-end; }
        .msg-meta.received { align-self: flex-start; }
        .bot-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; margin-bottom: 4px; align-self: flex-start;
        }
        .typing-indicator {
            display: none; align-self: flex-start;
            background: #f0f4ff; border-radius: 16px;
            padding: 12px 16px; margin-bottom: 16px;
        }
        .typing-dots { display: flex; gap: 4px; align-items: center; }
        .typing-dots span {
            width: 8px; height: 8px; border-radius: 50%;
            background: #1a73e8; animation: bounce 1.2s infinite;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }
        .chat-footer {
            background: white; border: 1px solid #e8f0fe;
            border-top: none; border-radius: 0 0 16px 16px; padding: 16px;
        }
        .sugerencias { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .sugerencia {
            background: #e8f0fe; color: #1a73e8; border: 1px solid #c5d8fb;
            border-radius: 20px; padding: 6px 14px; font-size: 12px;
            cursor: pointer; font-weight: 500; transition: all 0.2s;
        }
        .sugerencia:hover { background: #1a73e8; color: white; }
        .chat-input-row { display: flex; gap: 10px; align-items: flex-end; }
        .chat-input-row textarea {
            flex: 1; padding: 12px 14px;
            border: 1.5px solid #e0e0e0; border-radius: 12px;
            font-size: 14px; font-family: 'Inter', Arial, sans-serif;
            resize: none; background: #fafafa; max-height: 100px;
            transition: border-color 0.2s;
        }
        .chat-input-row textarea:focus {
            outline: none; border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1); background: white;
        }
        .chat-input-row button {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white; border: none; cursor: pointer; font-size: 20px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: opacity 0.2s;
        }
        .chat-input-row button:hover { opacity: 0.9; }
        .back-btn { margin-bottom: 16px; display: inline-block; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="chat-wrapper">
        <a href="/renta-facil/modules/propiedades/detalle.php?id=<?= $id_propiedad ?>" class="btn btn-primary back-btn">← Volver</a>

        <div class="chat-header">
            <div class="prop-icon"><?= $icono ?></div>
            <div class="prop-info">
                <h3><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?></h3>
                <p>$<?= number_format($propiedad['precio_mensual'], 0, ',', '.') ?>/mes · <?= $propiedad['ciudad'] ?></p>
                <span class="bot-badge"><span class="online-dot"></span>🤖 Asistente IA — En línea</span>
            </div>
        </div>

        <div class="chat-body" id="chatBody">
            <div class="msg-group">
                <div class="bot-avatar">🤖</div>
                <div class="msg-bubble msg-received">
                    ¡Hola! 👋 Soy el asistente virtual de <strong>Renta Fácil</strong>. Puedo responderte preguntas sobre este inmueble:<br><br>
                    🏠 <strong><?= ucfirst($propiedad['tipo_propiedad']) ?> en <?= $propiedad['barrio'] ?></strong><br>
                    📍 <?= $propiedad['ciudad'] ?><br>
                    💰 $<?= number_format($propiedad['precio_mensual'], 0, ',', '.') ?>/mes<br><br>
                    ¿En qué te puedo ayudar?
                </div>
                <div class="msg-meta received">Ahora</div>
            </div>
            <div class="typing-indicator" id="typingIndicator">
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        </div>

        <div class="chat-footer">
            <div class="sugerencias">
                <span class="sugerencia" onclick="enviarSugerencia(this)">¿Cuántas habitaciones?</span>
                <span class="sugerencia" onclick="enviarSugerencia(this)">¿Tiene parqueadero?</span>
                <span class="sugerencia" onclick="enviarSugerencia(this)">¿Cuál es el precio?</span>
                <span class="sugerencia" onclick="enviarSugerencia(this)">¿Cómo contactar al arrendador?</span>
                <span class="sugerencia" onclick="enviarSugerencia(this)">¿Está disponible?</span>
                <span class="sugerencia" onclick="enviarSugerencia(this)">¿Admite mascotas?</span>
            </div>
            <div class="chat-input-row">
                <textarea id="msgInput" placeholder="Escribe tu pregunta..." rows="1"></textarea>
                <button onclick="enviarMensaje()">➤</button>
            </div>
        </div>
    </div>

    <script>
        const CHAT_URL = '/renta-facil/modules/chat/chat.php?propiedad=<?= $id_propiedad ?>';
        const chatBody = document.getElementById('chatBody');
        const typingIndicator = document.getElementById('typingIndicator');
        const msgInput = document.getElementById('msgInput');

        msgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                enviarMensaje();
            }
        });

        msgInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        function enviarSugerencia(el) {
            msgInput.value = el.textContent;
            enviarMensaje();
        }

        function agregarMensaje(texto, esMio) {
            const group = document.createElement('div');
            group.className = 'msg-group';

            if (!esMio) {
                const avatar = document.createElement('div');
                avatar.className = 'bot-avatar';
                avatar.textContent = '🤖';
                group.appendChild(avatar);
            }

            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble ' + (esMio ? 'msg-sent' : 'msg-received');
            bubble.innerHTML = texto.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
            group.appendChild(bubble);

            const meta = document.createElement('div');
            meta.className = 'msg-meta ' + (esMio ? 'sent' : 'received');
            meta.textContent = new Date().toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit'});
            group.appendChild(meta);

            chatBody.insertBefore(group, typingIndicator);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        async function enviarMensaje() {
            const texto = msgInput.value.trim();
            if (!texto) return;

            msgInput.value = '';
            msgInput.style.height = 'auto';
            agregarMensaje(texto, true);

            typingIndicator.style.display = 'flex';
            chatBody.scrollTop = chatBody.scrollHeight;

            await new Promise(r => setTimeout(r, 700 + Math.random() * 500));

            try {
                const formData = new FormData();
                formData.append('mensaje', texto);

                const response = await fetch(CHAT_URL, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                typingIndicator.style.display = 'none';
                agregarMensaje(data.respuesta, false);
            } catch (error) {
                typingIndicator.style.display = 'none';
                agregarMensaje('Lo siento, hubo un error. Por favor intenta de nuevo. 😔', false);
            }
        }
    </script>
</body>
</html>