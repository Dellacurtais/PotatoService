<?php
namespace infrastructure\core\database;

use Illuminate\Database\Capsule\Manager as Capsule;
use infrastructure\core\database\interfaces\iDriverImplements;

class EloquentDriver implements iDriverImplements {

    public function createConnection(){
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOSTNAME'],
            'database'  => $_ENV['DB_DATABASE'],
            'username'  => $_ENV['DB_USERNAME'],
            'password'  => $_ENV['DB_PASSWORD'],
            'charset'   => $_ENV['DB_CHARSET'],
            'collation' => $_ENV['DB_COLLATION'],
            'prefix'    => $_ENV['DB_PREFIX'],
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

}
