# Usar una imagen oficial de PHP con Apache
FROM php:8.0-apache

# Establecer etiquetas de metadatos
LABEL maintainer="DevOps Team"
LABEL description="Aplicación de Consulta Médica - PHP + Apache"
LABEL version="1.0.0"

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mbstring

# Instalar utilidades adicionales
RUN apt-get update && apt-get install -y \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Copiar el código de la aplicación al directorio de trabajo de Apache
COPY ./app/consulta_medica /var/www/html/

# Configurar Apache para servir el directorio public como DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar mod_rewrite de Apache para URLs amigables
RUN a2enmod rewrite

# Crear directorio de logs
RUN mkdir -p /var/www/html/logs

# Establecer permisos adecuados para el directorio de la aplicación
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html/

# Crear endpoint de health check
RUN echo '<?php echo json_encode(["status" => "UP", "timestamp" => date("Y-m-d H:i:s")]); ?>' > /var/www/html/public/health.php

# Exponer el puerto 80 para el servidor web
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

# Comando por defecto
CMD ["apache2-foreground"]
