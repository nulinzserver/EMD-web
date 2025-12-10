# EMD Application

Laravel application running in Docker containers for testing and development.

##  Tech Stack

- **Framework**: Laravel 11.x
- **PHP**: 8.2-FPM (Docker)
- **Database**: MySQL 8.0 (Docker)
- **Web Server**: Nginx (Host)
- **Containerization**: Docker & Docker Compose

##  Prerequisites

- Docker & Docker Compose installed
- Nginx configured on host machine
- Git

##  Installation

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/beta-app.git /var/www/html/beta
cd /var/www/html/beta
```



### 2. Start Docker containers
```bash
docker-compose up -d --build
```

### 3. Install dependencies
```bash
docker-compose exec app composer install --no-dev --optimize-autoloader
```




##  Access

- **URL**: https://nulinz.co.in/beta


##  Docker Commands
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart



# Access container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

##  Project Structure
```
/var/www/html/beta/
├── app/                    # Application code
├── config/                 # Configuration files
├── database/               # Migrations & seeders
├── docker-compose.yml      # Docker services
├── Dockerfile             # PHP-FPM container
├── public/                # Public assets
├── resources/             # Views & assets
├── routes/                # Application routes
└── storage/               # Logs & cache
```

##  Useful Commands

### Laravel
```bash
# Clear all caches
docker-compose exec app php artisan optimize:clear

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Seed database
docker-compose exec app php artisan db:seed
```

### Composer
```bash
# Install packages
docker-compose exec app composer install

# Update packages
docker-compose exec app composer update

# Require new package
docker-compose exec app composer require vendor/package
```



##  Deployment Workflow
```bash
# Pull latest changes
git pull origin main

# Rebuild containers (if Dockerfile changed)
docker-compose up -d --build

# Install/update dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader


# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```




### Permission errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Clear all caches
```bash
docker-compose exec app php artisan optimize:clear

```

##  License

This project is proprietary software.

##  Contact

For issues or questions, contact the development team.