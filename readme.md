# Kustomin Hardware (ByetHost version)
Projet prêt pour hébergement PHP (ByetHost).
Structure:
- public/ (fichiers accessibles)
- api/ (endpoints PHP)
- data/prices.json (liste composants + prix)
- config.php (configuration à remplir)

Instructions:
1. Copier tous les fichiers dans ton hébergement ByetHost. Place le contenu de `public/` dans `htdocs/` et les autres fichiers à la racine ou dans `htdocs` selon ton configuration (les endpoints PHP sont dans /api/).
2. Ouvrir `config.php` et renseigner `STRIPE_SECRET_KEY` et les adresses email.
3. Tester `public/index.html` dans le navigateur.
4. Pour Stripe, utilise les clés de test d'abord.
