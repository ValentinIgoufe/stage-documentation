# Installation de Renovate

> ***Fil d'Arianne*** :
> * [Home](/README.md)
> * [Configuration de Renovate](RENOVATE.md)

### 1/ Créer un repository vide pour Renovate

> Dans le même groupe ou non sur GitLab (on peut choisir dans quels groupes Renovate a le droit de regarder grâce à `autodiscoverFilter` dans [config.js](./config.js)), nommé ***renovate-bot*** (ou un autre, mais il faudra adapter les URLs dans les étapes suivantes).
(Doc : https://docs.renovatebot.com/self-hosted-configuration/#autodiscoverfilter).

> Puis copier le contenu du dossier ***repo_renovate*** à la racine de ce dépôt.

> Les URLs à modifier (:bangbang: Il faut mettre l'URL du dépôt de Renovate sur le GitLab :bangbang:) :
> - [x] dans `config.js` -> `onboardingConfig`, remplacer uniquement `mon-groupe/renovate-bot`, ***laisser le `:default`***, **ET** modifier ***`autodiscoverFilter`*** pour correspondre aux groupes qui seront scannés par Renovate. C'est une liste, on peut donc mettre plusieurs groupes : `['mon-groupe/*', 'mon-groupe2/*']` (attention : supprimer les espaces après les `/` qui pourraient apparaitre dans le markdown)
> - [x] dans `package.json` -> `repository` -> `url`

> Enfin push dans le repository de Renovate.

### 2/ Créer 3 Tokens pour Renovate

> **1 :** Un Personal Access Token GitLab avec les droits `api`, `read_repository` et `write_repository`. Cliquer sur l'image de profil (GitLab) en haut à droite puis Settings, Access Tokens et ajouter un nouveau token avec les droits : api, write_repository et read_repository. 

> **2 :** Un Personal Access Token GitHub pour avoir les informations sur chaque version dans la Merge Request. Cliquer sur l'image de profil en haut à droite (GitHub) puis Settings, Developer Settings, Personal access tokens, Tokens (classic) et Generate new token (classic). Aucun droit spécifique n'est nécessaire puisque c'est juste pour examiner les dépôts publics des packages, Renovate a simplement besoin de se connecter.
(Doc : https://docs.renovatebot.com/mend-hosted/github-com-token/)

> **3 :** Un Personal Access Token DockerHub pour que Renovate puisse proposer les mis à jours des images **hardened** (il faut s'identifier sur le **Docker Hardened Images**). Se connecter sur DockerHub, cliquer sur son profil puis Account Settings, Personal access tokens (à gauche), Generate new token et choisir un nom (Public Repo Read-only est suffisant).

### 3/ Injecter les tokens dans les variables CI/CD Gitlab

> Dans le dépôt de Renovate fraîchement créé, à gauche, cliquer sur Settings, CI/CD, Variables et ajouter 3 variables :
> - `key: RENOVATE_TOKEN`, `value:` celle du Personal Access Token GitLab
> - `key: GITHUB_COM_TOKEN`, `value:` celle du Personal Access Token GitHub
> - `key : DOCKER_AUTH_CONFIG`, `value:` {"auths":{"dhi.io":{"auth":"**string**"}}}. 
> Pour obtenir **string**, powershell : [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes("**username**:**Personal Access Token DockerHub**")). N'est pas utile si vous enlevez les Hardened Images dans la CI ou le build (Syft & anciennement PHP).
> On peut laisser `Protected` (variables injectées uniquement dans les branches protégées :bangbang: ***par défaut, Renovate ne scanne que la branche `main`. Si on veut qu'il ne scanne que `dev`, il faudra définir `dev` comme branche protégée ou décocher `Protected` pour les tokens dans les variables CI/CD, sinon GitLab ne pourra pas les donner au Runner*** :bangbang:) et `Masked` (non visibles dans la console du Runner).

### 4/ Programmer un pipeline schedule

> Dans le dépôt de Renovate fraîchement créé, à gauche, cliquer sur Build, Pipeline schedules et créer le planning avec un cron. Fréquence recommandée : 1 fois par mois (selon préférences), de préférence la nuit, car la pipeline de chaque projet dans chaque groupe sera exécutée.
(Doc cron : https://crontab.guru/)

### 5/ Ajouter le renovate.json dans les projets

> Le `renovate.json` permet de spécifier les paramètres à appliquer pour le projet en question. Le `renovate.json` d'un projet `extends` (hérite de tous les paramètres) le `default.json` du dépôt de Renovate (celui qu'on a créé), qui lui-même `extends` une configuration préconfigurée par Renovate (Doc : https://docs.renovatebot.com/presets-config/). La config du `default.json` est "safe" : elle se base uniquement sur la plage du `composer.json` pour éviter de créer des conflits. Il est tout à fait possible de retirer `extends` (du renovate.json = celui dans chaque projet) et d'avoir une configuration différente pour les projets. **Aussi**, il faut modifier l'URL du `extends` dans le ***renovate.json*** pour qu'il pointe vers le ***default.json*** du dépôt de Renovate **à modifier** : `mon-groupe/renovate-bot`

### 6/ Lancer le pipeline schedule (ou attendre qu'il se lance tout seul)

> Renovate se lance, applique les changements et une ou plusieurs MR sont créées (les mises à jour de PHP du Dockerfile et celles des packages sont séparées pour éviter les conflits de versions).

> **Cas OK :** Pas de messages d'erreur composer de Renovate, le build passe, normalement tout est OK.

> **Cas PAS OK 1 :** Pas de messages d'erreur composer de Renovate, le build ne passe pas. Les erreurs sont affichées dans la console du Runner sur GitLab. 

> **Cas PAS OK 2 :** Le pipeline de la MR a échoué ***dès le départ*** à cause d'un job ***external*** **ET/OU** des messages d'erreur composer, de Renovate, apparaissent dans les commentaires de la MR.

> **Résolution des cas PAS OK :** Il faut restreindre la montée en version (mineures et/ou majeures). Les seules erreurs que j'ai rencontrées étaient des conflits de version. En effet, une version mineure peut demander une version majeure d'un autre package, mais on **sépare** les majeures et les mineures. **Exemple concret :** Doctrine passe de 2.11 à 2.18 (c'est une mineure), mais celle-ci a besoin de Symfony 6.4 alors que nous sommes en 5.4 → mise à jour impossible ! Pour résoudre cela, il faut ajouter : { "matchPackageNames": ["doctrine/doctrine-bundle"], "allowedVersions": "< 2.13.0" } dans le `packageRules` (voir [renovate.json](./renovate.json)) pour les packages en question (PHP/JavaScript), afin de restreindre à la dernière version compatible (il faut vérifier sur internet ou demander à une IA). Ce type de restriction peut aussi être utilisé pour les versions majeures afin d'éviter de sauter trop de versions pour les projets les plus anciens. Évidemment, si une version majeure nécessite un package que nous avons restreint, il faut le débrider pour harmoniser l'ensemble. :bangbang: **À noter** que le `packageRules` du `renovate.json` va **écraser** celui du `default.json`. Il est donc nécessaire de copier/coller pour bien avoir la configuration de base et ensuite modifier/ajouter des paramètres. :bangbang:

### Trucs et astuces 

> **chore(deps): lock file maintenance** : Il est possible de retrouver ceci dans le Dependency Dashboard (Work items). En effet, les mises à jour de Renovate se basent sur le `composer.json`, mais les sous-dépendances ne sont pas inscrites dans celui-ci : elles sont dans le `composer.lock`. Ainsi, si l'on coche cette option de lock file maintenance, Renovate mettra à jour les sous-dépendances.

> **Pourquoi ce gitlab-ci ?** : J'ai copié celui du renovate-runner officiel de GitLab : https://gitlab.com/renovate-bot/renovate-runner

### Désactivation de Renovate 

> Désactiver le planning de la pipeline.