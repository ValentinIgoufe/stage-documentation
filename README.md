# Documentation 

### Renovate et la pipeline 

#### Sommaire : 

> * [Renovate](renovate/RENOVATE.md)

> * [Pipeline GitLab CI](pipeline/WORKFLOW.md)

> * [Dockerfile](docker/_DODCKERFILE.md)

> * [Docker compose](docker/DOCKERCOMPOSE.md)

> * [Symfony](symfony/SYMFONY.md)

> * [Nginx](nginx/NGINX-CONF.md)


### Glossaire : 

> **SAST (Static Application Security Testing)**
***C'est quoi ?*** L'analyse "boîte blanche". On scanne le code source sans l'exécuter.
Dans ton projet : C'est ce que font PHPStan, Semgrep ou Deptrac à un niveau de qualité, mais des outils comme SonarQube ou Snyk cherchent spécifiquement des failles (ex: une injection SQL évidente).
Avantage : Détecte les erreurs très tôt (shift-left).

> **DAST (Dynamic Application Security Testing)**
***C'est quoi ?*** L'analyse "boîte noire". On teste l'application pendant qu'elle tourne (en staging/prod).
Analogie : C'est un hacker gentil qui essaie de forcer ta porte d'entrée (ton site web) pour voir si elle cède.
Avantage : Trouve des failles que le code seul ne montre pas (ex: mauvaise config serveur, cookies non sécurisés).

> **SBOM (Software Bill of Materials)**
***C'est quoi ?*** La liste des ingrédients de ton logiciel.
Pourquoi ? Ton projet Symfony utilise des centaines de paquets (via Composer). Le SBOM inventorie tout (versions, licences).
Utilité : Si une faille est découverte dans une petite bibliothèque obscure, le SBOM te permet de savoir instantanément si la mairie est en danger.

> **CVE (Common Vulnerabilities and Exposures)**
***C'est quoi ?*** Le dictionnaire public des failles de sécurité connues. Chaque faille a son numéro (ex: CVE-2024-1234).
Quand tu fais un composer audit, il compare tes dépendances avec la base de données des CVE pour voir si tu utilises une passoire.

> **SLSA (Supply chain Levels for Software Artifacts)**
***C'est quoi ?*** (Prononcé "Salsa"). C'est un standard de certification pour la chaîne de fabrication.
L'idée : Prouver que le code qui tourne en prod est bien celui que tu as écrit, et qu'il n'a pas été modifié par un pirate entre le moment où tu as poussé sur Git et le déploiement.
Niveaux : Plus tu as de preuves (logs signés, build isolé), plus ton niveau SLSA est élevé (1 à 4).

###### 5 juin 2026 Valentin Igoufe