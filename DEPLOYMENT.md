# Déploiement

## Architecture de production

Deux conteneurs Docker communiquent via un réseau interne :

- **nginx** (`nginx:alpine`) — sert les fichiers statiques, proxifie les requêtes PHP vers php-fpm, gère le SSL
- **php** (`php:8.3-fpm`) — exécute le code PHP

```
Internet → Nginx (443/80) → PHP-FPM (9000) → SQLite
```

## Lancer en production

### Prérequis serveur

- Docker installé (`curl -fsSL https://get.docker.com | sh`)
- Certificats Let's Encrypt présents dans `/etc/letsencrypt/`
- Fichier `.env` présent à la racine du projet

### Démarrer

```bash
docker compose -f docker-compose.production.yml up -d --build
```

### Mettre à jour (via script deploy)

```bash
deploy
```

Le script `/usr/local/bin/deploy` effectue :
1. `git pull` depuis `main`
2. Correction des permissions sur `./data`
3. `docker compose -f docker-compose.production.yml up -d --build`

## Développement local

```bash
docker compose up -d
```

- Application : http://localhost:8080
- MailHog (mails) : http://localhost:8025

## Variables d'environnement

Le fichier `.env` doit être présent à la racine (non commité) :

```env
APP_URL=https://budgie.software
APP_ENV=production
SMTP_HOST=smtp-relay.brevo.com
SMTP_PORT=587
SMTP_USER=xxx@smtp-brevo.com
SMTP_PASSWORD=xxx
SMTP_FROM_EMAIL=noreply@budgie.software
SMTP_FROM_NAME=Budgie
STRIPE_SECRET_KEY=sk_live_xxx
STRIPE_PREMIUM_PRICE_ID=price_xxx
```

## SSL

Les certificats sont gérés par Certbot sur l'hôte et montés en lecture seule dans le conteneur Nginx :

```yaml
- /etc/letsencrypt:/etc/letsencrypt:ro
```

Renouvellement automatique via le timer systemd Certbot. Après renouvellement, redémarrer Nginx :

```bash
docker compose -f docker-compose.production.yml restart nginx
```

## Dépannage

### Voir les logs

```bash
docker compose -f docker-compose.production.yml logs -f
```

### Permissions base de données

```bash
chown -R 33:33 /var/www/budgie/data
chmod -R 775 /var/www/budgie/data
```

### Rebuild complet

```bash
docker compose -f docker-compose.production.yml down
docker compose -f docker-compose.production.yml up -d --build
```
