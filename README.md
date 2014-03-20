Synapse ![Build Status](https://api.travis-ci.org/nehalvpatel/Synapse.png)
=======
Synapse saves sessions to the database.

### Requirements
- PHP 5.4 and up
- MySQL

### Getting Started
1. Install composer
2. Add the [package](https://packagist.org/packages/nehalvpatel/synapse) to your composer.json
3. Run composer

### Example
1. Pass a PDO object to the handler class (a table called "sessions" will be auto generated)
2. Set the save handler to use the class
3. Start the session

```php
  require 'vendor/autoload.php';
  	
  $Synapse = new \Synapse\Handler(new PDO("mysql:host=" . $db_host . ";dbname=" . $db_name, $db_username, $db_password));
  session_set_save_handler($Synapse, true);
  session_start();
  
  $_SESSION["user_id"] = "1";
```
