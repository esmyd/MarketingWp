# Guía de Despliegue - MarketingWp

Esta guía te ayudará a preparar tu proyecto Laravel para desplegarlo en tu hosting.

## 📋 Checklist Pre-Despliegue

### 1. Requisitos del Servidor

Asegúrate de que tu hosting cumple con estos requisitos:
- **PHP**: 8.1 o superior
- **Extensiones PHP requeridas**:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  - GD o Imagick (para procesamiento de imágenes)
- **Composer**: Instalado en el servidor
- **Node.js y NPM**: Para compilar assets (o compilar localmente)
- **Base de datos**: MySQL/MariaDB o PostgreSQL

### 2. Preparación Local (Antes de Subir)

#### 2.1. Archivo de Entorno (.env)

Crea un archivo `.env` para producción con las siguientes variables:

```env
APP_NAME="MarketingWp"
APP_ENV=production
APP_KEY=base64:TU_CLAVE_AQUI
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_bd
DB_PASSWORD=contraseña_bd

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# WhatsApp Business API Configuration
WHATSAPP_API_VERSION=v22.0
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_VERIFY_TOKEN=tu_verify_token
WHATSAPP_TOKEN=tu_access_token
WHATSAPP_PHONE_NUMBER=tu_numero_telefono
WHATSAPP_PHONE_NUMBER_ID=tu_phone_number_id
WHATSAPP_BUSINESS_ID=tu_business_id
WHATSAPP_WEBHOOK_URL=https://tudominio.com/api/whatsapp/webhook

# Mail Configuration (si usas correo)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**⚠️ IMPORTANTE**: 
- Genera una nueva `APP_KEY` con: `php artisan key:generate`
- NO subas el archivo `.env` al servidor directamente, créalo en el servidor
- Cambia `APP_DEBUG=false` en producción
- Actualiza `APP_URL` con tu dominio real

#### 2.2. Compilar Assets

Ejecuta estos comandos localmente para compilar los assets:

```bash
# Instalar dependencias de Node.js
npm install

# Compilar assets para producción
npm run build
```

Esto generará los archivos en `public/build/` que SÍ debes subir al servidor.

#### 2.3. Instalar Dependencias de Composer (Producción)

```bash
# Instalar solo dependencias de producción (sin dev)
composer install --no-dev --optimize-autoloader
```

#### 2.4. Optimizar Laravel

```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache
```

**Nota**: Estos comandos también los puedes ejecutar en el servidor después del despliegue.

### 3. Archivos a Subir al Servidor

#### ✅ Subir estos archivos/carpetas:
- `/app`
- `/bootstrap`
- `/config`
- `/database` (solo migrations y seeders, NO archivos SQL)
- `/public` (incluyendo `/public/build` después de compilar)
- `/resources`
- `/routes`
- `/storage` (carpeta vacía, luego configurar permisos)
- `/vendor` (o instalar con composer en el servidor)
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `vite.config.js`
- `tailwind.config.js`
- `.htaccess` (si usas Apache)

#### ❌ NO subir estos archivos:
- `/.env` (crear nuevo en el servidor)
- `/node_modules`
- `/.git`
- `/storage/logs/*.log`
- `/.phpunit.cache`
- `/tests` (opcional, no necesario en producción)
- Archivos `.zip` del proyecto

### 4. Configuración en el Servidor

#### 4.1. Estructura de Directorios

Asegúrate de que la estructura en el servidor sea:

```
public_html/ (o htdocs/)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← Este debe ser el DocumentRoot
│   ├── index.php
│   ├── build/       ← Assets compilados
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── artisan
├── composer.json
└── .env            ← Crear en el servidor
```

**⚠️ IMPORTANTE**: El `DocumentRoot` de tu servidor web debe apuntar a la carpeta `/public`, NO a la raíz del proyecto.

#### 4.2. Permisos de Carpetas

Ejecuta estos comandos en el servidor (vía SSH):

```bash
# Dar permisos de escritura a storage y cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Si no tienes acceso root, usa:
chmod -R 775 storage bootstrap/cache
```

#### 4.3. Crear Enlaces Simbólicos

```bash
# Crear enlace simbólico para storage (si no existe)
php artisan storage:link
```

#### 4.4. Instalar Dependencias en el Servidor

```bash
# Si no subiste vendor, instalar dependencias
composer install --no-dev --optimize-autoloader

# Si necesitas compilar assets en el servidor (no recomendado)
npm install
npm run build
```

#### 4.5. Configurar Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate --force

# (Opcional) Ejecutar seeders si es necesario
php artisan db:seed --force
```

#### 4.6. Optimizar Laravel en Producción

```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize
```

### 5. Configuración del Servidor Web

#### Apache (.htaccess)

Asegúrate de que existe un archivo `.htaccess` en `/public`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx

Si usas Nginx, configura así:

```nginx
server {
    listen 80;
    server_name tudominio.com;
    root /ruta/a/tu/proyecto/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6. Verificaciones Post-Despliegue

#### 6.1. Verificar que Funciona

1. Accede a tu dominio: `https://tudominio.com`
2. Verifica que no hay errores 500
3. Revisa los logs: `storage/logs/laravel.log`

#### 6.2. Verificar Variables de Entorno

```bash
# Verificar que APP_KEY está configurada
php artisan tinker
>>> config('app.key')
```

#### 6.3. Verificar Permisos

```bash
# Verificar permisos de storage
ls -la storage/
ls -la bootstrap/cache/
```

#### 6.4. Verificar WhatsApp Webhook

1. Accede a: `https://tudominio.com/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=tu_verify_token&hub.challenge=test`
2. Debe responder con "test"

### 7. Comandos Útiles para Producción

```bash
# Limpiar todas las cachés
php artisan optimize:clear

# Re-optimizar después de cambios
php artisan optimize

# Ver rutas disponibles
php artisan route:list

# Ver configuración actual
php artisan config:show

# Verificar el estado de la aplicación
php artisan about
```

### 8. Seguridad Adicional

1. **Ocultar información de Laravel**:
   - Asegúrate de que `APP_DEBUG=false`
   - No expongas archivos `.env`

2. **HTTPS**: Configura SSL/HTTPS en tu hosting

3. **Firewall**: Configura reglas de firewall si es necesario

4. **Backups**: Configura backups regulares de:
   - Base de datos
   - Carpeta `/storage`
   - Archivo `.env`

### 9. Troubleshooting Común

#### Error 500
- Verifica permisos de `storage/` y `bootstrap/cache/`
- Revisa `storage/logs/laravel.log`
- Verifica que `APP_KEY` está configurada

#### Assets no cargan
- Verifica que `public/build/` existe y tiene archivos
- Ejecuta `npm run build` nuevamente
- Verifica permisos de `public/build/`

#### Error de base de datos
- Verifica credenciales en `.env`
- Verifica que la base de datos existe
- Ejecuta `php artisan migrate`

#### WhatsApp no funciona
- Verifica todas las variables `WHATSAPP_*` en `.env`
- Verifica que el webhook está configurado en Facebook
- Revisa los logs: `storage/logs/laravel.log`

### 10. Script de Despliegue Rápido

Puedes crear un script `deploy.sh` para automatizar:

```bash
#!/bin/bash

# Limpiar cachés
php artisan optimize:clear

# Actualizar dependencias
composer install --no-dev --optimize-autoloader

# Ejecutar migraciones
php artisan migrate --force

# Compilar assets (si es necesario)
npm run build

# Optimizar
php artisan optimize

echo "Despliegue completado!"
```

---

## 📝 Notas Finales

- **Nunca** subas el archivo `.env` local al servidor
- Siempre usa `APP_DEBUG=false` en producción
- Mantén `composer.lock` y `package-lock.json` actualizados
- Haz backups antes de cada despliegue
- Prueba en un entorno de staging antes de producción

¡Buena suerte con tu despliegue! 🚀


