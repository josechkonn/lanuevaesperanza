<?php
/**
 * Paginación por CURSOR (keyset) + filtros rápidos de fecha.
 *
 * Todas las tablas del panel usan este archivo. El cursor codifica el par
 * (valor_columna_orden, id) de la última/primera fila visible, así que la
 * consulta usa el índice (col, id) y nunca hace OFFSET.
 *
 * Uso típico:
 *   $f   = filtro_fechas('semana');                         // lee ?rango=&p=&desde=&hasta=
 *   $w   = []; $prm = [];
 *   aplicar_fechas($f, 'e.fecha_entrega', $w, $prm, true);  // true = columna DATE
 *   $res = paginar_cursor([
 *       'select' => 'SELECT e.* FROM entregas e',
 *       'where'  => $w, 'params' => $prm,
 *       'col'    => 'e.fecha_entrega', 'key' => 'fecha_entrega',
 *       'id'     => 'e.id',
 *   ]);
 *   // $res['filas'], $res['siguiente'], $res['anterior'], $res['total']
 */

// ============================================================ Cursor
function cursor_codificar($valor, int $id): string
{
    return rtrim(strtr(base64_encode(json_encode([$valor, $id])), '+/', '-_'), '=');
}

function cursor_decodificar(?string $c): ?array
{
    if (!$c || strlen($c) > 200) return null;
    $json = base64_decode(strtr($c, '-_', '+/'), true);
    $d = $json ? json_decode($json, true) : null;
    if (!is_array($d) || count($d) !== 2 || !is_int($d[1])) return null;
    if (!is_scalar($d[0]) && $d[0] !== null) return null;
    return $d;
}

/**
 * @param array $o select, where[], params[], col, key, id, orden ('DESC'|'ASC'), limite
 * @return array filas, siguiente (cursor|null), anterior (cursor|null), total, limite
 */
function paginar_cursor(array $o): array
{
    global $CONFIG;
    $limite = (int)($o['limite'] ?? $CONFIG['por_pagina']);
    $orden  = strtoupper($o['orden'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $col    = $o['col'];
    $idCol  = $o['id'];
    $key    = $o['key'];
    $where  = $o['where'] ?? [];
    $params = $o['params'] ?? [];

    // Total con los mismos filtros (sin cursor) para mostrar "N resultados"
    $sqlCount = 'SELECT COUNT(*) FROM (' . $o['select'] . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ') t';
    $st = db()->prepare($sqlCount);
    $st->execute($params);
    $total = (int)$st->fetchColumn();

    $cursor = cursor_decodificar($_GET['cursor'] ?? null);
    $atras  = ($_GET['dir'] ?? 'next') === 'prev' && $cursor !== null;

    // Dirección efectiva de la consulta
    $asc = ($orden === 'ASC') xor $atras;
    $op  = $asc ? '>' : '<';
    $ord = $asc ? 'ASC' : 'DESC';

    if ($cursor !== null) {
        $where[] = "($col $op :_cv OR ($col = :_cv2 AND $idCol $op :_ci))";
        $params['_cv']  = $cursor[0];
        $params['_cv2'] = $cursor[0];
        $params['_ci']  = $cursor[1];
    }

    $sql = $o['select']
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . " ORDER BY $col $ord, $idCol $ord LIMIT " . ($limite + 1);

    $st = db()->prepare($sql);
    $st->execute($params);
    $filas = $st->fetchAll();

    $hayMas = count($filas) > $limite;
    if ($hayMas) array_pop($filas);
    if ($atras) $filas = array_reverse($filas);

    $siguiente = $anterior = null;
    if ($filas) {
        $primera = $filas[0];
        $ultima  = $filas[count($filas) - 1];
        $idKey   = $o['id_key'] ?? 'id';
        if ($atras) {
            $siguiente = cursor_codificar($ultima[$key], (int)$ultima[$idKey]);
            $anterior  = $hayMas ? cursor_codificar($primera[$key], (int)$primera[$idKey]) : null;
        } else {
            $siguiente = $hayMas ? cursor_codificar($ultima[$key], (int)$ultima[$idKey]) : null;
            $anterior  = $cursor !== null ? cursor_codificar($primera[$key], (int)$primera[$idKey]) : null;
        }
    }

    return compact('filas', 'siguiente', 'anterior', 'total', 'limite');
}

/** Botones Anterior / Siguiente que conservan todos los filtros activos. */
function render_paginador(array $res): string
{
    $n = count($res['filas']);
    $prev = $res['anterior']
        ? '<a class="btn btn-outline-secondary btn-sm" href="' . e(qs(['cursor' => $res['anterior'], 'dir' => 'prev'])) . '"><i class="bi bi-chevron-left"></i> Anterior</a>'
        : '<button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-chevron-left"></i> Anterior</button>';
    $next = $res['siguiente']
        ? '<a class="btn btn-outline-secondary btn-sm" href="' . e(qs(['cursor' => $res['siguiente'], 'dir' => 'next'])) . '">Siguiente <i class="bi bi-chevron-right"></i></a>'
        : '<button class="btn btn-outline-secondary btn-sm" disabled>Siguiente <i class="bi bi-chevron-right"></i></button>';
    $primera = !empty($_GET['cursor'])
        ? '<a class="btn btn-link btn-sm text-decoration-none" href="' . e(qs(['cursor' => null, 'dir' => null])) . '"><i class="bi bi-skip-start"></i> Inicio</a>'
        : '';

    return '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-top paginador">'
        . '<small class="text-secondary">Mostrando <strong>' . $n . '</strong> de <strong>' . number_format($res['total'], 0, ',', '.') . '</strong> resultados</small>'
        . '<div class="d-flex gap-2 align-items-center">' . $primera . $prev . $next . '</div></div>';
}

// ============================================================ Filtros de fecha
const RANGOS = ['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes', 'anio' => 'Año', 'todo' => 'Todo'];

/**
 * Lee ?rango=hoy|semana|mes|anio|todo|custom, ?p=desplazamiento (-1 = periodo anterior),
 * ?desde=YYYY-MM-DD&hasta=YYYY-MM-DD.  Devuelve desde/hasta (inclusive) y etiqueta.
 */
function filtro_fechas(string $porDefecto = 'todo'): array
{
    $rango = $_GET['rango'] ?? $porDefecto;
    $p     = max(-520, min(520, (int)($_GET['p'] ?? 0)));
    $re    = '/^\d{4}-\d{2}-\d{2}$/';
    $desdeIn = (isset($_GET['desde']) && preg_match($re, $_GET['desde'])) ? $_GET['desde'] : null;
    $hastaIn = (isset($_GET['hasta']) && preg_match($re, $_GET['hasta'])) ? $_GET['hasta'] : null;

    if ($desdeIn || $hastaIn) {
        $rango = 'custom';
    } elseif (!isset(RANGOS[$rango])) {
        $rango = $porDefecto;
    }

    $hoy = new DateTimeImmutable('today');
    $desde = $hasta = null;
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    switch ($rango) {
        case 'hoy':
            $desde = $hasta = $hoy->modify("$p day");
            $etiqueta = $p === 0 ? 'Hoy, ' . $desde->format('d/m/Y') : $desde->format('d/m/Y');
            break;
        case 'semana':
            $desde = $hoy->modify('monday this week')->modify(($p * 7) . ' day');
            $hasta = $desde->modify('+6 day');
            $etiqueta = 'Semana ' . $desde->format('d/m') . ' – ' . $hasta->format('d/m/Y');
            break;
        case 'mes':
            $desde = $hoy->modify('first day of this month')->modify("$p month");
            $hasta = $desde->modify('last day of this month');
            $etiqueta = ucfirst($meses[(int)$desde->format('n')]) . ' ' . $desde->format('Y');
            break;
        case 'anio':
            $desde = $hoy->setDate((int)$hoy->format('Y') + $p, 1, 1);
            $hasta = $desde->setDate((int)$desde->format('Y'), 12, 31);
            $etiqueta = 'Año ' . $desde->format('Y');
            break;
        case 'custom':
            $desde = $desdeIn ? new DateTimeImmutable($desdeIn) : null;
            $hasta = $hastaIn ? new DateTimeImmutable($hastaIn) : null;
            if ($desde && $hasta && $desde > $hasta) [$desde, $hasta] = [$hasta, $desde];
            $etiqueta = ($desde ? $desde->format('d/m/Y') : '…') . ' – ' . ($hasta ? $hasta->format('d/m/Y') : '…');
            break;
        default:
            $etiqueta = 'Todas las fechas';
    }

    return [
        'rango'    => $rango,
        'p'        => $p,
        'desde'    => $desde ? $desde->format('Y-m-d') : null,
        'hasta'    => $hasta ? $hasta->format('Y-m-d') : null,
        'etiqueta' => $etiqueta,
    ];
}

/** Agrega las condiciones de fecha a $where/$params. $esDate=true si la columna es DATE. */
function aplicar_fechas(array $f, string $col, array &$where, array &$params, bool $esDate = false): void
{
    if ($f['desde']) {
        $where[] = "$col >= :_fdesde";
        $params['_fdesde'] = $esDate ? $f['desde'] : $f['desde'] . ' 00:00:00';
    }
    if ($f['hasta']) {
        if ($esDate) {
            $where[] = "$col <= :_fhasta";
            $params['_fhasta'] = $f['hasta'];
        } else {
            $where[] = "$col < :_fhasta";
            $params['_fhasta'] = (new DateTimeImmutable($f['hasta']))->modify('+1 day')->format('Y-m-d 00:00:00');
        }
    }
}

/**
 * Barra de filtros: botones rápidos Hoy/Semana/Mes/Año/Todo, flechas de periodo
 * anterior/siguiente, rango personalizado y slot para filtros extra (búsqueda, estado…).
 */
function render_filtros(array $f, string $extra = ''): string
{
    $btns = '';
    foreach (RANGOS as $k => $txt) {
        $activo = $f['rango'] === $k ? ' active' : '';
        $btns .= '<a class="btn btn-sm btn-outline-secondary' . $activo . '" href="'
            . e(qs(['rango' => $k, 'p' => null, 'desde' => null, 'hasta' => null, 'cursor' => null, 'dir' => null]))
            . '">' . $txt . '</a>';
    }

    $flechas = '';
    if (in_array($f['rango'], ['hoy', 'semana', 'mes', 'anio'], true)) {
        $flechas = '<div class="btn-group btn-group-sm ms-1" role="group">'
            . '<a class="btn btn-outline-secondary" title="Periodo anterior" href="' . e(qs(['p' => $f['p'] - 1, 'cursor' => null, 'dir' => null])) . '"><i class="bi bi-chevron-left"></i></a>'
            . ($f['p'] !== 0 ? '<a class="btn btn-outline-secondary" title="Volver al actual" href="' . e(qs(['p' => null, 'cursor' => null, 'dir' => null])) . '"><i class="bi bi-record-circle"></i></a>' : '')
            . '<a class="btn btn-outline-secondary" title="Periodo siguiente" href="' . e(qs(['p' => $f['p'] + 1, 'cursor' => null, 'dir' => null])) . '"><i class="bi bi-chevron-right"></i></a>'
            . '</div>';
    }

    // Conserva los demás parámetros GET (búsqueda, estado…) dentro del formulario de rango
    $ocultos = '';
    foreach ($_GET as $k => $v) {
        if (in_array($k, ['desde', 'hasta', 'rango', 'p', 'cursor', 'dir'], true) || !is_string($v)) continue;
        $ocultos .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }

    $desdeVal = $f['rango'] === 'custom' ? $f['desde'] : '';
    $hastaVal = $f['rango'] === 'custom' ? $f['hasta'] : '';

    return '<div class="filtros card-body border-bottom p-3 p-md-4">'
        . '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">'
        . '  <div class="d-flex flex-wrap align-items-center gap-1.5">'
        . '    <span class="small fw-bold text-muted text-uppercase tracking-wider me-1" style="font-size:0.68rem"><i class="bi bi-clock-history me-1"></i>Periodo:</span>'
        . '    <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Rango rápido">' . $btns . '</div>'
        .      $flechas
        . '  </div>'
        . '  <span class="badge bg-white text-dark border px-3 py-1.5 rounded-pill shadow-2xs font-monospace small"><i class="bi bi-calendar-check me-1.5 text-success"></i>' . e($f['etiqueta']) . '</span>'
        . '</div>'
        . '<form method="get" class="row g-2 align-items-end">' . $ocultos
        . '<div class="col-6 col-md-auto"><label class="form-label small mb-1 fw-semibold text-muted" style="font-size:0.72rem">Desde</label><input type="date" name="desde" value="' . e($desdeVal) . '" class="form-control form-control-sm"></div>'
        . '<div class="col-6 col-md-auto"><label class="form-label small mb-1 fw-semibold text-muted" style="font-size:0.72rem">Hasta</label><input type="date" name="hasta" value="' . e($hastaVal) . '" class="form-control form-control-sm"></div>'
        . $extra
        . '<div class="col-12 col-md-auto d-flex gap-2 ms-md-auto pt-2 pt-md-0">'
        . '  <button class="btn btn-sm btn-primary px-3 shadow-2xs"><i class="bi bi-funnel-fill me-1"></i> Filtrar</button>'
        . '  <a class="btn btn-sm btn-light border px-3" href="?">Limpiar</a>'
        . '</div>'
        . '</form></div>';
}
