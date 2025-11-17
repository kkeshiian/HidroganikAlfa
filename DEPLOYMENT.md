# 🚀 Deployment Guide - Hidroganik Monitoring

Panduan lengkap deploy aplikasi Hidroganik ke production server.

---

## 📋 Prerequisites

### Yang Dibutuhkan:

- ✅ VPS/Cloud Server (Ubuntu 20.04/22.04 recommended)
- ✅ PHP 8.1+ dengan extensions: mysqli, mbstring, intl, json, xml
- ✅ MySQL 8.0+ atau MariaDB 10.5+
- ✅ Node.js 16+ dan npm
- ✅ Web server: Nginx atau Apache
- ✅ Domain name (optional, bisa pakai IP)
- ✅ SSL Certificate (optional, recommended)

---

## 🔧 Server Setup (Ubuntu)

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install PHP & Extensions

```bash
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring \
    php8.1-intl php8.1-xml php8.1-curl php8.1-zip php8.1-gd
```

### 3. Install MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 4. Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 5. Install Nginx

```bash
sudo apt install -y nginx
```

### 6. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 📦 Deploy Application

### 1. Clone/Upload Project

```bash
# Option A: Clone dari Git
cd /var/www
sudo git clone https://github.com/kkeshiian/HidroganikAlfa.git hidroganik
sudo chown -R www-data:www-data hidroganik

# Option B: Upload via FTP/SFTP
# Upload semua file ke /var/www/hidroganik
```

### 2. Install Dependencies

```bash
cd /var/www/hidroganik

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install MQTT Bridge dependencies
cd mqtt-bridge
npm install --production
cd ..
```

### 3. Setup Database

```bash
# Login MySQL
sudo mysql -u root -p

# Create database dan user
CREATE DATABASE hidroganik_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hidroganik'@'localhost' IDENTIFIED BY 'password_kuat_anda';
GRANT ALL PRIVILEGES ON hidroganik_db.* TO 'hidroganik'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Configure Environment

```bash
cd /var/www/hidroganik

# Copy dan edit .env file
cp env .env
nano .env
```

Edit `.env`:

```env
CI_ENVIRONMENT = production

database.default.hostname = localhost
database.default.database = hidroganik_db
database.default.username = hidroganik
database.default.password = password_kuat_anda
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

# MQTT Bridge Token
app.ingestToken = rahasia-hidroganik-2025-production
```

### 5. Run Migrations

```bash
php spark migrate
```

### 6. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/hidroganik
sudo chmod -R 755 /var/www/hidroganik
sudo chmod -R 777 /var/www/hidroganik/writable
```

---

## 🌐 Configure Web Server

### Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/hidroganik
```

```nginx
server {
    listen 80;
    server_name hidroganik.yourdomain.com;  # Ganti dengan domain Anda
    root /var/www/hidroganik/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Logs
    access_log /var/log/nginx/hidroganik-access.log;
    error_log /var/log/nginx/hidroganik-error.log;

    # Max upload size
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/hidroganik /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Apache Configuration (Alternative)

```bash
sudo nano /etc/apache2/sites-available/hidroganik.conf
```

```apache
<VirtualHost *:80>
    ServerName hidroganik.yourdomain.com
    DocumentRoot /var/www/hidroganik/public

    <Directory /var/www/hidroganik/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hidroganik-error.log
    CustomLog ${APACHE_LOG_DIR}/hidroganik-access.log combined
</VirtualHost>
```

Enable:

```bash
sudo a2ensite hidroganik
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 🤖 Setup MQTT Bridge Service (AUTO-START)

### Create Systemd Service

```bash
# Copy service file
sudo cp /var/www/hidroganik/mqtt-bridge/hidroganik-mqtt.service /etc/systemd/system/

# Edit if needed (adjust paths, user, environment)
sudo nano /etc/systemd/system/hidroganik-mqtt.service

# Reload systemd
sudo systemctl daemon-reload

# Enable auto-start on boot
sudo systemctl enable hidroganik-mqtt

# Start service
sudo systemctl start hidroganik-mqtt

# Check status
sudo systemctl status hidroganik-mqtt
```

### Service Commands

```bash
# Start service
sudo systemctl start hidroganik-mqtt

# Stop service
sudo systemctl stop hidroganik-mqtt

# Restart service
sudo systemctl restart hidroganik-mqtt

# View logs
sudo journalctl -u hidroganik-mqtt -f

# Enable auto-start
sudo systemctl enable hidroganik-mqtt

# Disable auto-start
sudo systemctl disable hidroganik-mqtt
```

---

## 🔐 SSL Certificate (HTTPS)

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get certificate (Nginx)
sudo certbot --nginx -d hidroganik.yourdomain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

Certificate auto-renews every 90 days.

---

## 🔒 Security Hardening

### 1. Firewall (UFW)

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. Disable Directory Listing

Already configured in web server config above.

### 3. Change Default Tokens

Edit `.env` and change:

- `app.ingestToken` - untuk MQTT security
- Database password

### 4. Secure MySQL

```bash
sudo mysql_secure_installation
```

- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes

### 5. Keep System Updated

```bash
# Auto-update security patches
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

---

## 📊 Monitoring & Maintenance

### Check Services Status

```bash
# Web server
sudo systemctl status nginx

# PHP-FPM
sudo systemctl status php8.1-fpm

# MySQL
sudo systemctl status mysql

# MQTT Bridge
sudo systemctl status hidroganik-mqtt
```

### View Logs

```bash
# Nginx access logs
sudo tail -f /var/log/nginx/hidroganik-access.log

# Nginx error logs
sudo tail -f /var/log/nginx/hidroganik-error.log

# MQTT Bridge logs
sudo journalctl -u hidroganik-mqtt -f

# Application logs (CodeIgniter)
sudo tail -f /var/www/hidroganik/writable/logs/log-*.php
```

### Database Backup

```bash
# Manual backup
mysqldump -u hidroganik -p hidroganik_db > backup_$(date +%Y%m%d).sql

# Automated daily backup (cron)
sudo crontab -e
```

Add line:

```cron
0 2 * * * /usr/bin/mysqldump -u hidroganik -pYOUR_PASSWORD hidroganik_db > /backup/hidroganik_$(date +\%Y\%m\%d).sql
```

---

## 🔄 Update Application

```bash
cd /var/www/hidroganik

# Backup database first!
mysqldump -u hidroganik -p hidroganik_db > backup_before_update.sql

# Pull latest code
sudo -u www-data git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
cd mqtt-bridge && npm install --production && cd ..

# Run new migrations
php spark migrate

# Restart MQTT Bridge
sudo systemctl restart hidroganik-mqtt

# Clear cache (if any)
php spark cache:clear
```

---

## ✅ Post-Deployment Checklist

- [ ] Database created and configured
- [ ] Migrations run successfully
- [ ] Web accessible via domain/IP
- [ ] MQTT Bridge service running and enabled
- [ ] SSL certificate installed (HTTPS)
- [ ] Firewall configured
- [ ] Default admin user created
- [ ] Test sensor data flow: ESP32 → MQTT → Database
- [ ] Check all pages loading correctly
- [ ] Export CSV working
- [ ] Auto-refresh working
- [ ] Calibration settings saving correctly
- [ ] Backup system configured

---

## 🐛 Troubleshooting

### Web shows 500 Error

```bash
# Check PHP errors
sudo tail -f /var/log/nginx/hidroganik-error.log

# Check permissions
sudo chown -R www-data:www-data /var/www/hidroganik/writable
```

### MQTT Bridge not saving data

```bash
# Check service status
sudo systemctl status hidroganik-mqtt

# View detailed logs
sudo journalctl -u hidroganik-mqtt -n 50

# Test API endpoint manually
curl -X POST http://localhost/api/telemetry/ingest \
  -H "Content-Type: application/json" \
  -H "X-INGEST-TOKEN: your-token" \
  -d '{"kebun":"kebun-a","ph":7.0,"tds":500,"suhu_air":25}'
```

### Database connection error

```bash
# Test MySQL connection
mysql -u hidroganik -p -e "SELECT 1"

# Check .env configuration
cat /var/www/hidroganik/.env | grep database
```

### Service crashes after server reboot

```bash
# Check if service is enabled
sudo systemctl is-enabled hidroganik-mqtt

# Re-enable if needed
sudo systemctl enable hidroganik-mqtt
sudo systemctl start hidroganik-mqtt
```

---

## 📱 Mobile Access

The web is responsive and works on mobile browsers. For native app:

- Android: Can use WebView wrapper
- iOS: Can use WKWebView wrapper
- Or access via mobile browser directly

---

## 🎯 Production URLs

After deployment:

- **Web Dashboard**: `https://hidroganik.yourdomain.com`
- **API Endpoint**: `https://hidroganik.yourdomain.com/api/telemetry`
- **Login**: `https://hidroganik.yourdomain.com/login`

---

## 💡 Additional Tips

### Performance Optimization

```bash
# Enable PHP OPcache
sudo nano /etc/php/8.1/fpm/php.ini
```

Set:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### Database Optimization

```sql
-- Add indexes for faster queries
ALTER TABLE telemetry_logs ADD INDEX idx_timestamp (timestamp_ms);
ALTER TABLE telemetry_logs ADD INDEX idx_device (device);
ALTER TABLE telemetry_logs ADD INDEX idx_date (date);
```

---

## 📞 Support

Jika ada masalah saat deployment, check:

1. Server logs (nginx/apache + php-fpm)
2. Application logs (writable/logs/)
3. MQTT Bridge logs (journalctl)
4. Database connection

---

**🎉 Deployment Complete!**

Service akan **otomatis running** setiap server restart.
Tidak perlu buka web untuk menyimpan data, semua otomatis via MQTT Bridge service.
