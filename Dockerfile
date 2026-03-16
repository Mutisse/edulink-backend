# Dockerfile otimizado para Laravel no Render
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

# Configurar Apache para usar a porta do Render
RUN a2enmod rewrite
RUN echo "Listen \${PORT:-80}" > /etc/apache2/ports.conf

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

# Script de entrada para configurar a porta dinamicamente
RUN echo '#!/bin/bash\n\
    # Configurar Apache para usar a porta do Render\n\
    sed -i "s/80/${PORT:-80}/g" /etc/apache2/ports.conf\n\
    sed -i "s/:80/:${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf\n\
    \n\
    # Gerar key se necessário\n\
    php artisan key:generate --no-interaction --force\n\
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
