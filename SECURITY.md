# Sécurité

## Authentification

- Mots de passe hachés avec `password_hash()` (bcrypt via `PASSWORD_DEFAULT`)
- Activation obligatoire par email (token 64 caractères, expiration 24h)
- Réinitialisation de mot de passe sécurisée (token 64 caractères, expiration 15 min)
- Tokens supprimés après utilisation
- Message générique sur "mot de passe oublié" (prévient l'énumération d'emails)

## Politique de mots de passe

- Minimum 8 caractères
- Au moins 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial (`@$!%*?&`)

## Injection SQL

Toutes les requêtes utilisent des requêtes préparées PDO — aucune interpolation de variable dans le SQL.

## XSS

Toutes les sorties utilisateur sont échappées avec `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

## Variables d'environnement

- Le fichier `.env` est dans `.gitignore` et n'est jamais commité
- Les clés (SMTP, Stripe) sont chargées via `getenv()` ou `$_ENV`

## SSL

- Certificat Let's Encrypt renouvelé automatiquement
- Redirection HTTP → HTTPS configurée dans Nginx

## Sessions

- Sessions PHP natives avec ID régénéré à la connexion
- Données sensibles jamais stockées en clair en session
