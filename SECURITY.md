# Sécurité de Budgie

## Vue d'ensemble

Budgie implémente les meilleures pratiques de sécurité pour protéger les données financières des utilisateurs, y compris :
- Authentification sécurisée avec hachage de mot de passe
- Vérification d'email obligatoire
- Réinitialisation sécurisée de mot de passe
- Validation stricte des données
- Protection contre les attaques CSRF via sessions PHP
- Préparation d'ordres SQL pour prévenir l'injection SQL

## Authentification et Inscription

### Exigences de mot de passe

Les mots de passe doivent respecter une politique stricte pour garantir une sécurité maximale :

- **Longueur minimale** : 8 caractères
- **Caractères requis** :
  - Au moins 1 lettre majuscule (A-Z)
  - Au moins 1 lettre minuscule (a-z)
  - Au moins 1 chiffre (0-9)
  - Au moins 1 caractère spécial (@$!%*?&)

**Exemple de mot de passe valide** : `SecurePass123!`

### Processus d'inscription avec vérification d'email

1. L'utilisateur remplit le formulaire d'inscription avec :
   - Adresse email
   - Nom complet
   - Mot de passe (respectant les critères ci-dessus)
   - Confirmation du mot de passe

2. Budgie génère un token d'activation unique :
   ```php
   $verificationToken = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux
   ```

3. Le token est stocké dans la base de données avec une date d'expiration (24 heures)

4. Un email d'activation est envoyé à l'utilisateur contenant un lien :
   ```
   http://localhost:8000/?page=activate&token=xxxxx...
   ```

5. L'utilisateur doit cliquer sur le lien pour activer son compte
   - Si le token est valide et non expiré : compte activé, token supprimé
   - Si le token a expiré : message d'erreur, l'utilisateur doit redemander l'activation

### Connexion

L'authentification vérifie :
1. L'email existe dans la base de données
2. Le compte est activé (`is_active = true`)
3. Le mot de passe correspond via `password_verify()` (hachage bcrypt)

### Réinitialisation de mot de passe

**Processus sécurisé** :

1. L'utilisateur accède à la page "Mot de passe oublié"
2. Entre son adresse email
3. Budgie génère un reset token avec expiration (15 minutes)
4. Un email est envoyé avec un lien de réinitialisation
5. L'utilisateur clique sur le lien et rentre un nouveau mot de passe
6. Le token est vérifié et supprimé après réinitialisation

**Messages de sécurité** :
- Même si l'email n'existe pas, un message générique est affiché ("Si cette adresse email existe...")
- Cela prévient la énumération d'utilisateurs

## Hachage des mots de passe

Budgie utilise `PASSWORD_DEFAULT` (bcrypt actuellement) :

```php
password_hash($password, PASSWORD_DEFAULT);
password_verify($userInput, $storedHash);
```

**Avantages** :
- Résistant aux attaques par force brute
- Se met à jour automatiquement si le serveur PHP passe à une meilleure algorithmique
- Salage automatique

## Validation des données

### ValidationHelper

- **Emails** : validation avec `filter_var(FILTER_VALIDATE_EMAIL)`
- **Noms** : filtrage des caractères spéciaux
- **Mots de passe** : validation stricte avec regex

```php
ValidationHelper::validatePassword($password);
ValidationHelper::validateEmail($email);
```

### Protection contre l'injection SQL

Toutes les requêtes utilisent les ordres préparés PDO :

```php
$this->db->query(
    'SELECT * FROM users WHERE email = ?',
    [$email]  // Paramètre lié, jamais interpolé
);
```

## Tokens d'activation et de réinitialisation

### Génération de tokens

```php
$token = bin2hex(random_bytes(32));  // 64 caractères hexadécimaux
```

**Propriétés** :
- Cryptographiquement sûr (`random_bytes`)
- Encodé en hexadécimal (lisible en URL)
- Pratiquement impossible à deviner

### Stockage des tokens

Les tokens sont **toujours supprimés après utilisation** :
- Après activation du compte
- Après réinitialisation du mot de passe

### Expiration des tokens

- **Activation** : 24 heures
- **Réinitialisation de mot de passe** : 15 minutes

Comparaison avec la base de données :
```php
if (strtotime($user['token_expiry']) < time()) {
    return false;  // Token expiré
}
```

## Base de données

### Schéma de la table `users`

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    full_name TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    verification_token TEXT,
    token_expiry TEXT,
    reset_token TEXT,
    reset_token_expiry TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT
)
```

**Sécurité** :
- `email` : UNIQUE, prévient les enregistrements en double
- `password_hash` : stocke le hachage, jamais le mot de passe en clair
- `is_active` : empêche la connexion jusqu'à l'activation par email
- Tokens séparés pour activation vs réinitialisation
- Timestamps pour les audits

## Sessions PHP

Les sessions sont utilisées pour maintenir l'état de connexion :

```php
$_SESSION['user'] = [
    'email' => $user['email'],
    'full_name' => $user['full_name'],
];
```

**Configuration recommandée** (PHP.ini) :
```ini
session.cookie_httponly = On    # Prévient l'accès depuis JavaScript
session.cookie_secure = On      # HTTPS uniquement en production
session.gc_maxlifetime = 3600   # Expiration après 1 heure d'inactivité
```

## Variables d'environnement

Budgie utilise un fichier `.env` pour les configurations sensibles :

```env
# Application
APP_URL=http://localhost:8000
APP_ENV=development

# Email
SMTP_HOST=localhost
SMTP_PORT=1025
SMTP_USER=
SMTP_PASSWORD=
SMTP_FROM_EMAIL=noreply@budgie.local
SMTP_FROM_NAME=Budgie
```

**Bonnes pratiques** :
- Ne jamais commiter `.env` (ignoré dans `.gitignore`)
- Utiliser `.env.example` pour la documentation
- Charger les variables avec `getenv()`

## Envoi d'emails

### EmailHelper

```php
EmailHelper::sendActivation($email, $firstname, $token);
EmailHelper::sendPasswordReset($email, $firstname, $token);
```

**Fonctionnalités** :
- HTML + texte brut (accessibilité)
- Boutons cliquables avec lien de fallback
- Logs des erreurs pour débogage
- Configuration flexible (SMTP ou mail() PHP)

## Recommandations pour la production

### HTTPS obligatoire

```php
if (empty($_SERVER['HTTPS'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### Variables d'environnement en production

```bash
export SMTP_HOST=smtp.example.com
export SMTP_PORT=587
export SMTP_USER=noreply@example.com
export SMTP_PASSWORD=xxxxx
```

### Logs de sécurité

```php
error_log("Tentative de connexion échouée : $email");
```

### Rate limiting (TODO)

Implémenter un système de limitation pour prévenir les attaques par force brute.

### CORS et CSP (TODO)

- Content Security Policy headers
- CORS appropriée si API externe

## Dépendances de sécurité

- PHP 8.4 avec support PDO natif
- SQLite3 pour la persistance sécurisée
- Password hashing natif PHP (PASSWORD_DEFAULT = bcrypt)

## Audit et conformité

- Tous les tokens sont tracés avec des timestamps
- Les tentatives de connexion peuvent être loggées
- Les données sensibles ne sont jamais loggées

## Contact de sécurité

Pour les rapports de sécurité, contactez : [email de sécurité]
