<?php

namespace infrastructure\core\attributes;

use Illuminate\Database\Capsule\Manager;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Transactional {

    public function __construct(){}

    public function begins(){
        Manager::connection()->beginTransaction();
    }

    public function commit(){
        Manager::connection()->commit();
    }

    public function rollback(){
        Manager::connection()->rollBack();
    }

}