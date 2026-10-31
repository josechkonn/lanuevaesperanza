# Sistema Operativo y Sistema de Gestión • EPSDC La Nueva Esperanza

Plataforma integral de soberanía agroalimentaria, caracterización territorial andina, distribución social directa y gestión comunal (Literal G del Objeto Social) para la **Empresa de Propiedad Social Directa Comunal (EPSDC) La Nueva Esperanza**, ubicada en la Ciudad de La Grita, Municipio Jáuregui (Aldea Quebrada de San José), Estado Táchira, Venezuela.

---

## 🌟 Características Principales

1. **Portal Web Institucional y Campesino**:
   - Diseño editorial andino de alto nivel (`Playfair Display`, `Plus Jakarta Sans`, `Caveat`).
   - Curvas topográficas andinas que celebran las altas cumbres de 2.000 a 3.100 msnm.
   - Cinta kinética continua de los 39 rubros cosechados y rubros emblema (*Truchas Arcoíris de Las Porqueras, Café Especial de Pregonero, Panela Artesanal de Humuquena*).
   - Catálogo interactivo de 39 Rubros con filtros agronómicos, tiempos de maduración y fuentes de riego de deshielo por gravedad.
   - Georreferenciación y mapa satelital de los 12 municipios articulados, cuencas hídricas y salida multimodal hacia Puerto La Ceiba y frontera colombo-venezolana.
   - Marco Comunal y fondos estatutarios (Fondo 70% Mantenimiento Productivo, 20% Atención a Productores, 10% Reinversión Comunitaria).

2. **Canal de Orientación Comunal Directa**:
   - Sistema de orientación con conexión en vivo a la base de datos MySQL (*grounding* estricto anti-alucinaciones).
   - Capacidad de transferir la conversación en vivo a un **Coordinador Comunal** de guardia mediante modales centralizados (`ComunalModal`).
   - Panel de administración tipo WhatsApp Web para la atención y seguimiento de conversaciones en tiempo real.

3. **Módulo de Productores y UPF (Literal G)**:
   - Formulario telemático para ofertas de cosecha disponible, requerimientos técnicos o sugerencias con soporte para adjuntar fotografías y acuse de recibo.
   - Módulo administrativo exclusivo para clasificar, revisar y atender solicitudes campesinas.

4. **Panel de Control Administrativo Seguro**:
   - Autenticación con limitador de intentos por IP/usuario y protección contra fuerza bruta.
   - Gestión de rubros, bolsas y combos agrícolas, precios históricos multimoneda (USD / Bs / COP).
   - Cronograma semanal de rutas de entrega y acopio.
   - Auditoría completa de acciones de usuarios con bitácora inmutable.

---

## 🚀 Requisitos del Entorno

- **PHP 8.1+** con extensiones `pdo_mysql`, `curl`, `mbstring`, `fileinfo`.
- **Servidor Web**: Apache con `mod_rewrite` habilitado (compatible con XAMPP / LAMP / Docker).
- **Base de Datos**: MySQL 5.7+ o MariaDB 10.3+.

---

## 📦 Instalación Rápida

1. Clonar el repositorio en el directorio raíz web (ej. `htdocs` en XAMPP):
   ```bash
   git clone git@github.com:josechkonn/lanuevaesperanza.git
   ```

2. Crear la base de datos MySQL:
   ```sql
   CREATE DATABASE lanuevaesperanza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Importar los esquemas iniciales:
   ```bash
   mysql -u root lanuevaesperanza < database/schema.sql
   mysql -u root lanuevaesperanza < database/migration_chat.sql
   ```

4. Poblar datos iniciales y usuario administrador:
   ```bash
   php database/seed_demo.php
   php database/crear_admin.php
   ```

5. Configurar el archivo `config/config.php` según las credenciales de tu servidor.

---

## 🛡️ Licencia y Propiedad Social

Desarrollado para la **Empresa de Propiedad Social Directa Comunal (EPSDC) La Nueva Esperanza**, RIF J-41298453-0. La Grita, Municipio Jáuregui, Estado Táchira, Venezuela.
