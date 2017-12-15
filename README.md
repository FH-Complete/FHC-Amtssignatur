# FHC-Amtssignatur
Erweitert den Signaturserver und stellt eine erweiterte Schnittstelle zur Signatur zur Verfügung.
* GUI zur Signatur von Dokumenten
* REST API zur Signatur von Dokumenten
* Archivierung der signierten Dokumenten
* Logging der Signaturvorgänge

# Installation

Zur Signatur der Dokumente muss die Signatursoftware PDF-AS installiert sein:
https://joinup.ec.europa.eu/solution/pdf

## Betriebssystem vorbereiten
```
apt-get install apache2
apt-get install php php-curl
apt-get install composer
```
## Apache konfigurieren
```
a2enmod ssl
a2enmod rewrite
a2enmod ldap
a2enmod authnz_ldap
```
Apache VHost Directory Configuration anpassen
```
AllowOverride AuthConfig Limit FileInfo Indexes
Require all granted
```

## Repository konfigurieren
```
git clone <url to this repo>
cp config-default-inc.php config.inc.php
composer install
```
```
mkdir /var/lib/signature
mkdir /var/lib/signature/log
mkdir /var/lib/signature/archive
```
```
chown www-data /var/lib/signature/log
chown www-data /var/lib/signature/archive
```
Die Datei /sign/.htaccess muss angepasst werden um nur jenen Usern Zugriff zu erlauben die Dokumente manuell signieren
dürfen.

Die Datei /api/.htaccess muss angepasst werden um nur jenen Usern Zugriff zu erlauben die Dokumente über die API
signieren dürfen.
