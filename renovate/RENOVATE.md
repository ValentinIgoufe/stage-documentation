# Renovate

> ***Fil d'Arianne*** :
> * [Home](/README.md)

> **Documentation de Renovate** : https://docs.renovatebot.com/configuration-options/

> Renovate permet de mettre à jour des dépendances automatiquement en fonction de paramètres que l'on peut lui indiquer.

### Installation

> * [Installation de Renovate](RENOVATE_INSTALLATION.md)

### La configuration de Renovate : 

> :bangbang: 
Les fichiers suivants sont inclus dans ce repository mais ne sont peut-être plus à jour.
:bangbang:

#### Le config.js

> C'est la configuration du robot Renovate. 

#### Le default.json

> C'est la configuration globale de Renovate c'est-à-dire de comment il va mettre à jour les dépendances. 

##### Les paramètres importants : 

> **rangeStrategy** : Permet de définir la portée des MAJ que Renovate va installer. Dans cette configuration, on utilise **in-range-only** ce qui permet de ne mettre à jour que dans la portée du composer.json, pour éviter des problèmes de compatiblités etc. On utilise aussi **bump** pour le PHP, ce qui lui permet de ne pas respecter le composer.json et de **sauter** à la dernière version disponible (Peut évidemment casser le code si les libs ne suivent pas...). 
:bangbang: Je recommande de limiter la montée de version que ce soit de PHP ou des librairies (***exemple*** : "php": "^8.1 <8.5") car il y a de **grandes** chances que le build échoue ou que le composer/npm update échoue car des versions ne seront plus compatibles entre elles (***exemple*** : Symfony 5.4 et Doctrine 2.18).

> **Packages rules** : Permet de définir des règles spécifiques pour certains paquets. Ici, on sépare les MAJ majeures et mineures. Seules les MAJ mineures seront proposées à travers une Merge Request de Renovate, les majeures quant à elles seront dans le Dependancy Dashboard (***"dependencyDashboardApproval": true***). Une case à cocher permettra à Renovate de créer la MR avec les MAJ majeures. L'option **postUpdateOptions** permet à Renovate d'exécuter une commande après avoir fait la mise à jour, le but est donc ici de synchroniser les lock et les json pour éviter tout problème de compatibilités. 
:bangbang: **RangeStrategy** et **matchUpdateTypes** sont incompatibles dans un même **Package rules**. Un package rule qui a matchUpdateTypes doit donc hériter de la configuration globale (c'est-à-dire rangeStrategy en dehors du bloc Packages rules). Il est cependant possible de définir une RangeStrategy pour chaque packge rule qui n'a pas de matchUpdateTypes (***exemple*** : PHP du Dockerfile ainsi que les MAJ majeures des dépendances PHP et NPM ). Les MAJ de PHP et des dépendances sont séparées pour éviter des problèmes de compatiblités de versions et autres.

> **matchUpdateTypes** : Permet de choisir quelles updates on souhaite dans un **Package rule** (***exemple*** : major, minor, patch, etc...).

##### Les paramètres moins importants : 

> **groupName (Packages Rules)** : Permet de grouper au sein d'une même Merge Request. 

> **composerIgnorePlatformReqs** : Permet d'exclure des librairies lors d'un composer update.

> **separateMajorMinor** : Si ***true*** entre en conflit avec les groupNames des Packages rules et on peut pas séparer les MAJs PHP (Dockerfile) et les librairies PHP (Symfony,etc). Donc ici on met en **false** et on gère tout.

##### Informations : 

> Si on déplace Renovate d'un dépôt à un autre ou d'un groupe à un autre, il faut modifier l'URL du dépôt actuel dans default.json -> customManagers, config.js -> onboardingConfig et package.json -> repository -> url.
