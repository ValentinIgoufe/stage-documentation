# Le Dockerfile

> ***Fil d'Arianne*** :
> * [Home](/README.md)

### Les changements : 

> **Le multi-stage** : Permet de séparer la logique dans notre Dockerfile. Les outils de build (`composer install`, `npm install`) ne sont plus dans l'image de prod : on ne copie que le résultat des builds, ce qui rend l'image plus légère et réduit sa surface d'attaque. Nous utilisons des images hardened pour la prod, proches de 0 CVE et très légères : on passe de ~2 Go à 400 Mo pour PHP sur FMD. 
:bangbang:
C'est vrai, on gagne de la place, mais il manque des dépendances système pour PHP. On ne peut donc pas toujours utiliser une image hardened prête à l'emploi, ou il faut payer pour une image hardened avec `sh` et `apt` (https://hub.docker.com/hardened-images/catalog/dhi/php).
:bangbang:

> **Healthcheck** : L'image se ping elle-même pour savoir si elle est en vie (recommandation de Dockle - utile pour l'orchestration).

> **Nginx** : est dans le même Dockerfile que Symfony car on peut utiliser le cache de Docker BuildKit pour ne pas refaire le stage de build et donc avoir le même build que Symfony et gagner du temps par ailleurs. De plus le port exposé est maintenant le **8080** car on utilise le user **nginx** au sein de son stage dans le Dockerfile, et un user non root ne peut pas ouvrir un port < 1024.