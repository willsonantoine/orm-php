# orm-php
Une bibliothèque de code qui facilite la création et la modification des tables dans une base de données #mysql avec #PHP
<strong>Modele d'une table en format JSON</strong>
![myimage-alt-tag](code.png =250x250) 

<h3>Le fichier de configuration de la base des données</h3>
<h3>orm-php/config/init.json</h5>

```json
{
  "database": {
    "host": "localhost",
    "username": "root",
    "password": "root",
    "name": "mydb_1"
  },
  "orm":{
    "folder_table": "./database/",
    "default_string_size": "255"
  }
}
```
<h3>orm-php/database/log.json</h5>
<h4>Exemple</h4>
```json
{
  "id": {
    "type": "STRING",
    "isPrimary": "true",
    "default": null
  },
  "name": {
    "type": "STRING",
    "default": null
  },
  "createAt": {
    "type": "DATETIME",
    "default": "CURRENT_TIMESTAMP"
  },
  "updateAt": {
    "type": "DATETIME",
    "default": "CURRENT_TIMESTAMP"
  }
}
```
  
<strong>orm-php/index.php</strong>
```php
use Configurations\vars;
include './config/cls.php';
include './config/vars.php';
include './config/dbo.php';

$vars = new vars(true);
var_dump($vars->data_response);

```

 
  
