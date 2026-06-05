# Symfony

> ***Fil d'Arianne*** :
> * [Home](/README.md)

### Points importants :

> **Désactiver DotEnv** : Les `.env` ne sont pas recommandés dans une image Docker (obligation de Dockle), mais Symfony peut en avoir besoin pour démarrer. Les variables d'environnement sont transmises via le `docker-compose`. Étapes à suivre pour désactiver : 
> - [x] `composer.json` : 
    "extra":{
        {
            ...
        },
        "runtime":{ 
            "disable_dotenv": true 
        } 
    }
> - [x] `/tests/bootstrap.php` : commenter la ligne suivante `//(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');`