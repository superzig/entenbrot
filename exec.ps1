# Docker installieren
Invoke-WebRequest -Uri https://desktop.docker.com/win/stable/Docker%20Desktop%20Installer.exe -OutFile DockerDesktopInstaller.exe
Start-Process -Wait -FilePath .\DockerDesktopInstaller.exe
Remove-Item .\DockerDesktopInstaller.exe

# Git-Repository klonen
git clone https://github.com/superzig/fps.git mylaravelapp
cd .\mylaravelapp

# Docker-Container erstellen und starten
docker build -t mylaravelapp .
docker run -p 8000:8000 -d mylaravelapp

# Warten, bis der Docker-Container gestartet ist
Start-Sleep -Seconds 10

# Composer installieren
docker exec -it (docker ps -q) composer install

# Laravel-App initialisieren
docker exec -it (docker ps -q) php artisan key:generate
docker exec -it (docker ps -q) php artisan migrate

# Node.js und npm installieren
docker exec -it (docker ps -q) apt-get install -y nodejs npm

# Laravel-Mix global installieren
docker exec -it (docker ps -q) npm install -g laravel-mix

# npm start ausführen
docker exec -it (docker ps -q) npm start

