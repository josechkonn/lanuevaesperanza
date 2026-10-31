<?php
/**
 * API Backend Completo: Chat Comunal con Persistencia de Sesión, IA (DeepSeek/Gemini/Local) y Handoff Humano.
 * EPSDC La Nueva Esperanza.
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/ai_service.php';

$pdo = db();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Leer JSON si se envía cuerpo raw
$inputRaw = file_get_contents('php://input');
$jsonBody = [];
if ($inputRaw) {
    $decoded = json_decode($inputRaw, true);
    if (is_array($decoded)) {
        $jsonBody = $decoded;
        if (!$action && isset($jsonBody['action'])) {
            $action = (string)$jsonBody['action'];
        }
    }
}

// Helpers para extraer parámetros
function getParam(string $key, $default = '') {
    global $jsonBody;
    if (isset($jsonBody[$key])) return $jsonBody[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    if (isset($_GET[$key])) return $_GET[$key];
    return $default;
}

function jsonOut(array $data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($action) {

        // ==========================================
        // 1. INICIALIZAR SESIÓN DE VISITANTE
        // ==========================================
        case 'session_init':
            $uuid = trim((string)getParam('session_uuid'));
            if ($uuid === '' && !empty($_COOKIE['epsdc_chat_uuid'])) {
                $uuid = trim((string)$_COOKIE['epsdc_chat_uuid']);
            }
            if ($uuid === '' || !preg_match('/^[a-zA-Z0-9_-]{10,64}$/', $uuid)) {
                $uuid = 'lne_' . bin2hex(random_bytes(16));
            }

            // Persistir también en cookie de larga duración (1 año)
            setcookie('epsdc_chat_uuid', $uuid, [
                'expires' => time() + 31536000,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            // Buscar si ya existe la sesión
            $stmt = $pdo->prepare('SELECT * FROM chat_sesiones WHERE session_uuid = ? LIMIT 1');
            $stmt->execute([$uuid]);
            $sesion = $stmt->fetch();

            if (!$sesion) {
                // Generar nombre amigable tipo "Visitante #8492"
                $randNum = rand(1000, 9999);
                $nombreVis = 'Visitante #' . $randNum;

                $stmtIns = $pdo->prepare('INSERT INTO chat_sesiones (session_uuid, nombre_visitante, estado, ip) VALUES (?, ?, "ia", ?)');
                $stmtIns->execute([$uuid, $nombreVis, $ip]);
                $sesionId = (int)$pdo->lastInsertId();

                // Mensaje de bienvenida inicial
                $org = config_get('nombre_organizacion', 'La Nueva Esperanza');
                $bienvenida = "¡Hola! Le damos una cordial bienvenida a la EPSDC $org en La Grita, Estado Táchira.\n\nEste es nuestro canal de orientación y atención comunal. Con gusto le brindamos información sobre nuestros rubros agrícolas, bolsas y combos familiares disponibles, cronograma de entregas de la semana o los requisitos de arrime para productores y UPF. Si requiere comunicarse con la coordinación, puede solicitarlo en cualquier momento.";
                
                $stmtMsg = $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, "ia", ?)');
                $stmtMsg->execute([$sesionId, $bienvenida]);

                $sesion = [
                    'id' => $sesionId,
                    'session_uuid' => $uuid,
                    'nombre_visitante' => $nombreVis,
                    'estado' => 'ia',
                    'telefono' => null,
                ];
            }

            // Obtener mensajes históricos
            $stmtM = $pdo->prepare('SELECT id, remitente, autor_nombre, mensaje, creado_en FROM chat_mensajes WHERE sesion_id = ? ORDER BY id ASC LIMIT 100');
            $stmtM->execute([$sesion['id']]);
            $mensajes = $stmtM->fetchAll();

            jsonOut([
                'ok' => true,
                'session' => [
                    'id' => (int)$sesion['id'],
                    'uuid' => $sesion['session_uuid'],
                    'nombre' => $sesion['nombre_visitante'],
                    'estado' => $sesion['estado'],
                ],
                'mensajes' => $mensajes,
            ]);
            break;

        // ==========================================
        // 1.1 RESETEAR / NUEVO CHAT DE VISITANTE
        // ==========================================
        case 'reset_session':
            $newUuid = 'lne_' . bin2hex(random_bytes(16));
            setcookie('epsdc_chat_uuid', $newUuid, [
                'expires' => time() + 31536000,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $randNum = rand(1000, 9999);
            $nombreVis = 'Visitante #' . $randNum;

            $stmtIns = $pdo->prepare('INSERT INTO chat_sesiones (session_uuid, nombre_visitante, estado, ip) VALUES (?, ?, "ia", ?)');
            $stmtIns->execute([$newUuid, $nombreVis, $ip]);
            $sesionId = (int)$pdo->lastInsertId();

            $org = config_get('nombre_organizacion', 'La Nueva Esperanza');
            $bienvenida = "¡Hola! Le damos una cordial bienvenida a la EPSDC $org en La Grita, Estado Táchira.\n\nEste es nuestro canal de orientación y atención comunal. Con gusto le brindamos información sobre nuestros rubros agrícolas, bolsas y combos familiares disponibles, cronograma de entregas de la semana o los requisitos de arrime para productores y UPF. Si requiere comunicarse con la coordinación, puede solicitarlo en cualquier momento.";

            $stmtMsg = $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, "ia", ?)');
            $stmtMsg->execute([$sesionId, $bienvenida]);
            $msgId = (int)$pdo->lastInsertId();

            jsonOut([
                'ok' => true,
                'session' => [
                    'id' => $sesionId,
                    'uuid' => $newUuid,
                    'nombre' => $nombreVis,
                    'estado' => 'ia',
                ],
                'mensajes' => [
                    [
                        'id' => $msgId,
                        'remitente' => 'ia',
                        'autor_nombre' => null,
                        'mensaje' => $bienvenida,
                        'creado_en' => date('Y-m-d H:i:s'),
                    ]
                ],
            ]);
            break;

        // ==========================================
        // 2. OBTENER MENSAJES (POLLING VISITANTE)
        // ==========================================
        case 'get_messages':
            $uuid = trim((string)getParam('session_uuid'));
            if ($uuid === '') jsonOut(['ok' => false, 'error' => 'UUID requerido'], 400);

            $stmt = $pdo->prepare('SELECT * FROM chat_sesiones WHERE session_uuid = ? LIMIT 1');
            $stmt->execute([$uuid]);
            $sesion = $stmt->fetch();
            if (!$sesion) jsonOut(['ok' => false, 'error' => 'Sesión no encontrada'], 404);

            $afterId = (int)getParam('after_id', 0);
            if ($afterId > 0) {
                $stmtM = $pdo->prepare('SELECT id, remitente, autor_nombre, mensaje, creado_en FROM chat_mensajes WHERE sesion_id = ? AND id > ? ORDER BY id ASC');
                $stmtM->execute([$sesion['id'], $afterId]);
            } else {
                $stmtM = $pdo->prepare('SELECT id, remitente, autor_nombre, mensaje, creado_en FROM chat_mensajes WHERE sesion_id = ? ORDER BY id ASC LIMIT 100');
                $stmtM->execute([$sesion['id']]);
            }
            $mensajes = $stmtM->fetchAll();

            jsonOut([
                'ok' => true,
                'estado' => $sesion['estado'],
                'mensajes' => $mensajes,
            ]);
            break;

        // ==========================================
        // 3. ENVIAR MENSAJE DE VISITANTE (IA / HUMANO)
        // ==========================================
        case 'send_message':
            $uuid = trim((string)getParam('session_uuid'));
            $texto = trim((string)getParam('mensaje'));

            if ($uuid === '' || $texto === '') {
                jsonOut(['ok' => false, 'error' => 'Datos incompletos'], 400);
            }

            $stmt = $pdo->prepare('SELECT * FROM chat_sesiones WHERE session_uuid = ? LIMIT 1');
            $stmt->execute([$uuid]);
            $sesion = $stmt->fetch();
            if (!$sesion) jsonOut(['ok' => false, 'error' => 'Sesión no encontrada'], 404);

            $sesionId = (int)$sesion['id'];
            $estadoActual = $sesion['estado'];

            // 1. Guardar mensaje del visitante
            $stmtIns = $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, "visitante", ?)');
            $stmtIns->execute([$sesionId, $texto]);
            $userMsgId = (int)$pdo->lastInsertId();

            // 2. Actualizar sesión
            $pdo->prepare('UPDATE chat_sesiones SET ultimo_mensaje = ?, no_leidos_admin = no_leidos_admin + 1, actualizado_en = NOW() WHERE id = ?')
                ->execute([$texto, $sesionId]);

            // 3. Verificar si el mensaje pide explícitamente atención humana
            $solicitaHumano = detect_human_request($texto);

            if ($solicitaHumano && $estadoActual === 'ia') {
                // Pasar a modo humano
                $pdo->prepare('UPDATE chat_sesiones SET estado = "esperando_humano", actualizado_en = NOW() WHERE id = ?')
                    ->execute([$sesionId]);

                $sysMsg = "🔔 Has solicitado atención con un operador humano. Un integrante de la administración de la EPSDC se conectará contigo en breve por este mismo chat. Por favor permanece en esta ventana.";
                $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, "sistema", ?)')->execute([$sesionId, $sysMsg]);
                $sysMsgId = (int)$pdo->lastInsertId();

                jsonOut([
                    'ok' => true,
                    'estado' => 'esperando_humano',
                    'respuesta' => [
                        'id' => $sysMsgId,
                        'remitente' => 'sistema',
                        'mensaje' => $sysMsg,
                        'creado_en' => date('Y-m-d H:i:s'),
                    ]
                ]);
            }

            // Si está en modo IA, generar respuesta inteligente
            if ($estadoActual === 'ia') {
                // Obtener historial reciente para dar contexto a la IA
                $stmtHist = $pdo->prepare('SELECT remitente, mensaje FROM chat_mensajes WHERE sesion_id = ? ORDER BY id DESC LIMIT 8');
                $stmtHist->execute([$sesionId]);
                $historial = array_reverse($stmtHist->fetchAll());

                $aiResult = ai_generate_response($texto, $historial);

                if (!empty($aiResult['requires_handoff'])) {
                    // Pasar a operador humano
                    $pdo->prepare('UPDATE chat_sesiones SET estado = "esperando_humano", actualizado_en = NOW() WHERE id = ?')
                        ->execute([$sesionId]);

                    $remitente = 'sistema';
                } else {
                    $remitente = 'ia';
                }

                $aiMsg = $aiResult['response'];
                $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, ?, ?)')
                    ->execute([$sesionId, $remitente, $aiMsg]);
                $aiMsgId = (int)$pdo->lastInsertId();

                jsonOut([
                    'ok' => true,
                    'estado' => (!empty($aiResult['requires_handoff']) ? 'esperando_humano' : 'ia'),
                    'source' => $aiResult['source'] ?? 'ia',
                    'respuesta' => [
                        'id' => $aiMsgId,
                        'remitente' => $remitente,
                        'mensaje' => $aiMsg,
                        'creado_en' => date('Y-m-d H:i:s'),
                    ]
                ]);
            }

            // Si ya está esperando operador humano o con humano activo, no responde la IA
            jsonOut([
                'ok' => true,
                'estado' => $estadoActual,
                'esperando_humano' => true,
                'user_msg_id' => $userMsgId
            ]);
            break;

        // ==========================================
        // 4. SOLICITAR OPERADOR HUMANO DIRECTAMENTE
        // ==========================================
        case 'request_human':
            $uuid = trim((string)getParam('session_uuid'));
            if ($uuid === '') jsonOut(['ok' => false, 'error' => 'UUID requerido'], 400);

            $stmt = $pdo->prepare('SELECT * FROM chat_sesiones WHERE session_uuid = ? LIMIT 1');
            $stmt->execute([$uuid]);
            $sesion = $stmt->fetch();
            if (!$sesion) jsonOut(['ok' => false, 'error' => 'Sesión no encontrada'], 404);

            $sesionId = (int)$sesion['id'];

            $pdo->prepare('UPDATE chat_sesiones SET estado = "esperando_humano", no_leidos_admin = no_leidos_admin + 1, actualizado_en = NOW() WHERE id = ?')
                ->execute([$sesionId]);

            $sysMsg = "🔔 Has solicitado hablar con un operador humano. Tu solicitud ha entrado a la sala de atención de los administradores comunales. En breve un operador te responderá aquí.";
            $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, "sistema", ?)')
                ->execute([$sesionId, $sysMsg]);
            $sysMsgId = (int)$pdo->lastInsertId();

            jsonOut([
                'ok' => true,
                'estado' => 'esperando_humano',
                'respuesta' => [
                    'id' => $sysMsgId,
                    'remitente' => 'sistema',
                    'mensaje' => $sysMsg,
                    'creado_en' => date('Y-m-d H:i:s')
                ]
            ]);
            break;

        // ==========================================
        // 5. REGISTRAR DATOS DE VISITANTE (OPCIONAL)
        // ==========================================
        case 'update_visitor_info':
            $uuid = trim((string)getParam('session_uuid'));
            $nombre = trim((string)getParam('nombre'));
            $tel = trim((string)getParam('telefono'));

            if ($uuid === '') jsonOut(['ok' => false, 'error' => 'UUID requerido'], 400);

            $stmt = $pdo->prepare('UPDATE chat_sesiones SET nombre_visitante = COALESCE(NULLIF(?, ""), nombre_visitante), telefono = COALESCE(NULLIF(?, ""), telefono) WHERE session_uuid = ?');
            $stmt->execute([$nombre, $tel, $uuid]);

            jsonOut(['ok' => true]);
            break;

        // ==========================================
        // 6. PANEL ADMIN: OBTENER SESIONES (TIPO WHATSAPP)
        // ==========================================
        case 'admin_get_sessions':
            requiere_login();
            $filtro = getParam('filtro', 'todos');

            $sql = 'SELECT s.*, u.nombre AS operador_nombre,
                           (SELECT COUNT(*) FROM chat_mensajes m WHERE m.sesion_id = s.id) AS total_mensajes
                    FROM chat_sesiones s
                    LEFT JOIN usuarios u ON u.id = s.atendido_por';

            if ($filtro === 'humano') {
                $sql .= ' WHERE s.estado IN ("esperando_humano", "humano_activo")';
            } elseif ($filtro === 'ia') {
                $sql .= ' WHERE s.estado = "ia"';
            }
            $sql .= ' ORDER BY s.actualizado_en DESC LIMIT 100';

            $sesiones = $pdo->query($sql)->fetchAll();

            // Resumen de contadores
            $totales = $pdo->query('
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado IN ("esperando_humano", "humano_activo") THEN 1 ELSE 0 END) AS esperando_humano,
                    SUM(no_leidos_admin) AS sin_leer
                FROM chat_sesiones
            ')->fetch();

            jsonOut([
                'ok' => true,
                'totales' => $totales,
                'sesiones' => $sesiones
            ]);
            break;

        // ==========================================
        // 7. PANEL ADMIN: OBTENER CONVERSACIÓN COMPLETA
        // ==========================================
        case 'admin_get_messages':
            requiere_login();
            $sesionId = (int)getParam('sesion_id');
            if ($sesionId <= 0) jsonOut(['ok' => false, 'error' => 'ID de sesión requerido'], 400);

            // Marcar mensajes como leídos
            $pdo->prepare('UPDATE chat_sesiones SET no_leidos_admin = 0 WHERE id = ?')->execute([$sesionId]);

            $stmtS = $pdo->prepare('SELECT s.*, u.nombre AS operador_nombre FROM chat_sesiones s LEFT JOIN usuarios u ON u.id = s.atendido_por WHERE s.id = ?');
            $stmtS->execute([$sesionId]);
            $sesion = $stmtS->fetch();

            if (!$sesion) jsonOut(['ok' => false, 'error' => 'Sesión no encontrada'], 404);

            $stmtM = $pdo->prepare('SELECT * FROM chat_mensajes WHERE sesion_id = ? ORDER BY id ASC');
            $stmtM->execute([$sesionId]);
            $mensajes = $stmtM->fetchAll();

            jsonOut([
                'ok' => true,
                'sesion' => $sesion,
                'mensajes' => $mensajes
            ]);
            break;

        // ==========================================
        // 8. PANEL ADMIN: ENVIAR MENSAJE (OPERADOR HUMANO)
        // ==========================================
        case 'admin_send_message':
            requiere_login();
            $sesionId = (int)getParam('sesion_id');
            $mensaje = trim((string)getParam('mensaje'));

            if ($sesionId <= 0 || $mensaje === '') {
                jsonOut(['ok' => false, 'error' => 'Datos incompletos'], 400);
            }

            $u = usuario_actual();
            $adminNombre = $u['nombre'] ?? 'Operador Comunal';
            $adminId = usuario_id();

            // Cambiar sesión a humano_activo
            $pdo->prepare('UPDATE chat_sesiones SET estado = "humano_activo", atendido_por = ?, ultimo_mensaje = ?, no_leidos_visitante = no_leidos_visitante + 1, actualizado_en = NOW() WHERE id = ?')
                ->execute([$adminId, $mensaje, $sesionId]);

            // Guardar mensaje
            $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, autor_nombre, mensaje) VALUES (?, "admin", ?, ?)')
                ->execute([$sesionId, $adminNombre, $mensaje]);
            $msgId = (int)$pdo->lastInsertId();

            jsonOut([
                'ok' => true,
                'id' => $msgId,
                'remitente' => 'admin',
                'autor_nombre' => $adminNombre,
                'mensaje' => $mensaje,
                'creado_en' => date('Y-m-d H:i:s')
            ]);
            break;

        // ==========================================
        // 9. PANEL ADMIN: FINALIZAR ATENCIÓN Y DEVOLVER A IA
        // ==========================================
        case 'admin_finish_session':
            requiere_login();
            $sesionId = (int)getParam('sesion_id');
            if ($sesionId <= 0) jsonOut(['ok' => false, 'error' => 'ID de sesión requerido'], 400);

            $u = usuario_actual();
            $adminNombre = $u['nombre'] ?? 'Operador';

            $sysMsg = "✨ La atención personalizada con el operador $adminNombre ha concluido con éxito. El Asistente Virtual Comunal (IA) retoma la conversación y queda atento a cualquier otra duda.";

            $pdo->prepare('UPDATE chat_sesiones SET estado = "ia", no_leidos_visitante = no_leidos_visitante + 1, actualizado_en = NOW() WHERE id = ?')
                ->execute([$sesionId]);

            $pdo->prepare('INSERT INTO chat_mensajes (sesion_id, remitente, mensaje) VALUES (?, "sistema", ?)')
                ->execute([$sesionId, $sysMsg]);
            $msgId = (int)$pdo->lastInsertId();

            jsonOut([
                'ok' => true,
                'mensaje_sistema' => $sysMsg,
                'id' => $msgId
            ]);
            break;

        default:
            jsonOut([
                'ok' => true,
                'servicio' => 'API Chat Comunal Inteligente con Handoff Humano',
                'version' => '2.0',
            ]);
            break;
    }
} catch (Throwable $e) {
    jsonOut([
        'ok' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ], 500);
}
