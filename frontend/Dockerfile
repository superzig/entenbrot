# Verwende ein Node.js-Image als Basis
FROM node:latest

# Setze das Arbeitsverzeichnis im Container
WORKDIR /app

# Kopiere die Package.json-Datei und den Package-lock.json falls vorhanden
COPY package*.json ./

# Installiere Abhängigkeiten
RUN npm install

# Kopiere den Rest des Codes
COPY . .

# Exponiere den Port, auf dem die Anwendung läuft
EXPOSE 3000

# Starte die Anwendung
CMD ["npm", "start"]
