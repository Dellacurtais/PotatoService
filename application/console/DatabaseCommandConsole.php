<?php

namespace application\console;

use infrastructure\core\database\Migration;
use infrastructure\core\interfaces\iConsole;

class DatabaseCommandConsole implements iConsole {

    public function execute($args, $callback): void {
        $function = array_shift($args);
        if (method_exists($this, $function)){
            $this->$function($args);
        }else{
            logError("Wrong args for database");
        }
    }

    public function setup($attrs): void {
        logWarning("This setup only works with MySQL/MariaDB databases. If you are using others, please configure manually.");
        logInfo('Enter the drive name (Ex: mysql):');
        $drive = fgets(STDIN);
        $drive =  trim($drive);

        logInfo('Enter the hostname:');
        $hostname = fgets(STDIN);
        $hostname =  trim($hostname);

        logInfo('Enter the database name:');
        $database = fgets(STDIN);
        $database =  trim($database);

        logInfo('Enter the username:');
        $username = fgets(STDIN);
        $username = trim($username);

        logInfo('Enter the password:');
        $password = fgets(STDIN);
        if (str_ends_with($password, "\n")) {
            $password = substr($password, 0, strlen($password) - 1);
        }

        logInfo('Enter the charset (empty = utf8):');
        $charset = fgets(STDIN);
        $charset = trim($charset);
        $charset =  empty($charset) ? 'utf8' : $charset;

        logInfo('Enter the collation (empty = utf8_unicode_ci):');
        $collation = fgets(STDIN);
        $collation = trim($collation);
        $collation =  empty($collation) ? 'utf8_unicode_ci' : $collation;

        $envFile = file_get_contents(INFRA_PATCH.'/.env');
        $envFile = preg_replace('/^DB_DRIVE=.+$/m', "DB_DRIVE={$drive}", $envFile);
        $envFile = preg_replace('/^DB_HOSTNAME=.+$/m', "DB_HOSTNAME={$hostname}", $envFile);
        $envFile = preg_replace('/^DB_DATABASE=.+$/m', "DB_DATABASE={$database}", $envFile);
        $envFile = preg_replace('/^DB_USERNAME=.+$/m', "DB_USERNAME={$username}", $envFile);
        $envFile = preg_replace('/^DB_PASSWORD=.+$/m', "DB_PASSWORD={$password}", $envFile);
        $envFile = preg_replace('/^DB_CHARSET=.+$/m', "DB_CHARSET={$charset}", $envFile);
        $envFile = preg_replace('/^DB_COLLATION=.+$/m', "DB_COLLATION={$collation}", $envFile);

        file_put_contents(INFRA_PATCH.'/.env', $envFile);

        core()->restartDatabase();

        logSuccess('Database configuration done!');
    }

    public function enable($attrs): void {
        $envFile = file_get_contents(INFRA_PATCH.'/.env');
        $envFile = preg_replace('/^DISABLE_DATABASE=.+$/m', "DISABLE_DATABASE=0", $envFile);
        file_put_contents(INFRA_PATCH.'/.env', $envFile);
        logSuccess('Database enable done!');
    }

    public function disable($attrs): void {
        $envFile = file_get_contents(INFRA_PATCH.'/.env');
        $envFile = preg_replace('/^DISABLE_DATABASE=.+$/m', "DISABLE_DATABASE=1", $envFile);
        file_put_contents(INFRA_PATCH.'/.env', $envFile);
        logSuccess('Database enable done!');
    }

    public function migrate($attrs): void {
        logInfo("Started Migration");
        $migrate = new Migration();
        $migrate->migrate();
        logInfo("Finished Migration");
    }
}