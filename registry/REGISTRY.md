# Installation du Container Registry de GitLab

>:bangbang: 
> J'ai fait sous **Docker**.
>:bangbang:

> Doc : 
https://docs.gitlab.com/install/docker/configuration/
https://gitlab.com/gitlab-org/omnibus-gitlab/blob/master/files/gitlab-config-template/gitlab.rb.template
https://docs.gitlab.com/ee/administration/packages/container_registry.html (Officiel)


## En admin : 

> Aller dans l'Admin area en cliquant sur la clé à molette à côté du profil en haut à droite. On arrive sur le dashboard (ou sinon Overview -> Dashboard), dans l'onglet Features, on voit Container registry qui est normalement **disabled**.

## Pour l'activer :

> Se connecter sur le serveur de GitLab et modifier "GITLAB_OMNIBUS_CONFIG" (pour Docker dans le docker-compose). Modifier ce fichier revient à modifier /etc/gitlab/gitlab.rb. Et ajouter : 
gitlab_rails['registry_path'] = "/var/opt/gitlab/gitlab-rails/shared/registry"
registry['enable'] = true
registry_external_url 'http://gitlab:5005'
registry_nginx['listen_port'] = 5005
registry_nginx['listen_https'] = false

>:bangbang: 
> J'ai tout fait en **HTTP** pour l'utilisation du Registry.
>:bangbang:

> Il faut remplacer 'gitlab' par l'url du vrai GitLab, puis redémarrer GitLab et si on retourne sur l'Admin area le Container Registry sera normalement activé.