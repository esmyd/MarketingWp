# ✅ Checklist Rápida de Despliegue

## Antes de Subir al Servidor

- [ ] **Compilar assets**: `npm run build`
- [ ] **Instalar dependencias de producción**: `composer install --no-dev --optimize-autoloader`
- [ ] **Optimizar Laravel**: `php artisan optimize`
- [ ] **Verificar que `.env` NO se sube** (está en .gitignore)
- [ ] **Hacer backup** de la base de datos actual (si existe)

## Archivos a Subir

- [ ] Carpeta `/app`
- [ ] Carpeta `/bootstrap`
- [ ] Carpeta `/config`
- [ ] Carpeta `/database` (solo migrations y seeders)
- [ ] Carpeta `/public` (incluyendo `/public/build`)
- [ ] Carpeta `/resources`
- [ ] Carpeta `/routes`
- [ ] Carpeta `/storage` (estructura vacía)
- [ ] Carpeta `/vendor` (o instalar en servidor)
- [ ] Archivo `artisan`
- [ ] Archivo `composer.json`
- [ ] Archivo `composer.lock`
- [ ] Archivo `package.json`
- [ ] Archivo `package-lock.json`
- [ ] Archivo `vite.config.js`
- [ ] Archivo `tailwind.config.js`

## En el Servidor

### Configuración Inicial

- [ ] **Crear archivo `.env`** con las variables de producción
- [ ] **Generar APP_KEY**: `php artisan key:generate`
- [ ] **Configurar base de datos** en `.env`
- [ ] **Configurar variables de WhatsApp** en `.env`:
  - [ ] `WHATSAPP_TOKEN`
  - [ ] `WHATSAPP_PHONE_NUMBER`
  - [ ] `WHATSAPP_PHONE_NUMBER_ID`
  - [ ] `WHATSAPP_BUSINESS_ID`
  - [ ] `WHATSAPP_VERIFY_TOKEN`
  - [ ] `WHATSAPP_WEBHOOK_URL`
- [ ] **Configurar APP_URL** con tu dominio real
- [ ] **Configurar APP_ENV=production**
- [ ] **Configurar APP_DEBUG=false**

### Instalación

- [ ] **Instalar dependencias**: `composer install --no-dev --optimize-autoloader`
- [ ] **Ejecutar migraciones**: `php artisan migrate --force`
- [ ] **Crear enlace storage**: `php artisan storage:link`
- [ ] **Configurar permisos**: `chmod -R 775 storage bootstrap/cache`

### Optimización

- [ ] **Cachear configuración**: `php artisan config:cache`
- [ ] **Cachear rutas**: `php artisan route:cache`
- [ ] **Cachear vistas**: `php artisan view:cache`

### Configuración del Servidor Web

- [ ] **DocumentRoot apunta a `/public`** (NO a la raíz)
- [ ] **Configurar `.htaccess`** (Apache) o configuración Nginx
- [ ] **Configurar SSL/HTTPS** (recomendado)

## Verificaciones Post-Despliegue

- [ ] **Acceder al dominio**: La aplicación carga correctamente
- [ ] **Verificar logs**: `storage/logs/laravel.log` no tiene errores críticos
- [ ] **Verificar assets**: CSS y JS cargan correctamente
- [ ] **Verificar base de datos**: Las migraciones se ejecutaron correctamente
- [ ] **Verificar WhatsApp webhook**: 
  - URL: `https://tudominio.com/api/whatsapp/webhook`
  - Verificar en Facebook Developer Console

## Seguridad

- [ ] **APP_DEBUG=false** en producción
- [ ] **APP_ENV=production** configurado
- [ ] **Archivo `.env` no es accesible** públicamente
- [ ] **HTTPS configurado** y funcionando
- [ ] **Permisos de archivos** correctos (775 para storage, 644 para archivos)

## Backup

- [ ] **Backup de base de datos** configurado
- [ ] **Backup de carpeta `/storage`** configurado
- [ ] **Backup de archivo `.env`** guardado de forma segura

---

## 🚀 Comando Rápido

Si tienes acceso SSH, puedes usar el script de despliegue:

```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 📞 Si Algo Sale Mal

1. **Revisa los logs**: `storage/logs/laravel.log`
2. **Limpia cachés**: `php artisan optimize:clear`
3. **Verifica permisos**: `ls -la storage/ bootstrap/cache/`
4. **Verifica .env**: Todas las variables están configuradas
5. **Verifica base de datos**: Conexión y credenciales correctas


