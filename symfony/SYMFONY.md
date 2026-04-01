# Symfony

> ***Fil d'Arianne*** :
> * [Home](/README.md)

### Points importants :

> **Désactiver DotEnv** : Car les .env ne sont pas les bienvenus dans une image Docker (obligation de Dockle), mais Symfony semble en avoir besoin pour démarrer (***les variables d'environnement sont transmises via le docker compose via un .env construit en CI***). Si les tests en dépendent, il est donc nécessaire de faire un compose up en CI par ex. Étapes à suivre pour désactiver : 
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