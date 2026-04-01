# Le Dockerfile

> ***Fil d'Arianne*** :
> * [Home](/README.md)

### Les changements : 

> **Le multi-stage** : Permet de séparer la logique dans notre Dockerfile, les outils de build (composer install, npm install) ne sont plus dans l'image buildée ! En effet, dans celle-ci on copie uniquement le résultat des builds et non les outils, l'image est donc plus légère et on a réduit sa surface d'attaque.

> **Healthcheck** : L'image se ping elle-même pour savoir si elle est en vie (recommandation de Dockle - utile pour l'orchestration).

> **Nginx** : est dans le même Dockerfile que Symfony car on peut utiliser le cache de Docker BuildKit pour ne pas refaire le stage de build et donc avoir le même build que Symfony et gagner du temps par ailleurs. De plus le port exposé est maintenant le **8080** car on utilise le user **nginx** au sein de son stage dans le Dockerfile, et un user non root ne peut pas ouvrir un port < 1024.