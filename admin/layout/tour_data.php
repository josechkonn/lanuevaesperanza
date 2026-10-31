<?php
/**
 * Definición de los pasos del tour interactivo contextuales por página.
 * Solo envía al frontend los pasos correspondientes a la página en la que se encuentra el usuario.
 */
$seccionTour = $seccion ?? 'index';

$catalogoTours = [
    'productores' => [
        [
            'id' => 'kpis_productores',
            'selector' => '#kpiProductoresWrap',
            'titulo' => 'Métricas de Acopio y Solicitudes (Literal G)',
            'texto' => 'Estas 4 tarjetas reflejan en tiempo real el estado de atención a los productores. Puede hacer clic en cualquiera de ellas (ej. <strong>Pendientes</strong> u <strong>Ofertas de rubros</strong>) para filtrar la tabla automáticamente.',
            'tip' => '💡 Al hacer clic en un KPI seleccionado se desactiva el filtro para volver a ver todas las comunicaciones.'
        ],
        [
            'id' => 'filtros_periodo',
            'selector' => '#filtroProductoresCard, .filtros',
            'titulo' => 'Filtro de Fechas y Búsqueda',
            'texto' => 'Consulte solicitudes por periodos rápidos (<strong>Hoy, Semana, Mes, Año o Todo</strong>) o defina un rango exacto de fechas. También puede buscar por nombre de productor o comunidad.',
            'tip' => '💡 Las flechas &lt; y &gt; le permiten navegar por periodos de tiempo sin escribir fechas manualmente.'
        ],
        [
            'id' => 'tabla_productores',
            'selector' => '#tablaProductoresWrap',
            'titulo' => 'Comunicaciones e Intercambio con Productores',
            'texto' => 'Aquí se listan las solicitudes enviadas desde el portal público por conucos y UPF: ofertas de cosecha, requerimientos técnicos o sugerencias.',
            'tip' => '💡 Cada fila indica el sector territorial, el tipo de gestión y el estado del requerimiento.'
        ],
        [
            'id' => 'detalle_seguimiento',
            'selector' => '.btn-tour-detalle-first, table tbody tr:first-child',
            'titulo' => 'Gestión, WhatsApp y Acuerdos Técnicos',
            'texto' => 'Al abrir la ficha de una solicitud, puede contactar al productor directamente vía <strong>WhatsApp</strong>, modificar su estado (ej. <em>En revisión</em> o <em>Atendido</em>) y registrar acuerdos técnicos de la EPSDC.',
            'tip' => '💡 Los acuerdos redactados quedan archivados en la auditoría interna de la junta directiva comunal.'
        ]
    ],
    'chat' => [
        [
            'id' => 'lista_conversaciones',
            'selector' => '#panelConversacionesWrap',
            'titulo' => 'Panel de Conversaciones Activas',
            'texto' => 'Muestra en tiempo real todas las sesiones de visitantes. Puede buscar por nombre o filtrar por <strong>Esperando operador humano</strong> o <strong>Atendidas por IA</strong>.',
            'tip' => '💡 Las sesiones con visitantes esperando atención humana resaltan con un indicador rojo parpadeante.'
        ],
        [
            'id' => 'modo_atencion',
            'selector' => '#chatHeaderBar',
            'titulo' => 'Modo de Atención (IA vs. Operador)',
            'texto' => 'Le permite alternar el control de la conversación. Pulse <strong>Tomar control</strong> para chatear en persona o <strong>Devolver a IA</strong> para que el asistente virtual continúe.',
            'tip' => '💡 La confirmación centralizada le asegura que la devolución a la IA se realice de forma segura y sin interrupciones bruscas.'
        ],
        [
            'id' => 'historial_chat',
            'selector' => '#adminChatFeed',
            'titulo' => 'Diálogo y Orientación en Tiempo Real',
            'texto' => 'Vea la conversación en directo. Los mensajes del visitante aparecen a la izquierda y las respuestas del operador a la derecha. La IA cuenta con la guía oficial para orientar en navegación web.',
            'tip' => '💡 La IA provee enlaces directos a <em>Ver Combos</em>, <em>Cronograma</em> o <em>Portal de Productores</em>.'
        ],
        [
            'id' => 'redaccion_chat',
            'selector' => '#adminMessageForm',
            'titulo' => 'Redacción y Envío Inmediato',
            'texto' => 'Escriba aquí su mensaje para el visitante cuando esté en modo operador. Al enviar, el mensaje llega instantáneamente a la pantalla del visitante.',
            'tip' => '💡 Presione <strong>Enter</strong> para enviar rápidamente su respuesta sin usar el ratón.'
        ]
    ],
    'index' => [
        [
            'id' => 'kpis_dashboard',
            'selector' => '#kpiGridDashboard',
            'titulo' => 'Métricas Globales de la EPSDC',
            'texto' => 'Resumen ejecutivo de la empresa comunal: volumen de productores atendidos, entregas programadas, rubros agrícolas y combos solidarios activos.',
            'tip' => '💡 Haga clic en cualquier tarjeta para navegar directamente al módulo correspondiente.'
        ],
        [
            'id' => 'accesos_rapidos',
            'selector' => '#bloqueAccesosRapidos',
            'titulo' => 'Operaciones y Distribución Comunal',
            'texto' => 'Acceda con un solo clic a la programación de rutas de distribución, gestión de precios y el portal de atención a productores.',
            'tip' => '💡 Mantenga actualizados los precios para que la IA responda siempre con exactitud.'
        ],
        [
            'id' => 'control_acceso',
            'selector' => '#topbarUserWrap',
            'titulo' => 'Perfil y Permisos de Usuario',
            'texto' => 'En la esquina superior derecha puede consultar su usuario actual, su rol asignado en la EPSDC y acceder a su perfil o cerrar sesión.',
            'tip' => '💡 Los usuarios con rol <strong>Atención</strong> están estrictamente asignados a la consola de chat en vivo.'
        ]
    ],
    'entregas' => [
        [
            'id' => 'nueva_entrega',
            'selector' => '#btnNuevaEntrega, .page-title-box',
            'titulo' => 'Programación de Rutas Comunitarias',
            'texto' => 'Planifique una nueva ruta de distribución indicando fecha, sector de Jáuregui, responsable comunal y cantidad de bolsas estimadas.',
            'tip' => '💡 La ruta aparecerá de inmediato en el cronograma público para los habitantes.'
        ],
        [
            'id' => 'filtros_entregas',
            'selector' => '.filtros',
            'titulo' => 'Filtro de Semanas y Despachos',
            'texto' => 'Filtre las entregas por semana o mes. Use las flechas para consultar despachos pasados o futuros.',
            'tip' => '💡 El botón circular central le devuelve instantáneamente a la semana en curso.'
        ],
        [
            'id' => 'tabla_entregas',
            'selector' => '#tablaEntregasWrap, .table-responsive',
            'titulo' => 'Control de Ciclo de Entrega',
            'texto' => 'Visualice las entregas y actualice su estado conforme avanzan: <strong>Programada &rarr; En ruta &rarr; Completada</strong>.',
            'tip' => '💡 Marcar las entregas como <em>Completadas</em> actualiza las estadísticas comunales.'
        ]
    ],
    'productos' => [
        [
            'id' => 'nuevo_combo',
            'selector' => '#btnNuevoCombo, .page-title-box',
            'titulo' => 'Creación de Bolsas y Combos Solidarios',
            'texto' => 'Registre una nueva bolsa o combo especificando su nombre, rubro principal, precio en USD y contenido detallado.',
            'tip' => '💡 El contenido detallado es utilizado por la IA del chat para informar a los vecinos con exactitud.'
        ],
        [
            'id' => 'tabla_productos',
            'selector' => '#tablaProductosWrap, .table-responsive',
            'titulo' => 'Gestión de Precios y Disponibilidad',
            'texto' => 'Edite los combos y alterne su disponibilidad (Activo / Inactivo). Cada cambio de precio queda automáticamente registrado en el historial.',
            'tip' => '💡 Desactive temporalmente los combos que estén fuera de cosecha para que no aparezcan en el portal.'
        ]
    ],
    'configuracion' => [
        [
            'id' => 'identidad_epsdc',
            'selector' => '#cardIdentidadWrap',
            'titulo' => 'Identidad Institucional de la EPSDC',
            'texto' => 'Configure la sede principal en Quebrada de San José, teléfonos oficiales y lema de la empresa comunal.',
            'tip' => '💡 Estos datos se reflejan automáticamente en todo el portal y en el contexto fáctico de la IA.'
        ],
        [
            'id' => 'motor_virtual_ia',
            'selector' => '#cardMotorIAWrap',
            'titulo' => 'Motor Virtual de Inteligencia Artificial',
            'texto' => 'Seleccione entre Google Gemini, DeepSeek o el modelo local, configure su API Key protegida y pruebe la conexión en vivo.',
            'tip' => '💡 Use el botón con icono de ojo para visualizar o editar la clave de forma segura.'
        ]
    ],
    'rubros' => [
        [
            'id' => 'nuevo_rubro',
            'selector' => '#btnNuevoRubro, .page-title-box',
            'titulo' => 'Registro de Rubros Andinos',
            'texto' => 'Añada hortalizas, tubérculos y frutales autóctonos con sus características de siembra y rendimiento.',
            'tip' => '💡 Enriquece el catálogo agrícola de La Grita para los productores.'
        ],
        [
            'id' => 'tabla_rubros',
            'selector' => '#tablaRubrosWrap, .table-responsive',
            'titulo' => 'Catálogo de Rubros Agrícolas',
            'texto' => 'Consulte y filtre los rubros registrados en el sistema por categoría o nombre.',
            'tip' => '💡 Se utiliza como base para la conformación de los combos solidarios.'
        ]
    ],
    'usuarios' => [
        [
            'id' => 'nuevo_usuario',
            'selector' => '#btnNuevoUsuario, .page-title-box',
            'titulo' => 'Creación de Operadores y Roles',
            'texto' => 'Cree nuevos usuarios asignando su rol: Superadmin, Editor, Lector o Atención (para soporte exclusivo en chat).',
            'tip' => '💡 El rol <strong>Atención</strong> garantiza que el usuario solo opere la consola de chat comunal.'
        ],
        [
            'id' => 'tabla_usuarios',
            'selector' => '#tablaUsuariosWrap, .table-responsive',
            'titulo' => 'Gestión de Accesos y Roles',
            'texto' => 'Monitoree los operadores activos, restablezca contraseñas y audite sus permisos.',
            'tip' => '💡 Toda creación y modificación queda registrada en la auditoría general.'
        ]
    ],
    'auditoria' => [
        [
            'id' => 'tabla_auditoria',
            'selector' => '#tablaAuditoriaWrap, .table-responsive',
            'titulo' => 'Trazabilidad y Seguridad Comunal',
            'texto' => 'Registro cronológico inmutable de todas las acciones en el panel: quién, cuándo, desde qué IP y qué registro se modificó.',
            'tip' => '💡 Fundamental para la rendición de cuentas comunal.'
        ]
    ]
];

// Pasos activos SOLO de la página actual
$pasosPaginaActual = $catalogoTours[$seccionTour] ?? [];
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.TourGuia) {
        window.TourGuia.init({
            seccion: <?= json_encode($seccionTour) ?>,
            pasos: <?= json_encode($pasosPaginaActual, JSON_UNESCAPED_UNICODE) ?>
        });
    }
});
</script>
