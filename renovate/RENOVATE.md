# Renovate

> ***Fil d'Arianne*** :
> * [Home](/README.md)

> **Documentation de Renovate** : https://docs.renovatebot.com/configuration-options/

> Renovate permet de mettre à jour des dépendances automatiquement en fonction de paramètres que l'on peut lui indiquer.

### Installation

> * [Installation de Renovate](RENOVATE_INSTALLATION.md)

### La configuration de Renovate : 

> :bangbang: 
Les fichiers suivants sont inclus dans repo_renovate mais ne sont peut-être plus à jour.
:bangbang:

#### Le config.js

> C'est la configuration du robot Renovate. 

#### Le default.json

> C'est la configuration globale par défaut de Renovate c'est-à-dire de comment il va mettre à jour les dépendances. 

#### Explications de chaque key dans le default.json

> **extends** : Héritage d'une config (***exemple*** : renovate.json hérite de default.json). Pourquoi **["config:best-practices"]** ? -> https://docs.renovatebot.com/presets-config/.

> **fetchChangeLogs** : Avoir les détails de chaque nouvelle version dans la MR. Pourquoi **off** -> plus il y a de projets plus il y a de MAJs plus il y a de logs = le temps d'exécution de Renovate devient **exponentiel**.

> **pr...** : Permet de définir une limite de MRs, ici 0 donc illimité.

> **recreateWhen** : Permet de définir si Renovate recréer une nouvelle MR si celle-ci a été fermée par un humain.

> **rebaseWhen** : Permet de définir quand Renovate rebase la MR.

> **rangeStrategy** : Permet de définir la portée des MAJ que Renovate va installer. Dans cette configuration, on utilise **bump**, ce qui permet de mettre la version exacte, du package qui est installé, dans le composer.json (pas de ^5.0.0).

> **separateMajorMinor** : Séparer les MAJs majeures et mineures.

> **composerIgnorePlatformReqs** : Permet d'exclure des librairies lors d'un composer update.

> **docker-compose + fileMatch** : Override le nom du fichier docker-compose (***exemple*** : docker-compose.yaml.ci).

> ##### packageRules et les sous-clés
> Permet de définir des règles spécifiques pour certains paquets. Ici, on sépare les MAJ majeures, mineures et celles de l'environnement PHP. 
> **groupName** : Permet de grouper au sein d'une même Merge Request. 
> **semantic... + commit...** : Titre de la MR.
> **matchPackageNames** : Packages autorisés dans cette MR.
> **excludePackageNames** : Packages non autorisés dans cette MR. Pourquoi "containerbase/php-prebuild" -> c'est le package qui définie php dans le composer.json.
> **matchManagers** : Managers de packages autorisés. Pourquoi "custom.regex" -> c'est pour le customManager qui permet de trouver la variable PHP_IMAGE dans le nouveau gitlab-ci.
> **matchUpdateTypes** : Permet de choisir quelles updates on souhaite dans cette MR.
> **excludePackagePatterns** : Exclure des packages qui matchent le pattern, le customManager renvoie le depName de ce qu'il a trouvé, ici php. On l'exclue dans cette key car ça ne fonctionnait pas dans excludePackageNames.
> **dependencyDashboardApproval** : Mettre la MR en attente dans le dependencyDashboardApproval (Work items).
> **postUpdateOptions** : Dire quoi faire après la modification des composer/package.json -> mettre à jour les locks.
> ##### Labels
> Afficher un petit label pour visualiser sur quels fichiers ont été fait les changements de Renovate.
> ##### Resteindre la montée en version
> **allowedVersions (packagesRules)** : Permet de resteindre les versions acceptées pour un package rule, utile si le composer update échoue dans une MR (**symptômes** : message de Renovate BOT comme quoi le composer update a failed dans la MR ou dans le build, et dans `changes` dans la MR le composer.lock n'est pas là).

> ##### customManagers 
> Regex pour trouver la variable PHP_IMAGE dans le gitlab-ci.

##### Informations : 

> Si on déplace Renovate d'un dépôt à un autre ou d'un groupe à un autre, il faut modifier l'URL du dépôt actuel dans config.js -> onboardingConfig et package.json -> repository -> url.
