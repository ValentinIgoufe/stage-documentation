# Installation des outils de la CI

## require-dev de `composer.json`
> (**Les versions peuvent être obsolètes**)
>   "infection/infection": "0.33.2",
    "phpstan/phpstan": "^2.1",
    "phpstan/phpstan-deprecation-rules": "^2.0",
    "phpstan/phpstan-doctrine": "^2.0",
    "phpstan/phpstan-strict-rules": "^2.0",
    "phpstan/phpstan-symfony": "^2.0",
    "deptrac/deptrac": "^2.0",
    "rector/rector": "^2.3.8",

## Plumber : 

> :bangbang:
**Ne sert qu'à donner une note au pipeline (75/100). On peut l'enlever.**
:bangbang:

> Cloner le dépôt officiel de Plumber, récupérer tous les tags de release, puis pousser dans un dépôt spécifique sur GitLab (changer l'URL dans le `.gitlab-ci.yml`).
> Dernier rapport de Plumber : [Voir](./gl-sast-report.json).
> **Ce job a besoin d'un `GITLAB_TOKEN` (à mettre dans les variables CI/CD du groupe) pour fonctionner avec les droits `read_repository` et `read_api`.**

## Rector : 
> **A besoin d'un Token GitLab** (Groupe Access Token ou Project Access Token), se rendre sur un groupe ou projet, settings, access tokens, add new token, scopes write_repository, role developer. 
> Mettre le token dans une var CI/CD `RECTOR_TOKEN` du groupe ou projet.
> **Lignes sujettes à modifications:**    
> - **(241)** git remote set-url origin "http://RECTOR-BOT:${RECTOR_TOKEN}@${CI_SERVER_HOST}/${CI_PROJECT_PATH}.git"
> - **(248)** git commit -m "chore(rector): apply rector refactoring [skip ci] -> le skip ci permet de skip la ci.
> Le RECTOR-BOT désigne le user qui fait la MR.
> Si problèmes rencontrés Rector fonctionne en **local** avec grumphp !


## Cosign : 

### Génération des clés publique et privée

> `cosign generate-key-pair`, puis saisir un mot de passe. On obtient ensuite `cosign.key` et `cosign.pub`.
> Exemple PowerShell :
> `docker run --rm -it -v "${PWD}:/keys" -w /keys cgr.dev/chainguard/cosign:latest@sha256:7a8921f7bb64e832ac0f4ea9ab014ac838b14303b3e19eaf78f4e27aa383af17 generate-key-pair`

### Variables CI/CD 

> Mettre les variables en protected

> - `COSIGN_PASSWORD` : valeur = le mot de passe saisi à l'étape précédente
> - `COSIGN_PRIVATE_KEY` : valeur = tout le contenu du fichier `cosign.key`
> - `COSIGN_PUBLIC_KEY` : valeur = tout le contenu du fichier `cosign.pub`

## (Si besoin) pour tirer les images indéfiniment

> **DOCKER_HUB_PASS** (à mettre dans les variables CI/CD du groupe) et utiliser l'ancre `docker_hub_login_pattern` dans le `.gitlab-ci.yml` en remplaçant `-u username`.

