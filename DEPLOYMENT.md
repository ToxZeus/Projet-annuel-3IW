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

Pour la production, considérez :

1. **Image optimisée** : Réduire la taille de l'image
2. **Variables d'environnement** : Passer via `.env`
3. **HTTPS/TLS** : Ajouter un reverse proxy (Traefik, Nginx)
4. **Database** : Migrer vers PostgreSQL/MySQL si nécessaire
5. **Backups** : Automatiser les sauvegardes SQLite
6. **Monitoring** : Ajouter logging centralisé (ELK stack, etc.)
7. **Secrets** : Gérer les secrets avec Docker Secrets

### Exemple avec Nginx reverse proxy

Voir `docker-compose.production.yml` pour un exemple de configuration production.

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
