-- Migración para Sistema de Chat en Vivo y Asistente Virtual IA con Handoff Humano

CREATE TABLE IF NOT EXISTS `chat_sesiones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `session_uuid` VARCHAR(64) NOT NULL UNIQUE,
  `nombre_visitante` VARCHAR(150) NOT NULL DEFAULT 'Visitante Comunal',
  `telefono` VARCHAR(50) DEFAULT NULL,
  `estado` ENUM('ia', 'esperando_humano', 'humano_activo', 'finalizado') NOT NULL DEFAULT 'ia',
  `atendido_por` INT UNSIGNED DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `ultimo_mensaje` TEXT DEFAULT NULL,
  `no_leidos_admin` INT UNSIGNED NOT NULL DEFAULT 0,
  `no_leidos_visitante` INT UNSIGNED NOT NULL DEFAULT 0,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_estado` (`estado`),
  INDEX `idx_actualizado` (`actualizado_en`),
  FOREIGN KEY (`atendido_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_mensajes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `sesion_id` INT UNSIGNED NOT NULL,
  `remitente` ENUM('visitante', 'ia', 'admin', 'sistema') NOT NULL,
  `autor_nombre` VARCHAR(100) DEFAULT NULL,
  `mensaje` TEXT NOT NULL,
  `leido` TINYINT(1) NOT NULL DEFAULT 0,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_sesion_creado` (`sesion_id`, `creado_en`),
  FOREIGN KEY (`sesion_id`) REFERENCES `chat_sesiones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
