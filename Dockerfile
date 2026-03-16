# Dockerfile corrigido para Laravel no Render
FROM php:8.2-apache

# Instalar extensões e dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache para usar a porta do Render (CORRIGIDO)
RUN a2enmod rewrite
RUN echo "# Porta dinâmica do Render\nListen \${PORT}" > /etc/apache2/ports.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . .

# Instalar dependências do Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar DocumentRoot para a pasta public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Criar .env a partir do .env.example se não existir
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Script de entrada corrigido
RUN echo '#!/bin/bash\n\
# Configurar Apache para usar a porta do Render\n\
echo "Listen $PORT" > /etc/apache2/ports.conf\n\
echo "<VirtualHost *:$PORT>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf\n\
\n\
# Verificar se APP_KEY existe\n\
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY .env | cut -d "=" -f2)" ]; then\n\
    echo "Gerando APP_KEY..."\n\
    php artisan key:generate --no-interaction\n\
else\n\
    echo "APP_KEY já existe"\n\
fi\n\
\n\
# Rodar migrations\n\
php artisan migrate --force\n\
\n\
# Iniciar Apache\n\
apache2-foreground' > /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Expor porta (será substituída pelo entrypoint)
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
