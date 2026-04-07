# Installation de Renovate

> ***Fil d'Arianne*** :
> * [Home](/README.md)
> * [Configuration de Renovate](RENOVATE.md)

### 1/ Créer un repository vide pour Renovate

> Dans le même groupe ou non sur GitLab (on peut choisir dans quels groupes Renovate a le droit de regarder grâce à `autodiscoverFilter` dans [config.js](./config.js)).
(Doc : https://docs.renovatebot.com/self-hosted-configuration/#autodiscoverfilter).

> Puis clone/push dans ce dépôt le contenu de ce repository : 
:bangbang: ajouter lien repo :bangbang:
(Doc: https://docs.renovatebot.com/examples/self-hosting/) 

### 2/ Créer 2 Tokens pour Renovate

> ***Disclaimer*** : J'ai testé Renovate avec le compte Admin de GitLab

> **1 :** Un Personal Access Token GitLab avec les droits `api`, `read_repository` et `write_repository`. Cliquer sur l'image de profil (GitLab) en haut à droite puis Settings, Access Tokens et ajouter un nouveau token. 

> **2 :** Un Personal Access Token GitHub pour avoir les informations sur chaque version dans la Merge Request. Cliquer sur l'image de profil en haut à droite (GitHub) puis Settings, Developer Settings, Personal access tokens, Tokens (classic) et Generate new token (classic). Aucun droit spécifique n'est nécessaire puisque c'est juste pour examiner les dépôts publics des packages, Renovate a simplement besoin de se connecter.
(Doc : https://docs.renovatebot.com/mend-hosted/github-com-token/)

### 3/ Injecter les tokens dans les variables CI/CD Gitlab

> Dans le dépôt de Renovate fraîchement créé, à gauche, cliquer sur Settings, CI/CD, Variables et ajouter 2 variables :
> - `key: RENOVATE_TOKEN`, `value:` celle du Personal Access Token GitLab
> - `key: GITHUB_COM_TOKEN`, `value:` celle du Personal Access Token GitHub
> 
> On peut laisser Protected (variables injectées uniquement dans les branches protégées) et Masked (non visibles dans la console du Runner).

### 4/ Programmer un pipeline schedule

> Dans le dépôt de Renovate fraîchement créé, à gauche, cliquer sur Build, Pipeline schedules et créer le planning avec un cron. Fréquence recommandée 1 fois par mois (A déterminer), la nuit car le pipeline de chaque projet dans chaques groupes va être exécuté.
(Doc cron : https://crontab.guru/)

### 5/ Ajouter le renovate.json dans les projets

> Le `renovate.json` permet de spécifier les paramètres à appliquer pour le projet en question. Le `renovate.json` d'un projet `extends` (hérite de tous les paramètres) le `default.json` du dépôt de Renovate, qui lui-même `extends` une configuration préconfigurée par Renovate (Doc : https://docs.renovatebot.com/presets-config/). La config du `default.json` est "safe" : elle se base uniquement sur la plage du `composer.json` pour éviter de créer des conflits. Il est tout à fait possible de retirer `extends` et d'avoir une configuration différente pour les projets.

### 6/ Lancer le pipeline schedule (ou attendre qu'il se lance tout seul)

> Renovate se lance, applique les changements et une ou plusieurs MR sont créées (les mises à jour majeures de PHP du Dockerfile et celles des packages sont séparées pour éviter les conflits de versions).

> **Cas OK :** Pas de messages d'erreur composer de Renovate, le build passe, normalement tout est OK.

> **Cas PAS OK 1 :** Pas de messages d'erreur composer de Renovate, le build ne passe pas. Les erreurs sont affichées dans la console du Runner sur GitLab. 

> **Cas PAS OK 2 :** Le pipeline de la MR a échoué. Des messages d'erreur composer de Renovate apparaissent dans les commentaires de la MR.

> **Résolution des cas PAS OK :** Il faut restreindre le `composer.json`. Les seules erreurs que j'ai rencontrées étaient des conflits de version. En effet, une version mineure peut demander une version majeure d'un autre package, mais on **sépare** les majeures et les mineures. **Exemple concret :** Doctrine passe de 2.11 à 2.18 (c'est une mineure), mais celle-ci a besoin de Symfony 6.4 alors que nous sommes en 5.4 → mise à jour impossible ! Pour résoudre cela, il faut ajouter `< x.x.x` **manuellement** dans le `composer.json` pour les packages en question (PHP et/ou JS), afin de restreindre à la dernière version compatible (Il faut regarder sur internet). Ce type de restriction peut aussi être utilisé pour les versions majeures afin d'éviter de sauter trop de versions pour les projets les plus anciens. Évidemment, si une version majeure nécessite un package que nous avons restreint, il faut le dérembourrer pour harmoniser l'ensemble. Pour restreindre les versions de PHP (Dockerfile), il faut passer directement par le renovate.json/default.json à travers "allowedVersions": "< 8.5.0".

### Trucs et astuces 

> **Il n'y a pas de MR pour les versions majeures** : C'est normal, Renovate a créé un Dependency Dashboard au sein des Work Items (menu à gauche) du dépôt du projet en question. Il faut cocher la case "fix(deps): update mises à jour majeures" dans la section "Pending Approval".

### Désactivation de Renovate 

> Désactiver le planning de la pipeline.