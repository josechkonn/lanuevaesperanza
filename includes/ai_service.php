<?php
declare(strict_types=1);

/**
 * Servicio de Asistente Virtual Comunal con IA (DeepSeek, OpenRouter, Gemini y Fallback Local Inteligente).
 * EPSDC La Nueva Esperanza - La Grita, Táchira.
 */

require_once __DIR__ . '/app.php';

function get_epsdc_system_prompt(): string {
    $org = config_get('nombre_organizacion', 'La Nueva Esperanza');
    $dbData = get_epsdc_live_database_context();

    return <<<PROMPT
Eres el equipo de Orientación y Atención Comunal de la Empresa de Propiedad Social Directa Comunal (EPSDC) "$org", ubicada en la Ciudad de La Grita, Municipio Jáuregui (Aldea Quebrada de San José), Estado Táchira, Venezuela.

$dbData

REGLAS DE ATENCIÓN Y GROUNDING ESTRICTO EN BASE DE DATOS:
1. IDENTIDAD FORMAL Y PROFESIONAL: Utiliza siempre un lenguaje formal, educado, respetuoso y general en primera persona del plural ("en nuestra empresa comunal", "nuestro equipo", "tenemos a su disposición"). Evita modismos informales o expresiones coloquiales. NUNCA uses frases robóticas ni digas "soy una IA", "soy un modelo de lenguaje" ni hables de algoritmos ni prompts. Eres el canal oficial de atención y coordinación de la EPSDC.
2. RESPONDER EXCLUSIVAMENTE CON DATOS REALES DE LA BASE DE DATOS: Basa todas tus respuestas ÚNICA Y EXCLUSIVAMENTE en los datos de la base de datos oficial que se te acaban de proporcionar arriba.
3. PROHIBIDO INVENTAR PRODUCTOS O SERVICIOS: Si el usuario pregunta por un producto que NO está explícitamente en la lista de productos de la base de datos (por ejemplo, carnes rojas, pollo, lácteos importados, pescado de mar, electrodomésticos, etc.), responde con honestidad y cordialidad: "Actualmente ese rubro no forma parte del catálogo oficial de nuestra red comunal."
4. PRECIOS EXACTOS: Si preguntan el precio de cualquier combo o bolsa, responde ÚNICAMENTE con la cifra exacta y la moneda registrada en la base de datos (ejemplo: Bolsa Nutricional Solidaria a $12.50 USD, Combo Familiar Completo a $24.00 USD, Combo Proteína y Granos a $18.50 USD, Mega Combo Despensa Mensual a $38.00 USD). PROHIBIDO alterar, inventar o redondear precios.
5. ENTREGAS Y RUTAS EXACTAS: Si preguntan por fechas, puntos de acopio o responsables de entregas, cita exclusivamente los sectores, fechas y responsables que figuran en la tabla de entregas de la base de datos.
6. TRANSPARENCIA ANTE DUDAS PARTICULARES: Si te solicitan cualquier dato que no conste en la base de datos suministrada, indica amablemente: "Ese dato específico no lo tenemos registrado en nuestro sistema en este instante. Si gustas, puedes presionar el botón de Coordinador para comunicarte directamente con la directiva."
7. TRANSFERENCIA A COORDINACIÓN: Si el usuario desea hablar con una persona específica de la directiva o un coordinador, indícale con calidez que puede pulsar el botón "Coordinador" o escribir "coordinador" para ser enlazado de inmediato con el equipo de guardia.
8. ESTILO: Cálido, andino, respetuoso, fraterno y conciso (máximo 1 a 2 párrafos o viñetas claras). No uses enlaces de WhatsApp ni inventes URLs externas.
PROMPT;
}

/**
 * Extrae en tiempo real el contexto fáctico desde la base de datos MySQL.
 * Grounding estricto anti-alucinaciones: Productos, Precios, Entregas, Estatutos y Configuración.
 */
function get_epsdc_live_database_context(): string {
    $pdo = db();
    $ctx = "=== BASE DE DATOS OFICIAL EN VIVO DE LA EPSDC (DATOS REALES VERIFICADOS) ===\n\n";

    // 1. Datos Institucionales y Contacto
    $nombre = config_get('nombre_organizacion', 'La Nueva Esperanza');
    $lema = config_get('lema', 'Caracterización Territorial y Soberanía Agroalimentaria');
    $tel = config_get('telefono', '+58 412 1234567');
    $email = config_get('email_contacto', 'contacto@lanuevaesperanza.org');
    $dir = config_get('direccion', 'Aldea Quebrada de San José, Ciudad de La Grita, Municipio Jáuregui, Estado Táchira');
    $moneda = config_get('moneda_defecto', 'USD');

    $ctx .= "1. DATOS DE CONTACTO Y SEDE OFICIAL:\n";
    $ctx .= "- Organización: $nombre\n";
    $ctx .= "- Lema: $lema\n";
    $ctx .= "- Sede Principal: $dir\n";
    $ctx .= "- Teléfono Oficial: $tel\n";
    $ctx .= "- Correo Electrónico: $email\n";
    $ctx .= "- Moneda de Referencia: $moneda\n\n";

    // 2. Catálogo Oficial de Productos y Combos
    try {
        $st = $pdo->query("
            SELECT p.id, p.tipo, p.nombre, p.contenido, p.precio, p.moneda, r.nombre AS rubro_categoria
            FROM productos p
            LEFT JOIN rubros r ON p.rubro_id = r.id
            WHERE p.activo = 1
            ORDER BY p.precio ASC
        ");
        $prods = $st->fetchAll();
        $ctx .= "2. PRODUCTOS Y COMBOS DISPONIBLES EN BASE DE DATOS (PRECIOS Y CONTENIDOS EXACTOS):\n";
        if (empty($prods)) {
            $ctx .= "Actualmente no hay combos registrados en la base de datos.\n";
        } else {
            foreach ($prods as $p) {
                $contenido = str_replace(["\r\n", "\n", "\r"], ", ", trim((string)$p['contenido']));
                $ctx .= sprintf(
                    "- [%s #%d] %s | Precio Oficial: $%.2f %s | Contenido: %s\n",
                    strtoupper((string)$p['tipo']),
                    $p['id'],
                    $p['nombre'],
                    (float)$p['precio'],
                    $p['moneda'],
                    $contenido ?: 'Surtido variado según cosecha'
                );
            }
        }
        $ctx .= "\n";
    } catch (Throwable $e) {
        error_log("Error cargando productos para IA: " . $e->getMessage());
    }

    // 3. Cronograma Oficial de Rutas y Entregas
    try {
        $st = $pdo->query("
            SELECT sector, fecha_entrega, responsable, cantidad_bolsas, estado
            FROM entregas
            ORDER BY fecha_entrega DESC
            LIMIT 8
        ");
        $entregas = $st->fetchAll();
        $ctx .= "3. CRONOGRAMA OFICIAL DE ENTREGAS Y RUTAS (EN BASE DE DATOS):\n";
        if (empty($entregas)) {
            $ctx .= "No hay rutas programadas en el cronograma.\n";
        } else {
            foreach ($entregas as $e) {
                $ctx .= sprintf(
                    "- Sector: %s | Fecha: %s | Responsable: %s | Cupos Asignados: %d bolsas | Estado: %s\n",
                    $e['sector'],
                    $e['fecha_entrega'],
                    $e['responsable'] ?: 'Comité de Alimentación',
                    $e['cantidad_bolsas'],
                    $e['estado']
                );
            }
        }
        $ctx .= "\n";
    } catch (Throwable $e) {
        error_log("Error cargando entregas para IA: " . $e->getMessage());
    }

    // 4. Marco Legal Estatutario y Fondos
    $ctx .= "4. FONDOS ESTATUTARIOS Y MARCO LEGAL COMUNAL:\n";
    $ctx .= "- 70% Fondo de Mantenimiento Productivo\n";
    $ctx .= "- 20% Fondo de Atención a Productores y Familias Campesinas\n";
    $ctx .= "- 10% Fondo Comunitario para el Poder Popular\n\n";

    // 5. Rubros Emblema y Territorio
    $ctx .= "5. RUBROS EMBLEMA Y TERRITORIO ANDINO:\n";
    $ctx .= "- Truchas en Las Porqueras: Municipio Jáuregui, aguas gélidas de páramo (2.400 msnm).\n";
    $ctx .= "- Café de Pregonero: Municipio Uribante, café arábica de estricta altura (1.500 - 1.950 msnm).\n";
    $ctx .= "- Panela de Humuquena: Municipio San Judas Tadeo, trapiches tradicionales de caña pura.\n";
    $ctx .= "- 12 Municipios Articulados: Jáuregui, Uribante, Francisco de Miranda (San José de Bolívar), Sucre (Queniquea), San Judas Tadeo (Humuquena), Seboruco, José María Vargas (El Cobre), Michelena, Lobatera, Panamericano (Coloncito), más Mérida y Trujillo.\n";
    $ctx .= "- Agua Continua 365 Días: Manantiales del Parque Nacional General Juan Pablo Peñaloza (Páramos del Batallón y La Negra).\n\n";

    return $ctx;
}

/**
 * Genera la respuesta del Asistente Virtual.
 * Prioriza DeepSeek / OpenRouter / Gemini si hay API Key configurada; de lo contrario usa el motor local inteligente.
 */
function ai_generate_response(string $userPrompt, array $conversationHistory = []): array {
    $pdo = db();
    $provider = config_get('ai_provider', 'deepseek');
    $apiKey = trim((string)config_get('ai_api_key', ''));
    $model = trim((string)config_get('ai_model', 'deepseek-chat'));

    // Si el usuario solicita explícitamente atención humana
    if (detect_human_request($userPrompt)) {
        return [
            'ok' => true,
            'requires_handoff' => true,
            'source' => 'system',
            'response' => "🔔 Hemos recibido su solicitud de comunicación directa con la administración. En este momento estamos transfiriendo su consulta al equipo de coordinación de la EPSDC La Nueva Esperanza para atenderle por este mismo medio. Por favor espere unos instantes.",
        ];
    }

    // Fast-path para saludos y cortesías comunes (Respuesta instantánea < 5 ms)
    $cleanPrompt = mb_strtolower(trim($userPrompt));
    if (preg_match('/^(hola|buenos d[ií]as|buenas tardes|buenas noches|saludos|buenas|qu[eé] tal|como est[aá]s)[\.!\s]*$/i', $cleanPrompt)) {
        return [
            'ok' => true,
            'requires_handoff' => false,
            'source' => 'fast_kb',
            'response' => "¡Hola! Le damos una cordial bienvenida a la EPSDC La Nueva Esperanza en La Grita, Estado Táchira. ¿En qué podemos orientarle hoy? Puede consultarnos sobre combos disponibles, precios oficiales en USD, fechas de entrega o solicitar comunicación con un coordinador."
        ];
    }
    if (preg_match('/^(gracias|muchas gracias|vale gracias|ok gracias|perfecto gracias)[\.!\s]*$/i', $cleanPrompt)) {
        return [
            'ok' => true,
            'requires_handoff' => false,
            'source' => 'fast_kb',
            'response' => "¡Con mucho gusto! Quedamos a su completa disposición para cualquier consulta adicional."
        ];
    }

    // Si hay API Key y no es local, intentar llamada externa
    if ($apiKey !== '' && $provider !== 'local') {
        try {
            if ($provider === 'deepseek') {
                $res = call_deepseek_api($userPrompt, $conversationHistory, $apiKey, $model ?: 'deepseek-chat');
                return ['ok' => true, 'requires_handoff' => false, 'source' => 'deepseek', 'response' => $res];
            } elseif ($provider === 'openrouter') {
                $res = call_openrouter_api($userPrompt, $conversationHistory, $apiKey, $model ?: 'deepseek/deepseek-r1:free');
                return ['ok' => true, 'requires_handoff' => false, 'source' => 'openrouter', 'response' => $res];
            } elseif ($provider === 'gemini') {
                $res = call_gemini_api($userPrompt, $conversationHistory, $apiKey, $model ?: 'gemini-1.5-flash');
                return ['ok' => true, 'requires_handoff' => false, 'source' => 'gemini', 'response' => $res];
            }
        } catch (Throwable $e) {
            error_log("Error en proveedor IA ($provider): " . $e->getMessage());
            // Fallback elegante al motor agronómico local
        }
    }

    // Motor local inteligente de respaldo
    $localResp = ai_local_agronomic_response($userPrompt);
    return [
        'ok' => true,
        'requires_handoff' => false,
        'source' => 'local_kb',
        'response' => $localResp
    ];
}

/**
 * Detecta si el usuario está pidiendo hablar con un agente humano.
 */
function detect_human_request(string $text): bool {
    $t = mb_strtolower(trim($text));
    $keywords = [
        'humano', 'persona real', 'hablar con alguien', 'operador', 'administrador',
        'asesor', 'atencion humana', 'hablar con persona', 'agente', 'soporte humano',
        'quiero un humano', 'conectar con persona', 'atenderme una persona'
    ];
    foreach ($keywords as $kw) {
        if (str_contains($t, $kw)) {
            return true;
        }
    }
    return false;
}

/**
 * Sanitiza la respuesta de la IA eliminando rastros de pensamiento interno (CoT / reasoning).
 */
function sanitize_ai_response_text(string $text): string {
    // 1. Eliminar etiquetas <think>...</think>
    $text = preg_replace('/<think\b[^>]*>[\s\S]*?<\/think>/i', '', $text);
    // 2. Eliminar bloques de código de pensamiento ```thought...``` o ```reasoning...```
    $text = preg_replace('/```(?:thought|reasoning)[\s\S]*?```/i', '', $text);
    // 3. Eliminar si empieza con reflexiones en inglés tipo "The user is asking..."
    $text = preg_replace('/^(?:The user is asking|According to the strict grounding rules)[\s\S]*?\n\n/i', '', $text);
    return trim($text);
}

/**
 * Llamada a la API oficial de DeepSeek (formato compatible OpenAI).
 */
function call_deepseek_api(string $prompt, array $history, string $apiKey, string $model): string {
    $messages = [
        ['role' => 'system', 'content' => get_epsdc_system_prompt()]
    ];

    // Incluir últimos 6 mensajes del historial para contexto
    $recentHistory = array_slice($history, -6);
    foreach ($recentHistory as $msg) {
        $role = ($msg['remitente'] === 'visitante') ? 'user' : 'assistant';
        $messages[] = ['role' => $role, 'content' => (string)$msg['mensaje']];
    }
    $messages[] = ['role' => 'user', 'content' => $prompt];

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.5,
        'max_tokens' => 800,
        'stream' => false,
    ];

    $ch = curl_init('https://api.deepseek.com/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) throw new RuntimeException("cURL error: $err");
    if ($httpCode !== 200) throw new RuntimeException("DeepSeek HTTP $httpCode: $response");

    $data = json_decode((string)$response, true);
    if (!isset($data['choices'][0]['message']['content'])) {
        throw new RuntimeException("Formato inválido devuelto por DeepSeek");
    }

    $clean = sanitize_ai_response_text((string)$data['choices'][0]['message']['content']);
    if ($clean === '') {
        throw new RuntimeException("Respuesta vacía tras sanitizar razonamiento interno.");
    }
    return $clean;
}

/**
 * Llamada a OpenRouter (soporta modelos como nvidia/nemotron-3-ultra-550b-a55b:free, meta-llama, etc.).
 */
function call_openrouter_api(string $prompt, array $history, string $apiKey, string $model): string {
    $messages = [
        ['role' => 'system', 'content' => get_epsdc_system_prompt()]
    ];

    $recentHistory = array_slice($history, -4);
    foreach ($recentHistory as $msg) {
        $role = ($msg['remitente'] === 'visitante') ? 'user' : 'assistant';
        $messages[] = ['role' => $role, 'content' => (string)$msg['mensaje']];
    }
    $messages[] = ['role' => 'user', 'content' => $prompt];

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.4,
        'max_tokens' => 650,
        'reasoning' => ['effort' => 'none'],
    ];

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_ENCODING => 'gzip',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: http://localhost/lanuevaesperanza',
            'X-Title: EPSDC La Nueva Esperanza',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 16,
        CURLOPT_CONNECTTIMEOUT => 4,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) throw new RuntimeException("cURL error: $err");
    if ($httpCode !== 200) throw new RuntimeException("OpenRouter HTTP $httpCode: $response");

    $data = json_decode((string)$response, true);
    $rawContent = $data['choices'][0]['message']['content'] ?? null;
    if (empty($rawContent)) {
        throw new RuntimeException("Formato inválido o respuesta vacía devuelta por OpenRouter");
    }

    $content = sanitize_ai_response_text((string)$rawContent);
    if ($content === '') {
        throw new RuntimeException("Respuesta vacía tras sanitizar razonamiento interno en OpenRouter");
    }

    return $content;
}

/**
 * Llamada a Google Gemini API (modelo gemini-1.5-flash / gemini-2.0-flash).
 */
function call_gemini_api(string $prompt, array $history, string $apiKey, string $model): string {
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $contents = [];
    $recentHistory = array_slice($history, -6);
    foreach ($recentHistory as $msg) {
        $role = ($msg['remitente'] === 'visitante') ? 'user' : 'model';
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => (string)$msg['mensaje']]]
        ];
    }
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $prompt]]
    ];

    $payload = [
        'contents' => $contents,
        'systemInstruction' => [
            'parts' => [['text' => get_epsdc_system_prompt()]]
        ],
        'generationConfig' => [
            'temperature' => 0.5,
            'maxOutputTokens' => 800,
        ]
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) throw new RuntimeException("cURL error: $err");
    if ($httpCode !== 200) throw new RuntimeException("Gemini HTTP $httpCode: $response");

    $data = json_decode((string)$response, true);
    $parts = $data['candidates'][0]['content']['parts'] ?? [];
    $text = '';
    foreach ($parts as $p) {
        if (!empty($p['thought'])) continue;
        if (isset($p['text'])) {
            $text .= $p['text'];
        }
    }

    $clean = sanitize_ai_response_text($text);
    if ($clean === '') {
        throw new RuntimeException("Formato inválido o respuesta vacía devuelta por Gemini");
    }

    return $clean;
}

/**
 * Motor local agronómico inteligente de EPSDC La Nueva Esperanza (sin dependencia externa).
 */
function ai_local_agronomic_response(string $text): string {
    $t = mb_strtolower(trim($text));

    // 1. Saludos
    if (preg_match('/^(hola|buenos|saludos|buenas|que tal|como estas)/i', $t)) {
        return "¡Hola! Le damos una cordial bienvenida a la EPSDC La Nueva Esperanza en La Grita, Estado Táchira. ¿En qué podemos orientarle hoy? Puede consultarnos sobre nuestros rubros agrícolas, combos y bolsas disponibles, cronograma de entregas, o si desea comunicarse con un coordinador de guardia.";
    }

    // 2. Rubros Emblema y Especialidades Territoriales (Truchas, Café, Panela)
    if (str_contains($t, 'trucha') || str_contains($t, 'porquera') || str_contains($t, 'piscicultura')) {
        return "🐟 Las Truchas en Las Porqueras: En el sector de alta montaña Las Porqueras (Municipio Jáuregui, vía Páramo del Batallón y La Negra), manantiales de deshielo a temperaturas entre 9°C y 14°C alimentan nuestros criaderos comunales de Trucha Arcoíris. Es una fuente proteica de excepcional pureza, rica en Omega-3 y criada en agua corriente de manantial sin químicos ni antibióticos.";
    }

    if (str_contains($t, 'café') || str_contains($t, 'cafe') || str_contains($t, 'pregonero') || str_contains($t, 'uribante')) {
        return "☕ El Café de Pregonero: Cosechado en el Municipio Uribante (capital Pregonero) en laderas andinas entre 1.450 y 1.950 msnm. Es un café Arábica de estricta altura recolectado artesanalmente bajo sombra natural. Destaca por su aroma profundo, acidez cítrica brillante, cuerpo sedoso y notas a chocolate y panela.";
    }

    if (str_contains($t, 'panela') || str_contains($t, 'humuquena') || str_contains($t, 'umuquena') || str_contains($t, 'trapiche') || str_contains($t, 'papelon') || str_contains($t, 'papelón')) {
        return "🍯 Humuquena: La Panela: En el Municipio San Judas Tadeo (Humuquena / Umuquena), las UPF campesinas procesan la caña de azúcar en trapiches comunales tradicionales. Producimos panela pura en bloque y granulada, libre de aditivos químicos y blanqueadores industriales, aportando minerales esenciales (hierro, calcio y fósforo) y energía 100% natural.";
    }

    if (str_contains($t, 'san jose de bolivar') || str_contains($t, 'san josé de bolívar') || str_contains($t, 'bolivar') || str_contains($t, 'francisco de miranda') || str_contains($t, 'queniquea') || str_contains($t, 'sucre')) {
        return "🏔️ Municipios San José de Bolívar y Queniquea: San José de Bolívar (Municipio Francisco de Miranda) aporta caña panelera, trapiches, café de montaña, ganadería lechera y hortalizas desde el fértil Valle del Río Bobo. Queniquea (Municipio Sucre) destaca por su papa de semilla, apio criollo, mora andina y hortalizas de piso frío en el corazón de la alta serranía andina.";
    }

    // 3. Rubros / Papa / Hortalizas / Cosecha (39 rubros + 3 emblema)
    if (str_contains($t, 'rubro') || str_contains($t, 'papa') || str_contains($t, 'cebolla') || str_contains($t, 'zanahoria') || str_contains($t, 'hortaliza') || str_contains($t, '39') || str_contains($t, '42')) {
        return "Cosechamos 39 rubros agrícolas tradicionales en el macizo montañoso de La Grita y Táchira (Papa Única y Granola a más de 2.000 msnm, cebolla tecnificada, zanahorias, ajo morado, apio, etc.), complementados por nuestros 3 rubros emblema: Truchas de Las Porqueras, Café de Pregonero y Panela de Humuquena. Gracias a las reservas hídricas del Parque Nacional Juan Pablo Peñaloza, producimos los 365 días del año.";
    }

    // 4. Combos / Precios / Bolsas / Comprar (Grounding Real en MySQL)
    if (str_contains($t, 'combo') || str_contains($t, 'bolsa') || str_contains($t, 'precio') || str_contains($t, 'costo') || str_contains($t, 'comprar') || str_contains($t, 'adquirir')) {
        try {
            $pdo = db();
            $prods = $pdo->query("SELECT nombre, tipo, precio, moneda, contenido FROM productos WHERE activo = 1 ORDER BY precio ASC")->fetchAll();
            if ($prods) {
                $lista = [];
                foreach ($prods as $p) {
                    $cont = str_replace(["\r\n", "\n", "\r"], ", ", trim((string)$p['contenido']));
                    $lista[] = "• **" . $p['nombre'] . "** (" . strtoupper($p['tipo']) . "): $" . number_format((float)$p['precio'], 2) . " " . $p['moneda'] . "\n  Contenido: " . ($cont ?: 'Surtido variado');
                }
                return "📦 **Productos y Combos Oficiales en Base de Datos:**\n\n" . implode("\n\n", $lista) . "\n\nPuedes apartar tu cupo en la sección 'Combos' o escribir 'humano' si deseas atención personalizada.";
            }
        } catch (Throwable $e) {}
        return "Actualmente no figuran combos activos en la base de datos oficial. Puedes consultar con un operador humano para confirmar próximas fechas de armado.";
    }

    // 5. Entregas / Horarios / Rutas (Grounding Real en MySQL)
    if (str_contains($t, 'entrega') || str_contains($t, 'ruta') || str_contains($t, 'despacho') || str_contains($t, 'cuando') || str_contains($t, 'horario') || str_contains($t, 'cronograma')) {
        try {
            $pdo = db();
            $entregas = $pdo->query("SELECT sector, fecha_entrega, responsable, cantidad_bolsas, estado FROM entregas ORDER BY fecha_entrega DESC LIMIT 4")->fetchAll();
            if ($entregas) {
                $lista = [];
                foreach ($entregas as $e) {
                    $lista[] = "📍 **" . $e['sector'] . "**\n  Fecha: " . $e['fecha_entrega'] . " | Resp: " . ($e['responsable'] ?: 'Comité Comunal') . " | Cupos: " . $e['cantidad_bolsas'] . " bolsas (" . ucfirst($e['estado']) . ")";
                }
                return "🚚 **Cronograma Oficial de Entregas (Base de Datos):**\n\n" . implode("\n\n", $lista) . "\n\nPuedes consultar el mapa y los detalles en la sección 'Rutas de Entrega'.";
            }
        } catch (Throwable $e) {}
        return "Las rutas de entrega de combos y bolsas se programan semanalmente en el cronograma oficial de nuestra página principal.";
    }

    // 9. Estatutos / Fondos / 70% / 20% / 10%
    if (str_contains($t, 'fondo') || str_contains($t, 'estatuto') || str_contains($t, 'excedente') || str_contains($t, 'ley')) {
        return "Conforme a la Ley Orgánica del Sistema Económico Comunal, nuestros excedentes se reinvierten en: 70% Fondo de Mantenimiento Productivo, 20% Fondo de Atención a Productores y Familias Campesinas, y 10% Fondo Comunitario para el Poder Popular.";
    }

    // Respuesta genérica informativa con sugerencia de operador humano
    return "Gracias por tu consulta. Como asistente de la EPSDC La Nueva Esperanza, puedo darte información sobre nuestros 39 rubros andinos, combos y precios, cronograma de entregas o georreferenciación en La Grita. Si tu consulta requiere atención personalizada, escribe 'hablar con un operador' o pulsa el botón en la parte superior del chat.";
}
