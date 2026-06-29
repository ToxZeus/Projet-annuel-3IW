# Budgie

**Ton partenaire financier personnel.**

Budgie est une application web de suivi budgétaire permettant de gérer comptes, dépenses, revenus et prévisions financières, sans connexion bancaire directe.

Application déployée : **https://budgie.software**

## Stack technique

- **Backend :** PHP 8.3 natif (sans framework)
- **Base de données :** SQLite
- **Frontend :** HTML + CSS + PHP templates
- **Architecture :** MVC avec couche services
- **Déploiement :** Docker (Nginx + PHP-FPM), serveur Hetzner, SSL Let's Encrypt

## Fonctionnalités

- Authentification (inscription, connexion, activation email, réinitialisation mot de passe)
- Gestion des comptes avec taux de rémunération et taux d'imposition
- Suivi des dépenses et revenus (ponctuel, mensuel, tous les N mois)
- Prévisions mensuelles avec calcul d'intérêts nets et graphiques
- Exceptions sur dépenses/revenus (modification temporaire du montant)
- Abonnements Stripe (gratuit / premium)
- Administration

## Lancer en local

### Avec Docker (recommandé)

```bash
docker compose up -d
```

Application disponible sur **http://localhost:8080**

Mails interceptés par MailHog sur **http://localhost:8025**

### Sans Docker

```bash
php -S localhost:8000 -t public
```

Nécessite PHP 8.3+ avec l'extension `pdo_sqlite`.

## Structure du projet

```
.
├── public/
│   ├── index.php           # Front controller
│   └── assets/css/app.css  # Styles globaux
├── src/
│   ├── App.php             # Routeur + handlers HTTP
│   ├── Database.php        # Wrapper PDO/SQLite
│   ├── UserService.php
│   ├── AccountService.php
│   ├── ExpenseService.php
│   ├── IncomeService.php
│   ├── ExceptionService.php
│   ├── ValidationHelper.php
│   └── Helpers/EmailHelper.php
├── templates/
│   ├── layout.php
│   └── pages/
│       ├── accounts/
│       ├── expenses/
│       ├── incomes/
│       ├── exceptions/
│       └── previsions.php
├── data/                   # Base SQLite (générée, ignorée par git)
├── deploy/
│   ├── nginx.conf          # Config Nginx production
│   └── nginx.dev.conf      # Config Nginx développement
├── Dockerfile              # PHP 8.3-FPM
├── docker-compose.yml      # Dev (Nginx + PHP-FPM + MailHog)
└── docker-compose.production.yml
```

## Variables d'environnement

Copier `.env.example` en `.env` et renseigner :

```env
APP_URL=http://localhost:8000
APP_ENV=development
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASSWORD=
SMTP_FROM_EMAIL=noreply@budgie.local
SMTP_FROM_NAME=Budgie
STRIPE_SECRET_KEY=
STRIPE_PREMIUM_PRICE_ID=
```

## Plans d'abonnement

| Type | Comptes | Dépenses/compte | Revenus/compte |
|---|---|---|---|
| Gratuit | 2 | 7 | 2 |
| Premium | Illimité | Illimité | Illimité |

## Git Flow

- `main` — version stable (déployée en production)
- `dev` — développement
- `feature/*` — nouvelles fonctionnalités
- `fix/*` — correctifs

## Déploiement

Voir [DEPLOYMENT.md](DEPLOYMENT.md).

## Sécurité

Voir [SECURITY.md](SECURITY.md).
