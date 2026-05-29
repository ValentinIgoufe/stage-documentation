# Symfony

> ***Fil d'Arianne*** :
> * [Home](/README.md)

### Points importants :

> **Désactiver DotEnv** : Car les .env ne sont pas les bienvenus dans une image Docker (obligation de Dockle), mais Symfony semble en avoir besoin pour démarrer, les var d'env sont transmises via le docker-compose. Étapes à suivre pour désactiver : 
> - [x] composer.json : 
    "extra":{
        {
            ...
        },
        "runtime":{ 
            "disable_dotenv": true 
        } 
    }
> - [x] /tests/bootstrap.php : commenter la ligne suivante //(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');