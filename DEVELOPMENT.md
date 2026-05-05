# Guide de développement

## Configuration de l'environnement

### Prérequis

- PHP 8.4+
- Composer (optionnel pour les dépendances futures)
- Git avec SSH key configurée
- SQLite3 (inclus dans PHP par défaut)

### Installation locale

1. Cloner le dépôt :

```bash
git clone git@github.com:ToxZeus/Projet-annuel-3IW.git
cd Projet-annuel-3IW
```

2. Lancer le serveur PHP :

```bash
php -S localhost:8000 -t public
```

3. Accéder à l'application :

**http://localhost:8000**

## Architecture de l'application

### Front controller

`public/index.php` est le point d'entrée unique. Il :
- Démarre la session
- Charge les classes via `require`
- Instancie `App` et appelle `run()`

### Routeur

`src/App.php` gère :
- Routing basé sur paramètre `?page=xxx`
- Handlers POST
- Protection des routes par authentification
- Rendu des templates

### Services

Chaque domaine a son service dans `src/` :

- `UserService` : Gestion utilisateurs (CREATE, LOGIN, VERIFY)
- `AccountService` : Gestion comptes (CRUD)
- `ExpenseService` : Gestion dépenses (CRUD)
- `IncomeService` : Gestion revenus (CRUD)

### Database

`src/Database.php` est un wrapper PDO léger :

- `connect()` : Connexion PDO
- `init()` : Création des tables
- `query()` : Préparation + exécution
- `fetch()` / `fetchAll()` : Résultats
- `exec()` : Modifications (INSERT/UPDATE/DELETE)

### Templates

Tous les templates PHP dans `templates/pages/` :

- `layout.php` : Layout principal avec topbar
- `home.php` : Page d'accueil
- `login.php` / `signup.php` : Auth
- `dashboard.php` : Espace personnel
- `accounts/`, `expenses/`, `incomes/` : CRUD pages
- `previsions.php` : Prévisions mensuelles

## Git Flow

### Branches

- `main` : Version stable
- `dev` : Branche de développement
- `feature/*` : Nouvelles fonctionnalités
- `fix/*` : Correctifs

### Commits

Tous les commits doivent être signés :

```bash
git commit -S -m "type: description"
```

Format de message recommandé :

```
feat: ajouter une fonctionnalité
fix: corriger un bug
chore: tâche administrative
refactor: restructurer du code
docs: mise à jour documentation
```

### Workflow

1. Créer une branche depuis `dev` :

```bash
git checkout dev
git pull origin dev
git checkout -b feature/ma-feature
```

2. Faire des commits atomiques :

```bash
git add fichiers/modifiés
git commit -S -m "feat: description courte"
```

3. Pousser et créer une PR :

```bash
git push -u origin feature/ma-feature
```

4. Une fois approvée, merger dans `dev` :

```bash
git checkout dev
git merge feature/ma-feature --no-ff
git push origin dev
```

## Convention de code

### PHP

- Typage strict : `declare(strict_types=1);`
- Noms de classe en PascalCase
- Noms de fonction/variable en camelCase
- Indentation 4 espaces
- Longueur de ligne max 100 caractères

Exemple :

```php
<?php
declare(strict_types=1);

final class UserService
{
    public function __construct(private Database $db)
    {
    }

    public function create(string $email, string $fullName, string $password): int
    {
        // ...
    }
}
```

### HTML/CSS

- Classes CSS en kebab-case
- BEM (Block-Element-Modifier) pour les composants
- CSS custom properties pour les couleurs

Exemple :

```html
<div class="account-card">
    <h3 class="account-card__title">Mon compte</h3>
    <p class="account-card__description">...</p>
</div>
```

```css
.account-card {
    padding: 24px;
    border-radius: 20px;
}

.account-card__title {
    margin: 0;
    font-size: 1.2rem;
}
```

## Validation

### PHP Lint

Vérifier la syntaxe avant de commiter :

```bash
php -l src/App.php
php -l templates/pages/home.php
```

### Tests manuels

1. Accéder à http://localhost:8000
2. Tester les flows principaux :
   - Inscription avec un nouvel email
   - Connexion avec ce compte
   - Créer un compte
   - Ajouter une dépense/revenu
   - Voir les prévisions

## Ajout d'une nouvelle page

### Étape 1 : Créer le template

`templates/pages/ma-page.php` :

```php
<section class="section">
    <h1>Ma page</h1>
    <!-- Contenu -->
</section>
```

### Étape 2 : Ajouter la route dans App.php

```php
'ma-page' => [
    'title' => 'Budgie | Ma page',
    'template' => 'pages/ma-page.php',
],
```

### Étape 3 : Gérer les données (si nécessaire)

Dans la section data fetching de `run()` :

```php
elseif ($page === 'ma-page') {
    $data['mon_donnee'] = 'valeur';
}
```

### Étape 4 : Protéger si nécessaire

Ajouter la page à la liste des routes protégées :

```php
if (in_array($page, ['dashboard', 'ma-page']) && !$this->isAuthenticated()) {
    header('Location: /?page=login');
    exit;
}
```

## Ressources

- [PHP 8.4 docs](https://www.php.net/manual/fr/index.php)
- [PDO documentation](https://www.php.net/manual/fr/book.pdo.php)
- [SQLite documentation](https://www.sqlite.org/docs.html)
- [CSS Layout](https://developer.mozilla.org/fr/docs/Learn/CSS)

## Questions ?

Consulter les issues GitHub ou demander aux contributeurs.
