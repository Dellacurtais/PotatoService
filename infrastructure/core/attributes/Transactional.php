<?php

namespace infrastructure\core\attributes;

use Illuminate\Database\Capsule\Manager;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Transactional {

    /**
     * @throws \Throwable
     */
    public function begins(): void {
        if (Manager::connection()->getPdo()->inTransaction()){
            Manager::connection()->getPdo()->rollBack();
        }
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

    /**
     * Executa uma função dentro de uma transação
     * @param callable $callback Função a ser executada dentro da transação
     * @return mixed Retorno da função executada
     * @throws \Throwable
     */
    public static function fn(callable $callback): mixed
    {
        $transactional = new self();
        $transactional->begins();

        try {
            $result = $callback();
            $transactional->commit();
            return $result;
        } catch (\Throwable $e) {
            $transactional->rollback();
            throw $e;
        }
    }
}