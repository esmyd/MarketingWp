# Configuración de Email para Monitoreo

## Problema
El sistema está intentando conectarse a un servidor de correo llamado "mailpit" que no está disponible.

## Soluciones

### Opción 1: Usar Log (Recomendado para Desarrollo)
Para desarrollo local, puedes configurar Laravel para que solo registre los emails en los logs en lugar de intentar enviarlos realmente.

Agrega estas líneas a tu archivo `.env`:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Tu Nombre"
```

Los emails se guardarán en `storage/logs/laravel.log` en lugar de enviarse.

### Opción 2: Configurar SMTP Real (Para Producción)

#### Gmail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="Tu Nombre"
```

**Nota:** Para Gmail necesitas usar una "Contraseña de aplicación" en lugar de tu contraseña normal. Ve a: Configuración de Google > Seguridad > Verificación en 2 pasos > Contraseñas de aplicaciones

#### Outlook/Hotmail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@outlook.com
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@outlook.com
MAIL_FROM_NAME="Tu Nombre"
```

#### Otro Servidor SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=tu-servidor-smtp.com
MAIL_PORT=587
MAIL_USERNAME=tu-usuario
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Tu Nombre"
```

### Opción 3: Servicios de Email Profesionales

#### Mailgun
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=tu-dominio.mailgun.org
MAILGUN_SECRET=tu-api-key
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Tu Nombre"
```

#### SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu-api-key-de-sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Tu Nombre"
```

## Después de Configurar

1. Guarda el archivo `.env`
2. Limpia la caché de configuración:
   ```bash
   php artisan config:clear
   ```
3. Prueba el envío de email nuevamente

## Nota Importante

El sistema continuará funcionando normalmente aunque falle el envío de email. Los errores se registrarán en los logs pero no afectarán el funcionamiento del bot de WhatsApp ni el envío de mensajes de monitoreo por WhatsApp.


