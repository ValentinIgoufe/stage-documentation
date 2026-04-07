# Le workflow de la pipeline

> ***Fil d'Arianne*** :
> * [Home](/README.md)

#### lint :
> **Hadolint** : Analyse statique du Dockerfile 
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

> **BuildKit** : Build des images Nginx et PHP, utilisation du cache des layers Docker dans le Registry GitLab au lieu de stocker une 'vraie' image. Les tags des images pointent vers les layers du cache, système de pointeurs, comme en langage ***C***. Gain de place car on a pas 2 fois la même chose. 
:bangbang: Il est nécessaire de configurer le Registry pour supprimer les layers qui ne sont pointées par aucun tag.
Permet aussi de générer la provenance de notre build (pour SLSA).
(Doc : https://docs.docker.com/reference/cli/docker/buildx/build/)
(Doc : https://blog.stephane-robert.info/docs/conteneurs/images-conteneurs/build/buildkit/)

#### test :  

> **Tests unitaires - PHPUnit** : Tests unitaires

> **"Smoke Test"** : Permet de tester les droits du user (changer l'heure, whoami, docker inspect, etc...) car ceux-ci sont restreints à partir du docker compose.

> **DAST - OWASP ZAP** : Analyse dynamique de l'application, elle curl les URLs de l'app pour trouver des failles de sécurité au niveau de nginx (***ex*** : Anti-clickjacking, X-Content-Type-Options, Server Version, CSP, Anti-CSRF, et bien d'autres... Celles-ci ont été trouvées sur CAB_DRH_FMD). Voir fichier de configuration [zap.yaml](./zap.yaml).

> **Fuzzing - FFUF** : Permet de bombarder une URL (GET,POST,PUT,PATCH,DELETE) (ici le /login) avec des valeurs aberrantes (***voir [fuzz.txt](./fuzz.txt)***). Avec la console web, on peut facilement voir les variables qui sont envoyées en POST et vers quelle URL, ainsi on peut bombarder le controller pour voir comment il réagit. Sur ***CAB_DRH_FMD***, on remarque qu'**aucune** vérification est effectuée sur la data reçue du frontend ! Un simple body **VIDE** (via Postman) déclenche une erreur 500 ! 

> **Tests de charge - Grafana k6** : Simuler n utilisateurs en parallèles sur l'application.

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

> **Deploy** : Vérification des signatures et déploiement en prod/test avec docker compose pull puis up et génération du .env à la volée grâce aux variables GitLab CI. De plus, étant donné qu'on ne stocke que du cache dans le Registry, on peut facilement rollback de versions images (le tag d'une image contient le sha du commit git).