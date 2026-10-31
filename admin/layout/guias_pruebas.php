<?php
/**
 * Guía Interactiva de Pruebas QA para el Panel de Administración.
 * Proporciona suites de pruebas paso a paso contextuales para cada zona del sistema,
 * con persistencia de progreso en localStorage y atajos rápidos.
 */
$seccionQA = $seccion ?? 'index';
$zonasDisponibles = [
    'index' => [
        'nombre' => 'Dashboard Principal',
        'icono' => 'bi-speedometer2',
        'color' => '#059669',
        'pruebas' => [
            [
                'id' => 'kpis_carga',
                'tipo' => 'Critico',
                'titulo' => 'Consistencia de Métricas y KPIs en Tiempo Real',
                'objetivo' => 'Verificar que las cifras de entregas, productores, rubros y combos coincidan con la base de datos sin errores ni ceros por defecto.',
                'pasos' => [
                    'Observe las tarjetas superiores de métricas (Productores, Entregas, Rubros, Combos).',
                    'Verifique que no haya advertencias PHP ni números en formato incorrecto.',
                    'Haga clic en una tarjeta para validar la redirección con filtro aplicado.'
                ],
                'esperado' => 'Las cifras reflejan los registros activos en la base de datos y los enlaces navegan a las secciones correctas.',
            ],
            [
                'id' => 'sesion_seguridad',
                'tipo' => 'Seguridad',
                'titulo' => 'Aislamiento de Sesión y Verificación de Rol Activo',
                'objetivo' => 'Confirmar que el usuario logueado visualiza únicamente los elementos autorizados para su rol.',
                'pasos' => [
                    'Examine el menú lateral según el rol actual (Superadmin, Editor, Lector o Atención).',
                    'Si el rol es Lector, confirme que aparece el aviso "Modo solo lectura".',
                    'Si el rol es Atención, confirme que sólo tiene acceso a Atención en Vivo / Chat.'
                ],
                'esperado' => 'Menú lateral y permisos blindados según la matriz RBAC institucional.',
            ],
            [
                'id' => 'atajos_directos',
                'tipo' => 'Funcional',
                'titulo' => 'Navegación Rápida a Módulos Operativos',
                'objetivo' => 'Comprobar que los botones de acción rápida conducen a la pantalla correcta sin demoras.',
                'pasos' => [
                    'Pruebe el botón "Portal público" en la barra superior para abrir la web comunal en nueva pestaña.',
                    'Valide que el menú de perfil permite acceder a "Mi perfil" y cerrar sesión.'
                ],
                'esperado' => 'Apertura fluida en nueva pestaña sin desloguear al administrador.',
            ]
        ]
    ],
    'productos' => [
        'nombre' => 'Bolsas y Combos Solidarios',
        'icono' => 'bi-bag-heart',
        'color' => '#10b981',
        'pruebas' => [
            [
                'id' => 'crear_combo',
                'tipo' => 'Critico',
                'titulo' => 'Creación y Validación de Nuevo Combo Solidario',
                'objetivo' => 'Registrar un combo de prueba con rubro asignado, precio en USD y detalle de contenido.',
                'pasos' => [
                    'Haga clic en el botón "Nuevo combo".',
                    'Llene el formulario con nombre representativo, rubro vinculado, precio (ej. 15.00) y descripción.',
                    'Guarde el formulario y compruebe el mensaje de éxito.'
                ],
                'esperado' => 'El combo aparece en el listado y queda disponible para su visibilidad comunal.',
            ],
            [
                'id' => 'historial_precios',
                'tipo' => 'Regresion',
                'titulo' => 'Auditoría Automática de Modificación de Precios',
                'objetivo' => 'Comprobar que al modificar el precio de un combo se genera un registro en el historial.',
                'pasos' => [
                    'Edite un combo existente y modifique su precio en USD (ejemplo: incremente $1.00).',
                    'Guarde los cambios.',
                    'Diríjase a "Historial de precios" en el menú Catálogo.'
                ],
                'esperado' => 'Se observa el registro con fecha, usuario responsable, precio anterior y precio nuevo.',
            ],
            [
                'id' => 'toggle_activo',
                'tipo' => 'Funcional',
                'titulo' => 'Activación / Desactivación de Combos',
                'objetivo' => 'Validar que combos inactivos se ocultan de las consultas públicas y de la IA.',
                'pasos' => [
                    'Cambie un combo a estado "Inactivo".',
                    'Abra el portal público y verifique que dicho combo no aparece en la sección #combos.',
                    'Reactívelo nuevamente.'
                ],
                'esperado' => 'La visibilidad se sincroniza de inmediato en tiempo real.',
            ]
        ]
    ],
    'rubros' => [
        'nombre' => 'Rubros Agrícolas de La Grita',
        'icono' => 'bi-tags',
        'color' => '#15803d',
        'pruebas' => [
            [
                'id' => 'registro_rubro',
                'tipo' => 'Critico',
                'titulo' => 'Registro de Rubro con Rendimiento y Categoría',
                'objetivo' => 'Añadir un rubro autóctono indicando tipo (hortaliza, tubérculo, etc.) y rendimiento promedio.',
                'pasos' => [
                    'Pulse "Nuevo rubro" y complete los campos obligatorios.',
                    'Verifique la validación de campos vacíos intentando guardar en blanco.',
                    'Guarde con datos válidos y confirme que aparece en el catálogo.'
                ],
                'esperado' => 'Registro insertado con éxito y badge de categoría con color correspondiente.',
            ],
            [
                'id' => 'filtro_rubros',
                'tipo' => 'Funcional',
                'titulo' => 'Buscador y Paginación de Rubros',
                'objetivo' => 'Verificar filtrado por nombre y temporada de siembra.',
                'pasos' => [
                    'Escriba "Papa" o "Zanahoria" en la barra de búsqueda rápida.',
                    'Presione enter o filtrar y confirme que los resultados coinciden.',
                    'Presione "Limpiar" para restaurar la vista completa.'
                ],
                'esperado' => 'Resultados filtrados al instante sin perder parámetros GET.',
            ]
        ]
    ],
    'entregas' => [
        'nombre' => 'Entregas y Cronograma Comunal',
        'icono' => 'bi-truck',
        'color' => '#0284c7',
        'pruebas' => [
            [
                'id' => 'programar_ruta',
                'tipo' => 'Critico',
                'titulo' => 'Planificación y Programación de Nueva Entrega',
                'objetivo' => 'Crear una entrega asignando fecha, sector de Jáuregui, responsable y cantidad de bolsas.',
                'pasos' => [
                    'Haga clic en "Nueva entrega" o "Programar".',
                    'Seleccione una fecha futura, sector (ej. Quebrada de San José) y responsable comunitario.',
                    'Guarde y verifique que figure en estado "Programada".'
                ],
                'esperado' => 'La ruta aparece en el cronograma administrativo y en la sección pública #cronograma.',
            ],
            [
                'id' => 'cambio_estado_entrega',
                'tipo' => 'Funcional',
                'titulo' => 'Transición de Estado: Programada -> En Ruta -> Completada',
                'objetivo' => 'Validar flujo de ciclo de vida de la distribución comunal.',
                'pasos' => [
                    'Abra la edición de una entrega.',
                    'Cambie el estado a "En ruta" y guarde.',
                    'Posteriormente, márquela como "Completada" y agregue observaciones si aplica.'
                ],
                'esperado' => 'El badge de estado cambia visualmente de color y se registra la auditoría.',
            ],
            [
                'id' => 'filtros_periodo_entregas',
                'tipo' => 'Regresion',
                'titulo' => 'Navegación de Periodo (Semana / Mes / Año)',
                'objetivo' => 'Probar botones rápidos de fecha y flechas de semana anterior / siguiente.',
                'pasos' => [
                    'Haga clic en "Semana", luego en la flecha izquierda (periodo anterior).',
                    'Haga clic en el botón circular central para volver a la semana actual.',
                    'Compruebe que no se generan advertencias PHP en la cabecera.'
                ],
                'esperado' => 'La fecha cambia matemáticamente de 7 en 7 días sin warnings.',
            ]
        ]
    ],
    'productores' => [
        'nombre' => 'UPF y Productores (Literal G)',
        'icono' => 'bi-person-lines-fill',
        'color' => '#059669',
        'pruebas' => [
            [
                'id' => 'modal_seguimiento_guardar',
                'tipo' => 'Critico',
                'titulo' => 'Gestión de Comunicación y Guardado de Seguimiento',
                'objetivo' => 'Comprobar que el modal de comunicación actualiza el estado y notas internas sin warnings PHP.',
                'pasos' => [
                    'Ubique el primer registro de comunicación de la tabla y haga clic en "Ver Detalle y Seguimiento".',
                    'En el modal, cambie el estado (ej. de Pendiente a "En revisión" o "Atendido").',
                    'Escriba una breve nota en "Respuesta interna y acuerdos pactados".',
                    'Haga clic en "Guardar Seguimiento".'
                ],
                'esperado' => 'Guarda con éxito, muestra mensaje verde, sin ningún warning ni undefined array key, y el estado se actualiza en la tabla.',
            ],
            [
                'id' => 'filtros_tipo_kpi',
                'tipo' => 'Funcional',
                'titulo' => 'Filtrado por Oferta de Rubros vs. Requerimientos Técnicos',
                'objetivo' => 'Verificar que las tarjetas KPI superiores filtran la lista de productores correctamente.',
                'pasos' => [
                    'Haga clic en la tarjeta "Ofertas de rubros" (verde).',
                    'Compruebe que la tabla solo muestra ofertas de cosecha con el badge de filtro activo.',
                    'Haga clic en la tarjeta "Requerimientos técnicos" (morada) y valide la alternancia.'
                ],
                'esperado' => 'Filtro segmentado reactivo con conteo fiel.',
            ],
            [
                'id' => 'enlace_whatsapp',
                'tipo' => 'UX',
                'titulo' => 'Acción Rápida de Contacto Telefónico y WhatsApp',
                'objetivo' => 'Validar que el enlace directo a WhatsApp contiene el formato de número internacional adecuado.',
                'pasos' => [
                    'Abra el modal de detalle de un productor.',
                    'Verifique el botón "Abrir Chat en WhatsApp" en el pie del modal.',
                    'Inspeccione que el enlace apunte a api.whatsapp.com/send con el teléfono normalizado.'
                ],
                'esperado' => 'Apertura de WhatsApp con el número del productor listo para entablar comunicación.',
            ]
        ]
    ],
    'chat' => [
        'nombre' => 'Atención en Vivo · Chat Comunal',
        'icono' => 'bi-chat-dots-fill',
        'color' => '#0d9488',
        'pruebas' => [
            [
                'id' => 'chat_toma_control',
                'tipo' => 'Critico',
                'titulo' => 'Toma de Control Humano y Devolución a la IA',
                'objetivo' => 'Verificar alternancia fluida entre el asistente virtual de la EPSDC y el operador humano.',
                'pasos' => [
                    'Seleccione una conversación en la lista izquierda.',
                    'En la barra superior del chat, haga clic en "Tomar control" si está en modo IA.',
                    'Escriba un mensaje como operador y presione Enviar.',
                    'Haga clic en "Devolver a IA" y confirme en la alerta centralizada.'
                ],
                'esperado' => 'El badge cambia entre "Modo Operador" y "Atendido por IA"; el diálogo centralizado confirma la acción.',
            ],
            [
                'id' => 'chat_navegacion_ia',
                'tipo' => 'Funcional',
                'titulo' => 'Orientación de Navegación del Portal Web por la IA',
                'objetivo' => 'Comprobar que la IA orienta a los visitantes con enlaces a las secciones del portal.',
                'pasos' => [
                    'Abra el chat público como visitante en una pestaña privada.',
                    'Pregunte: "¿Dónde puedo ver los combos y sus precios?" o "¿Cómo hago para arrimar cosechas como productor?".',
                    'Observe la respuesta generada por la IA.'
                ],
                'esperado' => 'La IA responde amablemente proporcionando enlaces markdown navegables ([Ver Combos](#combos), [Portal de Productores](#productores), etc.).',
            ],
            [
                'id' => 'chat_rol_atencion',
                'tipo' => 'Seguridad',
                'titulo' => 'Aislamiento Estricto para el Rol "atención"',
                'objetivo' => 'Validar que usuarios con rol atención solo tienen acceso a la consola de chat.',
                'pasos' => [
                    'Inicie sesión con atencion@lanuevaesperanza.com (clave: Atencion2026*).',
                    'Verifique que ingresa directamente a admin/chat.php.',
                    'Intente navegar manualmente a admin/index.php o admin/usuarios.php.'
                ],
                'esperado' => 'El sistema redirige forzosamente a admin/chat.php garantizando el principio de menor privilegio.',
            ]
        ]
    ],
    'publicaciones' => [
        'nombre' => 'Información y Prensa Comunal',
        'icono' => 'bi-megaphone',
        'color' => '#e11d48',
        'pruebas' => [
            [
                'id' => 'crear_publicacion',
                'tipo' => 'Critico',
                'titulo' => 'Publicación de Comunicado Oficial',
                'objetivo' => 'Crear un comunicado de asamblea o jornada comunal.',
                'pasos' => [
                    'Presione "Nueva publicación".',
                    'Complete título, extracto, contenido y fecha de publicación.',
                    'Guarde y verifique el listado.'
                ],
                'esperado' => 'Comunicado indexado con estado publicado o borrador.',
            ]
        ]
    ],
    'estatutos' => [
        'nombre' => 'Estatutos y Marco Legal',
        'icono' => 'bi-journal-text',
        'color' => '#4f46e5',
        'pruebas' => [
            [
                'id' => 'estatutos_visor',
                'tipo' => 'Funcional',
                'titulo' => 'Visualización de Estatutos Comunitarios',
                'objetivo' => 'Verificar que la normativa comunal se visualiza correctamente para la consulta ciudadana.',
                'pasos' => [
                    'Revise la lista de artículos o documentos estatutarios cargados.',
                    'Compruebe el visor público en estatutos.php.'
                ],
                'esperado' => 'Documentos legibles con formato institucional.',
            ]
        ]
    ],
    'usuarios' => [
        'nombre' => 'Usuarios y Control de Acceso (RBAC)',
        'icono' => 'bi-people',
        'color' => '#7c3aed',
        'pruebas' => [
            [
                'id' => 'crear_usuario_roles',
                'tipo' => 'Seguridad',
                'titulo' => 'Creación de Usuario con Rol "atención"',
                'objetivo' => 'Comprobar que el formulario de creación de usuario incluye y guarda el nuevo rol "atención".',
                'pasos' => [
                    'Vaya a "Nuevo usuario".',
                    'Despliegue la lista de roles y verifique la opción "Atención (solo chat en vivo)".',
                    'Cree un usuario de prueba con este rol y verifique su registro en la tabla.'
                ],
                'esperado' => 'Usuario guardado exitosamente con el rol "atención" y badge distintivo.',
            ],
            [
                'id' => 'auditoria_usuario',
                'tipo' => 'Regresion',
                'titulo' => 'Registro de Creación en Auditoría',
                'objetivo' => 'Comprobar que la acción de crear o editar usuarios genera traza en auditoría.',
                'pasos' => [
                    'Consulte el módulo de Auditoría.',
                    'Verifique la fila con acción "crear" o "editar" en la tabla "usuarios".'
                ],
                'esperado' => 'Trazabilidad con fecha, IP y usuario responsable.',
            ]
        ]
    ],
    'configuracion' => [
        'nombre' => 'Configuración Institucional y Motor IA',
        'icono' => 'bi-gear',
        'color' => '#0d9488',
        'pruebas' => [
            [
                'id' => 'motor_ia_switch',
                'tipo' => 'Critico',
                'titulo' => 'Alternancia de Motor de IA (Gemini / DeepSeek / Local)',
                'objetivo' => 'Probar selección de proveedor y guardado de credenciales del motor virtual.',
                'pasos' => [
                    'Seleccione el proveedor de IA deseado.',
                    'Pruebe el botón de mostrar/ocultar clave de API con el icono de ojo.',
                    'Haga clic en "Probar Conexión con IA" para comprobar conectividad.'
                ],
                'esperado' => 'El indicador en vivo confirma el estado del motor y la prueba retorna respuesta exitosa.',
            ],
            [
                'id' => 'datos_institucionales',
                'tipo' => 'Funcional',
                'titulo' => 'Modificación de Identidad de la EPSDC',
                'objetivo' => 'Actualizar datos de sede, teléfono de guardia o lema institucional.',
                'pasos' => [
                    'Modifique el teléfono oficial o dirección de la EPSDC.',
                    'Guarde los cambios.',
                    'Compruebe que el pie de página público y el prompt de la IA adoptan los nuevos datos.'
                ],
                'esperado' => 'Propagación inmediata de los parámetros globales en el portal.',
            ]
        ]
    ],
    'auditoria' => [
        'nombre' => 'Auditoría y Trazabilidad',
        'icono' => 'bi-shield-check',
        'color' => '#334155',
        'pruebas' => [
            [
                'id' => 'auditoria_eventos',
                'tipo' => 'Seguridad',
                'titulo' => 'Inspección de Registro Histórico de Acciones',
                'objetivo' => 'Verificar que todas las mutaciones críticas quedan debidamente auditadas.',
                'pasos' => [
                    'Filtre los registros por acción (crear, editar, eliminar, atender).',
                    'Verifique que se registran las direcciones IP reales y los IDs correspondientes.'
                ],
                'esperado' => 'Registro cronológico inmutable sin saltos de secuencia.',
            ]
        ]
    ]
];

// Si la sección actual no tiene suite directa, predeterminar a index
$zonaActiva = isset($zonasDisponibles[$seccionQA]) ? $seccionQA : 'index';
?>

<!-- OFFCANVAS DRAWER: GUÍA INTERACTIVA DE PRUEBAS QA -->
<div class="offcanvas offcanvas-end shadow-2xl border-start border-slate-200" tabindex="-1" id="drawerGuiaPruebas" aria-labelledby="drawerGuiaPruebasLabel" style="width: 520px; max-width: 95vw; background: #f8fafc; z-index: 1060;">
    
    <!-- Encabezado del Drawer -->
    <div class="offcanvas-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2.5">
            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 38px; height: 38px; background: #ecfdf5; color: #059669;">
                <i class="bi bi-patch-check-fill fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-slate-900 mb-0" id="drawerGuiaPruebasLabel" style="font-size: 1rem; letter-spacing: -0.01em;">Guía Interactiva de Pruebas</h5>
                <span class="text-slate-400 small" style="font-size: 0.72rem;">Control de calidad y verificación continua · EPSDC</span>
            </div>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <!-- Barra de Selección de Zona y Progreso General -->
    <div class="bg-white border-bottom p-3 px-4">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <label class="form-label small fw-bold text-slate-700 mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                <i class="bi bi-compass-fill text-primary"></i> Zona del Sistema:
            </label>
            <select id="selectZonaGuia" class="form-select form-select-sm fw-semibold shadow-none text-slate-800" style="width: auto; max-width: 250px; border-color: #cbd5e1; border-radius: 8px; font-size: 0.78rem;" onchange="cambiarZonaGuiaPruebas(this.value)">
                <?php foreach ($zonasDisponibles as $key => $info): ?>
                    <option value="<?= e($key) ?>" <?= $key === $zonaActiva ? 'selected' : '' ?>>
                        <?= e($info['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Barra de Progreso de la Zona -->
        <div class="pt-2">
            <div class="d-flex align-items-center justify-content-between small text-slate-500 mb-1" style="font-size: 0.74rem;">
                <span id="labelProgresoZona" class="fw-semibold text-slate-700">Progreso en esta zona</span>
                <span id="conteoProgresoZona" class="font-monospace fw-bold text-emerald-700">0 / 0 completadas</span>
            </div>
            <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 999px;">
                <div id="barraProgresoZona" class="progress-bar bg-success transition-all duration-300" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <!-- Contenedor Desplazable de Casos de Prueba -->
    <div class="offcanvas-body p-4 overflow-y-auto" id="contenedorCasosPrueba">
        <?php foreach ($zonasDisponibles as $key => $info): ?>
            <div class="suite-zona-wrapper <?= $key === $zonaActiva ? '' : 'd-none' ?>" id="suite_zona_<?= e($key) ?>">
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background: <?= $info['color'] ?>15; color: <?= $info['color'] ?>;">
                            <i class="bi <?= $info['icono'] ?> me-1"></i><?= e($info['nombre']) ?>
                        </span>
                        <span class="small text-slate-400" style="font-size: 0.75rem;"><?= count($info['pruebas']) ?> escenarios</span>
                    </div>
                    <button type="button" onclick="reiniciarPruebasZona('<?= e($key) ?>')" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs" title="Reiniciar marcas de verificación">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reiniciar
                    </button>
                </div>

                <!-- Lista de Escenarios -->
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($info['pruebas'] as $idx => $p): 
                        $tipoBadge = match($p['tipo']) {
                            'Critico' => 'bg-danger-subtle text-danger border-danger-subtle',
                            'Seguridad' => 'bg-purple-subtle text-purple-700 border-purple-subtle',
                            'Regresion' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                            'UX' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                            default => 'bg-emerald-subtle text-emerald-800 border-emerald-subtle'
                        };
                    ?>
                        <div class="card border rounded-3 p-3 bg-white shadow-2xs item-prueba-card" id="card_prueba_<?= e($key) ?>_<?= e($p['id']) ?>" style="border-color: #e2e8f0;">
                            
                            <!-- Cabecera de la prueba -->
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="form-check pt-0.5">
                                    <input class="form-check-input check-prueba-item" type="checkbox" id="chk_<?= e($key) ?>_<?= e($p['id']) ?>" onchange="togglePruebaCheck('<?= e($key) ?>', '<?= e($p['id']) ?>', this.checked)" style="width: 1.15rem; height: 1.15rem; cursor: pointer; border-color: #cbd5e1;">
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1">
                                        <span class="badge <?= $tipoBadge ?> border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;"><?= e(strtoupper($p['tipo'])) ?></span>
                                        <span class="text-slate-400 small" style="font-size: 0.7rem;">#<?= $idx + 1 ?></span>
                                    </div>
                                    <h6 class="fw-bold text-slate-800 mb-1 titulo-prueba-text" style="font-size: 0.88rem;"><?= e($p['titulo']) ?></h6>
                                    <p class="text-slate-500 small mb-2" style="font-size: 0.78rem; line-height: 1.4;"><?= e($p['objetivo']) ?></p>
                                </div>
                            </div>

                            <!-- Desplegable con Pasos y Resultado Esperado -->
                            <div class="bg-slate-50 border rounded-2 p-2.5 mt-2" style="border-color: #f1f5f9;">
                                <div class="small fw-semibold text-slate-700 mb-1.5 d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                    <i class="bi bi-list-ol text-primary"></i> Pasos guiados:
                                </div>
                                <ol class="ps-3 mb-2 small text-slate-600" style="font-size: 0.74rem; line-height: 1.45;">
                                    <?php foreach ($p['pasos'] as $paso): ?>
                                        <li class="mb-0.5"><?= e($paso) ?></li>
                                    <?php endforeach; ?>
                                </ol>
                                <div class="border-top pt-1.5 mt-1.5 small text-emerald-800 d-flex align-items-start gap-1.5" style="font-size: 0.73rem; border-color: #e2e8f0;">
                                    <i class="bi bi-check-circle-fill text-success shrink-0 mt-0.5"></i>
                                    <span><strong>Resultado esperado:</strong> <?= e($p['esperado']) ?></span>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pie del Drawer: Diagnóstico del Entorno -->
    <div class="offcanvas-footer bg-white border-top p-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 0.74rem;">
        <div class="text-slate-500 d-flex align-items-center gap-2">
            <span><i class="bi bi-cpu me-1 text-slate-400"></i>PHP <?= PHP_VERSION ?></span>
            <span>•</span>
            <span class="text-emerald-700 fw-semibold"><i class="bi bi-database-check me-1"></i>MySQL Activo</span>
        </div>
        <div class="text-slate-400">
            <span class="badge bg-slate-100 text-slate-700 border"><?= e($u['rol'] ?? 'operador') ?></span>
        </div>
    </div>

</div>

<!-- SCRIPT DE INTERACCIÓN Y PERSISTENCIA DE PRUEBAS -->
<script>
(function() {
    const STORAGE_KEY_PREFIX = 'lne_qa_test_';

    window.cambiarZonaGuiaPruebas = function(zona) {
        document.querySelectorAll('.suite-zona-wrapper').forEach(el => el.classList.add('d-none'));
        const target = document.getElementById('suite_zona_' + zona);
        if (target) target.classList.remove('d-none');
        actualizarProgresoZona(zona);
    };

    window.togglePruebaCheck = function(zona, id, checked) {
        const key = STORAGE_KEY_PREFIX + zona + '_' + id;
        if (checked) {
            localStorage.setItem(key, '1');
        } else {
            localStorage.removeItem(key);
        }
        aplicarEstiloPruebaCard(zona, id, checked);
        actualizarProgresoZona(zona);
        actualizarProgresoGlobal();
    };

    window.reiniciarPruebasZona = function(zona) {
        if (!confirm('¿Desea reiniciar todas las marcas de prueba para esta zona?')) return;
        const checks = document.querySelectorAll('#suite_zona_' + zona + ' .check-prueba-item');
        checks.forEach(chk => {
            chk.checked = false;
            const id = chk.id.replace('chk_' + zona + '_', '');
            localStorage.removeItem(STORAGE_KEY_PREFIX + zona + '_' + id);
            aplicarEstiloPruebaCard(zona, id, false);
        });
        actualizarProgresoZona(zona);
        actualizarProgresoGlobal();
    };

    function aplicarEstiloPruebaCard(zona, id, checked) {
        const card = document.getElementById('card_prueba_' + zona + '_' + id);
        if (!card) return;
        const titulo = card.querySelector('.titulo-prueba-text');
        if (checked) {
            card.classList.add('bg-emerald-50/40', 'border-emerald-300');
            if (titulo) titulo.classList.add('text-decoration-line-through', 'text-slate-400');
        } else {
            card.classList.remove('bg-emerald-50/40', 'border-emerald-300');
            if (titulo) titulo.classList.remove('text-decoration-line-through', 'text-slate-400');
        }
    }

    function actualizarProgresoZona(zona) {
        const suite = document.getElementById('suite_zona_' + zona);
        if (!suite) return;
        const checks = suite.querySelectorAll('.check-prueba-item');
        const total = checks.length;
        let completadas = 0;
        checks.forEach(chk => {
            if (chk.checked) completadas++;
        });

        const pct = total > 0 ? Math.round((completadas / total) * 100) : 0;
        const barra = document.getElementById('barraProgresoZona');
        const conteo = document.getElementById('conteoProgresoZona');
        if (barra) {
            barra.style.width = pct + '%';
            barra.setAttribute('aria-valuenow', pct);
        }
        if (conteo) {
            conteo.textContent = `${completadas} / ${total} completadas (${pct}%)`;
        }
    }

    function actualizarProgresoGlobal() {
        const allChecks = document.querySelectorAll('.check-prueba-item');
        const total = allChecks.length;
        let completadas = 0;
        allChecks.forEach(chk => {
            if (chk.checked) completadas++;
        });
        const pct = total > 0 ? Math.round((completadas / total) * 100) : 0;
        const pillGlobal = document.getElementById('guiaPillGlobal');
        if (pillGlobal) {
            pillGlobal.textContent = pct + '%';
            if (pct === 100) {
                pillGlobal.className = 'badge bg-success text-white rounded-pill ms-1 font-monospace';
            } else if (pct > 0) {
                pillGlobal.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1 font-monospace';
            } else {
                pillGlobal.className = 'badge bg-light text-secondary border rounded-pill ms-1 font-monospace';
            }
        }
    }

    // Inicializar estados desde localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const allChecks = document.querySelectorAll('.check-prueba-item');
        allChecks.forEach(chk => {
            const parts = chk.id.replace('chk_', '').split('_');
            const zona = parts[0];
            const id = parts.slice(1).join('_');
            const key = STORAGE_KEY_PREFIX + zona + '_' + id;
            if (localStorage.getItem(key) === '1') {
                chk.checked = true;
                aplicarEstiloPruebaCard(zona, id, true);
            }
        });

        const sel = document.getElementById('selectZonaGuia');
        if (sel) {
            actualizarProgresoZona(sel.value);
        }
        actualizarProgresoGlobal();
    });
})();
</script>
