# Le docker compose

> ***Fil d'Arianne*** :
> * [Home](/README.md)

### Les ajouts : 

> **CAP_DROP et CAP_ADD** : Permet de restreindre les droits du user 1000:1000 ou de nginx encore plus.

> **Healthcheck** : Permet de démarrer tous les services dans l'ordre des ***depends_on*** en healthy (on attend que le service soit prêt avant de démarrer les autres : docker compose up -d --wait).

### Le stockage en local :

> **Possible** 
:bangbang: à condition que ce soit le user 1000:1000 sur la VM car c'est lui dans l'image Docker.
:bangbang: