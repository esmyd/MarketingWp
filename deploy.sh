#!/bin/bash

# Script de Despliegue para MarketingWp
# Ejecutar en el servidor después de subir los archivos

echo "🚀 Iniciando despliegue de MarketingWp..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para mostrar errores
error() {
    echo -e "${RED}❌ Error: $1${NC}"
    exit 1
}

# Función para mostrar éxito
success() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Función para mostrar advertencia
warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    error "No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto."
fi

# Verificar que existe .env
if [ ! -f ".env" ]; then
    error "No se encontró el archivo .env. Por favor, créalo antes de continuar."
fi

# 1. Limpiar cachés
echo ""
echo "📦 Limpiando cachés..."
php artisan optimize:clear || warning "No se pudieron limpiar todas las cachés"
success "Cachés limpiadas"

# 2. Verificar APP_KEY
echo ""
echo "🔑 Verificando APP_KEY..."
APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" == "" ]; then
    warning "APP_KEY no está configurada. Generando nueva clave..."
    php artisan key:generate --force
    success "APP_KEY generada"
else
    success "APP_KEY ya está configurada"
fi

# 3. Instalar/Actualizar dependencias de Composer
echo ""
echo "📥 Instalando dependencias de Composer..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction || error "Error al instalar dependencias de Composer"
    success "Dependencias de Composer instaladas"
else
    warning "Composer no está instalado. Asegúrate de instalar las dependencias manualmente."
fi

# 4. Ejecutar migraciones
echo ""
echo "🗄️  Ejecutando migraciones de base de datos..."
read -p "¿Ejecutar migraciones? (s/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    php artisan migrate --force || warning "Error al ejecutar migraciones. Verifica la conexión a la base de datos."
    success "Migraciones ejecutadas"
else
    warning "Migraciones omitidas"
fi

# 5. Crear enlace simbólico de storage
echo ""
echo "🔗 Creando enlace simbólico de storage..."
php artisan storage:link || warning "El enlace simbólico ya existe o hubo un error"
success "Enlace simbólico creado"

# 6. Configurar permisos
echo ""
echo "🔐 Configurando permisos..."
if [ -w "storage" ] && [ -w "bootstrap/cache" ]; then
    chmod -R 775 storage bootstrap/cache 2>/dev/null || warning "No se pudieron cambiar permisos (puede requerir sudo)"
    success "Permisos configurados"
else
    warning "No se pudieron verificar permisos. Asegúrate de que storage/ y bootstrap/cache/ tienen permisos 775"
fi

# 7. Optimizar Laravel
echo ""
echo "⚡ Optimizando Laravel..."
php artisan config:cache || warning "Error al cachear configuración"
php artisan route:cache || warning "Error al cachear rutas"
php artisan view:cache || warning "Error al cachear vistas"
success "Laravel optimizado"

# 8. Verificar configuración
echo ""
echo "🔍 Verificando configuración..."
APP_ENV=$(grep "^APP_ENV=" .env | cut -d '=' -f2)
APP_DEBUG=$(grep "^APP_DEBUG=" .env | cut -d '=' -f2)

if [ "$APP_ENV" != "production" ]; then
    warning "APP_ENV está configurado como '$APP_ENV'. Para producción debería ser 'production'"
fi

if [ "$APP_DEBUG" == "true" ]; then
    warning "APP_DEBUG está en 'true'. Para producción debería ser 'false'"
fi

# 9. Verificar variables de WhatsApp
echo ""
echo "📱 Verificando configuración de WhatsApp..."
WHATSAPP_VARS=("WHATSAPP_TOKEN" "WHATSAPP_PHONE_NUMBER" "WHATSAPP_BUSINESS_ID" "WHATSAPP_PHONE_NUMBER_ID")
MISSING_VARS=()

for var in "${WHATSAPP_VARS[@]}"; do
    if ! grep -q "^${var}=" .env || grep "^${var}=" .env | grep -q "=$"; then
        MISSING_VARS+=("$var")
    fi
done

if [ ${#MISSING_VARS[@]} -gt 0 ]; then
    warning "Variables de WhatsApp faltantes o vacías: ${MISSING_VARS[*]}"
else
    success "Variables de WhatsApp configuradas"
fi

# Resumen final
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✨ Despliegue completado!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Verifica que tu aplicación funciona: visita tu dominio"
echo "   2. Revisa los logs si hay errores: storage/logs/laravel.log"
echo "   3. Configura el webhook de WhatsApp si aún no lo has hecho"
echo "   4. Verifica que APP_DEBUG=false y APP_ENV=production en .env"
echo ""
echo "🔗 Comandos útiles:"
echo "   - Ver rutas: php artisan route:list"
echo "   - Ver estado: php artisan about"
echo "   - Limpiar cachés: php artisan optimize:clear"
echo ""


