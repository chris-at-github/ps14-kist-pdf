# Ps14 Kist PDF

## Installation
1. Per ddev ssh auf Dateiebene wechseln (nur in ddev)
2. Ins Root-Verzeichnis der Extension wechseln
3. Package direkt installieren: composer install --working-dir=/var/www/html/public/typo3conf/ext/ps14_kist_pdf

## Todo`s
- [x] fehlerfreie Installation der Extension
- [x] Registrierung und Erstellung Middleware und Auslieferung eines Beispiel-Codes
- [x] Implementierung DemoProviderService 
- [x] Implementierung FileProviderService 
  - [x] Einlesen einer Beispiel PDF und Ausgabe dieser PDF
  - [x] Beispiel API-Request erzeugen -> PDF-Datei von ps14.de laden
  - [x] Code MD5 Hash aus dem erzeugten Source-Code erzeugen und Caching implmenentieren
- [ ] Implementierung des Cloudconvert-Services