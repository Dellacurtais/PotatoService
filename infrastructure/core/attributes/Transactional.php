<?php

namespace infrastructure\core\attributes;

use Illuminate\Database\Capsule\Manager;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Transactional {

    /**
     * @throws \Throwable
     */
    public function begins(): void {
        Manager::connection()->beginTransaction();
    }

    /**
     * @throws \Throwable
     */
    public function commit(): void {
        Manager::connection()->commit();
    }

    /**
     * @throws \Throwable
     */
    public function rollback(): void {
        Manager::connection()->rollBack();
    }

}