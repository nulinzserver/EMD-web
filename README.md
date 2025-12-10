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

### 4. Setup Laravel
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 5. Set permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

##  Access

- **URL**: https://nulinz.co.in/beta
- **Database Port**: 3307 (host) → 3306 (container)
- **PHP-FPM Port**: 9001 (host) → 9000 (container)

##  Docker Commands
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f app
docker-compose logs -f mysql

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

### Database
```bash
# Access MySQL
docker-compose exec mysql mysql -u beta_user -p beta_db

# Backup database
docker-compose exec mysql mysqldump -u beta_user -p beta_db > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u beta_user -p beta_db < backup.sql
```

##  Deployment Workflow
```bash
# Pull latest changes
git pull origin main

# Rebuild containers (if Dockerfile changed)
docker-compose up -d --build

# Install/update dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear and cache configs
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

##  Troubleshooting

### Container won't start
```bash
docker-compose down -v
docker-compose up -d --build
```

### Database connection refused
- Check if MySQL container is running: `docker-compose ps`
- Verify `.env` has `DB_HOST=beta_mysql` (not 127.0.0.1)
- Wait 10 seconds for MySQL to fully start

### Permission errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Clear all caches
```bash
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

##  License

This project is proprietary software.

##  Contact

For issues or questions, contact the development team.