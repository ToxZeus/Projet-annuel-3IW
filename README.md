# Budgie

**Ton partenaire financier personnel.**

Budgie est une application web de suivi budgétaire centrée sur les comptes, dépenses, revenus, prévisions et partages de visibilité, sans connexion bancaire directe.

## Technologie

- **Backend:** PHP natif (pas de framework)
- **Database:** SQLite (persistence locale)
- **Frontend:** HTML + CSS + PHP templates
- **Architecture:** MVC (Model-View-Controller) avec services layer

## Fonctionnalités

- ✅ Authentification et gestion des utilisateurs
- ✅ Gestion des comptes avec intérêts et taxes
- ✅ Suivi des dépenses et revenus (ponctuels ou périodiques)
- ✅ Prévisions mensuelles avec calcul d'intérêts nets
- ✅ Interface responsive
- 🚀 Déploiement Docker

## Installation locale

### Prérequis

- PHP 8.4+ avec `pdo_sqlite`
- Composer (optional)
- SQLite3

### Lancer le serveur PHP

```bash
php -S localhost:8000 -t public
```

Accédez à l'application sur `http://localhost:8000`

### Compte de démo

- Email: `demo@budgie.local`
- Mot de passe: `BudgieDemo2026!`

## Déploiement Docker

### Avec Docker Compose (recommandé)

```bash
docker-compose up -d
```

L'application sera disponible sur `http://localhost:8080`

La base de données SQLite est stockée dans `./data/budgie.db` et persistera entre les redémarrages.

### Arrêter les conteneurs

```bash
docker-compose down
```

### Rebuild après modifications

```bash
docker-compose up -d --build
```

## Structure du projet

```
.
├── public/
│   ├── index.php          # Front controller
│   └── assets/
│       └── css/
│           └── app.css    # Styling global
├── src/
│   ├── App.php            # Router + HTTP handlers
│   ├── Database.php       # SQLite wrapper
│   ├── UserService.php    # User management
│   ├── AccountService.php # Account CRUD
│   ├── ExpenseService.php # Expense CRUD
│   └── IncomeService.php  # Income CRUD
├── templates/
│   ├── layout.php         # Layout principal
│   └── pages/
│       ├── home.php
│       ├── login.php
│       ├── signup.php
│       ├── dashboard.php
│       ├── accounts/      # Account pages
│       ├── expenses/      # Expense pages
│       ├── incomes/       # Income pages
│       └── previsions.php # Monthly forecast
├── data/
│   └── budgie.db          # SQLite database (générée)
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## Routes principales

| Page | Route | Authentifiée |
|------|-------|--------------|
| Accueil | `/?page=home` | Non |
| Connexion | `/?page=login` | Non |
| Inscription | `/?page=signup` | Non |
| Tableau de bord | `/?page=dashboard` | Oui |
| Comptes | `/?page=accounts` | Oui |
| Détail compte | `/?page=account&id=ID` | Oui |
| Créer compte | `/?page=account-create` | Oui |
| Dépenses | `/?page=expenses` | Oui |
| Détail dépense | `/?page=expense&id=ID` | Oui |
| Créer dépense | `/?page=expense-create&account_id=ID` | Oui |
| Revenus | `/?page=incomes` | Oui |
| Détail revenu | `/?page=income&id=ID` | Oui |
| Créer revenu | `/?page=income-create&account_id=ID` | Oui |
| Prévisions | `/?page=previsions` | Oui |

## Authentification

- Système d'authentification natif avec session PHP
- Hash des mots de passe avec `password_hash()` (bcrypt)
- Seed automatique d'un utilisateur de démo

## Base de données

### Tables

**users**
- `id` (PRIMARY KEY)
- `email` (UNIQUE)
- `full_name`
- `password_hash`
- `created_at`

**accounts**
- `id` (PRIMARY KEY)
- `user_email` (FOREIGN KEY → users)
- `short_name`
- `description`
- `created_at`
- `interest_rate` (annuel %)
- `tax_rate` (%)
- `balance`

**expenses**
- `id` (PRIMARY KEY)
- `account_id` (FOREIGN KEY → accounts)
- `short_name`
- `description`
- `amount`
- `frequency` (ponctuel, mensuel, periodic)
- `frequency_months` (pour periodic)
- `start_date`
- `end_date` (nullable)

**incomes**
- `id` (PRIMARY KEY)
- `account_id` (FOREIGN KEY → accounts)
- `short_name`
- `description`
- `amount`
- `frequency` (ponctuel, mensuel, periodic)
- `frequency_months` (pour periodic)
- `start_date`
- `end_date` (nullable)

## Calcul des prévisions

Pour chaque mois sélectionné, Budgie calcule :

```
Solde début = balance du compte
Revenus = somme des revenus qui surviennent ce mois
Dépenses = somme des dépenses qui surviennent ce mois
Intérêts nets = Solde début × (Taux annuel / 12) × (1 - Taux de taxe)
Solde projeté fin = Solde début + Revenus - Dépenses + Intérêts nets
```

## Git Flow

Branches principales :
- `main` : version stable
- `dev` : branche de développement
- `feature/*` : nouvelles fonctionnalités
- `fix/*` : correctifs

Commits signés avec clé SSH.

## Bonus (à implémenter)

- [ ] Exceptions de fréquence (jours fériés, jours spécifiques)
- [ ] Partage de comptes entre utilisateurs
- [ ] Figma mockups
- [ ] Tests unitaires
- [ ] API REST
- [ ] Souscriptions/subscriptions
- [ ] Export PDF/CSV

## Licence

Privé - Projet annuel 3IW

