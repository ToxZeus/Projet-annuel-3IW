# Guide de déploiement

## Déploiement local avec Docker Compose

### Étape 1 : Prérequis

- Docker Desktop (ou Docker + Docker Compose)
- Git

### Étape 2 : Cloner le dépôt

```bash
git clone https://github.com/ToxZeus/Projet-annuel-3IW.git
cd Projet-annuel-3IW
```

### Étape 3 : Lancer l'application

```bash
docker-compose up -d
```

### Étape 4 : Accéder à l'application

L'application est disponible sur : **http://localhost:8080**

### Connexion

- Email de démo : `demo@budgie.local`
- Mot de passe : `BudgieDemo2026!`

Ou créer un nouveau compte en cliquant sur "Créer un compte".

### Logs

Voir les logs en direct :

```bash
docker-compose logs -f web
```

### Arrêter l'application

```bash
docker-compose down
```

### Persistance des données

La base de données SQLite est stockée dans le répertoire `./data/budgie.db`. Ce fichier persiste entre les redémarrages des conteneurs.

## Architecture Docker

### Dockerfile

- Image de base : `php:8.4-apache`
- Extensions PHP : `pdo`, `pdo_sqlite`
- Apache modules : `mod_rewrite`
- Document root : `/var/www/html/public`

### docker-compose.yml

Un seul service :
- **web** : Apache + PHP 8.4
  - Port : `8080:80`
  - Volumes :
    - Code application : `.:/var/www/html`
    - Répertoire données : `./data:/var/www/html/data`

## Déploiement en production

Le déploiement production utilise :

- l'application PHP/Apache construite par le `Dockerfile`
- Caddy comme reverse proxy public
- HTTPS automatique avec Let's Encrypt
- la base SQLite persistante dans `./data/budgie.db`

### Prérequis serveur

- Un serveur Linux avec Docker et Docker Compose
- Les ports `80` et `443` ouverts
- Un nom de domaine qui pointe vers l'IP publique du serveur

### Configurer le nom de domaine

Chez votre fournisseur DNS, créez un enregistrement `A` :

```text
budgie.example.com -> IP_PUBLIQUE_DU_SERVEUR
```

Remplacez `budgie.example.com` par votre vrai domaine ou sous-domaine.

### Préparer les variables d'environnement

Sur le serveur :

```bash
cp .env.production.example .env.production
```

Puis modifiez au minimum :

```env
DOMAIN_NAME=budgie.example.com
APP_URL=https://budgie.example.com
SMTP_FROM_EMAIL=noreply@budgie.example.com
```

Pour activer le paiement premium Stripe, renseignez aussi :

```env
STRIPE_SECRET_KEY=sk_live_xxx
STRIPE_PREMIUM_PRICE_ID=price_xxx
```

### Lancer en production

```bash
docker compose -f docker-compose.production.yml --env-file .env.production up -d --build
```

L'application sera disponible sur :

```text
https://budgie.example.com
```

Caddy demande et renouvelle automatiquement le certificat HTTPS tant que le domaine pointe bien vers le serveur.

### Mettre à jour l'application sur le serveur

```bash
git pull origin main
docker compose -f docker-compose.production.yml --env-file .env.production up -d --build
```

### Voir les logs production

```bash
docker compose -f docker-compose.production.yml logs -f
```

### Arrêter la production

```bash
docker compose -f docker-compose.production.yml down
```

Les volumes Caddy conservent les certificats HTTPS. Le dossier `./data` conserve la base SQLite.

### Points à renforcer ensuite

1. **Backups** : automatiser les sauvegardes SQLite
2. **Secrets** : gérer les secrets avec Docker Secrets ou un coffre externe
3. **Monitoring** : ajouter logs centralisés et alertes
4. **Database** : migrer vers PostgreSQL/MySQL si le trafic augmente

## Dépannage

### Port 8080 déjà utilisé

Modifier le port dans `docker-compose.yml` :

```yaml
ports:
  - "8090:80"  # Utiliser 8090 au lieu de 8080
```

### Base de données non accessible

Vérifier les permissions du répertoire `./data` :

```bash
chmod 755 ./data
```

### Rebuild suite à modifications

```bash
docker-compose down
docker-compose up -d --build
```

## Développement local sans Docker

Si vous préférez développer sans Docker :

```bash
php -S localhost:8000 -t public
```

Assurez-vous que PHP 8.4+ et SQLite3 sont installés.
