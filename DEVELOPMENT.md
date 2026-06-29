# Guide de développement

## Prérequis

- PHP 8.3+ avec `pdo_sqlite`
- Docker (recommandé)
- Git

## Lancer en local

```bash
# Avec Docker
docker compose up -d

# Sans Docker
php -S localhost:8000 -t public
```

## Architecture

### Front controller

`public/index.php` — point d'entrée unique. Charge le `.env`, démarre la session, instancie `App` et appelle `run()`.

### Routeur

`src/App.php` — gère le routing via `?page=xxx`, les handlers POST, la protection des routes et le rendu des templates.

### Services

| Service | Responsabilité |
|---|---|
| `UserService` | Inscription, connexion, tokens |
| `AccountService` | CRUD comptes |
| `ExpenseService` | CRUD dépenses |
| `IncomeService` | CRUD revenus |
| `ExceptionService` | CRUD exceptions |

### Database

`src/Database.php` — wrapper PDO léger autour de SQLite.

### Templates

`templates/pages/` — templates PHP par domaine (`accounts/`, `expenses/`, `incomes/`, `exceptions/`).

## Conventions de code

### PHP

- `declare(strict_types=1)` en tête de chaque fichier
- PascalCase pour les classes, camelCase pour les méthodes et variables
- Indentation 4 espaces

### Git

Format de commit :

```
feat: ajouter une fonctionnalité
fix: corriger un bug
refactor: restructurer du code
docs: mise à jour documentation
chore: tâche administrative
```

Workflow :

```bash
git checkout dev
git checkout -b feature/ma-feature
# ... commits ...
git push origin feature/ma-feature
# Pull Request → merge dans dev → merge dans main pour déployer
```

## Ajouter une page

1. Créer `templates/pages/ma-page.php`
2. Ajouter la route dans `$routes` dans `App.php`
3. Ajouter les données dans le bloc `elseif ($page === 'ma-page')` de `run()`
4. Si la page est protégée, l'ajouter dans la liste des routes authentifiées
