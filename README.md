# LumiDev_AutoRedirectToProduct

## Description
Le module LumiDev Auto Redirect To Product permet de rediriger l'utilisateur directement sur la fiche produit lorsque la catégorie 
sur laquelle il a cliqué contient qu'un seul produit.
Il améliore l'expérience client en leur faisant gagner du temps.
## Fonctionnalités
- Redirige l'utilisateur directement sur la fiche produit lorsque la catégorie sur laquelle il a cliqué contient qu'un seul produit
- Développé selon les standards Magento 2 (plugins, dependency injection, pas d’override de core)


## Installation

### Via composer
1.composer require lumidev/auto-redirect-to-product
2. Lancer :
   ```bash
   bin/magento module:enable LumiDev_AutoRedirectToProduct
   bin/magento setup:upgrade
   bin/magento cache:flush

### Via `app/code` :
1. Copier le dossier `LumiDev/AutoRedirectToProduct` dans `app/code/`.
2. Lancer :
   ```bash
   bin/magento module:enable LumiDev_AutoRedirectToProduct
   bin/magento setup:upgrade
   bin/magento cache:flush

### Compatibilté
Magento versions : 2.4.3 – 2.4.7
Éditions : Community, Enterprise
PHP versions : 7.4 – 8.2

### Mentions légales

© 2025 LumiDev. Tous droits réservés.

