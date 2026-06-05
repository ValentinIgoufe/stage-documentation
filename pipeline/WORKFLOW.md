# Le workflow de la pipeline

> ***Fil d'Arianne*** :
> * [Home](/README.md)

>:bangbang:
**Certains outils nécessitent d'être installés (composer.json) ou ont besoin d'un token etc pour fonctionner.**
[Voir installation des jobs](./JOBS_INSTALLATION.md)
:bangbang:

#### Plumber : 

> Donner une note au pipeline.
> Voir la configuration dans [`.plumber.yaml`](./.plumber.yaml).

#### lint :
> **Hadolint** : Analyse statique du Dockerfile (**peut être modifier le chemin du Dockerfile**)
(Doc : https://blog.stephane-robert.info/docs/conteneurs/outils/hadolint/)

> **Composer Unused** : Liste les packages inutilisés explicitement dans le code.

> **PHP CS-fixer** : Correction du style PHP

> **Twigcs** : Vérification de la syntaxe Twig

#### analysis (SAST) :

> **Semgrep** : Analyse statique de la sécurité du code (Injection SQL, Secrets écrits en brute, etc...)
(Doc : https://blog.stephane-robert.info/docs/securiser/analyser-code/semgrep/)

> **PHPStan** : Analyse en profondeur et stricte de PHP (typage, etc...), différents niveaux (0 à 9) d'analyse : le niveau 9 relève **toutes** les erreurs. Voir fichier de configuration [phpstan.neon](./phpstan.neon).
(Doc : https://phpstan.org/)

> **DepTrac** : Analyse de l'architecture (MVC, Hexagonale, etc...). Voir fichier de configuration : [deptrac.yaml](./deptrac.yaml)
(Doc : https://github.com/deptrac/deptrac)

#### refactor :

> **Rector** : Refactorisation du code en fonction des règles définies dans le [rector.php](./rector.php). Il peut notamment effectuer une migration de versions PHP (de 8.1 à 8.2, etc...) si des noms de fonctions changent, etc...
(Doc : https://getrector.com/)

#### build :

> **BuildKit** : Build des images Nginx et PHP, utilisation du cache des layers Docker dans le Registry GitLab au lieu de stocker une 'vraie' image. Les tags des images pointent vers les layers du cache, système de pointeurs comme en langage ***C***. Gain de place car on n'a pas deux fois la même chose. 
:bangbang: Il est nécessaire de configurer le Registry pour supprimer les layers qui ne sont pointées par aucun tag.
Permet aussi de générer la provenance de notre build (pour SLSA). :bangbang:
(Doc : https://docs.docker.com/reference/cli/docker/buildx/build/)
(Doc : https://blog.stephane-robert.info/docs/conteneurs/images-conteneurs/build/buildkit/)

#### test :  

> **Tests unitaires - PHPUnit** : Tests unitaires

> **"Smoke Test"** : Permet de tester les droits du user (`changer l'heure`, `whoami`, `docker inspect`, etc.) car ceux-ci sont restreints par le `docker compose`.

> **DAST - OWASP ZAP** : Analyse dynamique de l'application. Il s'agit de curl les URLs de l'app pour trouver des failles de sécurité au niveau de nginx (***ex*** : Anti-clickjacking, X-Content-Type-Options, Server Version, CSP, Anti-CSRF, et bien d'autres...). Celles-ci ont été trouvées sur CAB_DRH_FMD. Voir le fichier de configuration [zap.yaml](./zap.yaml).

> **Fuzzing - FFUF** : Permet de bombarder une URL (GET,POST,PUT,PATCH,DELETE) (ici le /login) avec des valeurs aberrantes (***voir [fuzz.txt](./fuzz.txt)***). Avec la console web, on peut facilement voir les variables qui sont envoyées en POST et vers quelle URL, ainsi on peut bombarder le controller pour voir comment il réagit. Sur ***CAB_DRH_FMD***, on remarque qu'**aucune** vérification est effectuée sur la data reçue du frontend ! Un simple body **VIDE** (via Postman) déclenche une erreur 500 ! 

> **Tests de charge - Grafana k6** : Simuler n utilisateurs en parallèle sur l'application.
> Voir le scénario dans [load-test.js](./load-test.js).

> **InfectionPHP** : Tests de mutations, vérification de la qualité des tests. Job manuel et/ou planifié car long (~40 min) et gourmand. D'après mon professeur, c'est **30%** de bugs détectés avant la mise en prod ! Voir fichier de configuration [infection.json.dist](./infection.json.dist).
(Doc : https://infection.github.io/)

#### scan :

> **Dockle** : Analyse de l'image buildée, vérifie si le container n'est pas lancé en root, s'il y a un healthcheck, s'il n'y a pas de fichiers temporaires, pas de Dockerfiles, etc...
(Doc : https://blog.stephane-robert.info/docs/securiser/outils/dockle/)

> **Syft** : Génération du SBOM (toutes les librairies systèmes, php, npm, etc...) de l'image buildée.
(Doc : https://github.com/anchore/syft)

> **Grype** : Analyse de ce SBOM pour trouver les CVE connues, la pipeline échoue si des CVE High sont trouvées et que l'on peut les corriger en mettant à jour le package. 
(Doc : https://blog.stephane-robert.info/docs/securiser/outils/grype/)

#### push :

> **Promote** : On promeut nos images en :latest

#### sign :

> **SLSA & Cosign** : SLSA de niveau 2 qui tend vers le niveau 3, signature de l'image, du SBOM et de la provenance, GitLab runners qui exécutent les jobs dans des runners isolés grâce à Docker. Pour atteindre le niveau 3, il faudrait utiliser GitLab.com pour que lors de la génération de la provenance par Docker BuildKit, un autre organisme (Fulcio) signe celle-ci. Ici on signe tout dans la CI. 
(Doc : https://blog.stephane-robert.info/docs/securiser/supply-chain/slsa/)

#### deploy :

>:bangbang:
**Sera potentiellement à adapter !!**
:bangbang:

> **Deploy** : Connexion SSH, scp le docker-compose (généré à la 'volée' car on deploy avec le digest de l'image docker construite), git pull (si besoin), docker compose pull et docker compose up (-d --wait, si on veut).  