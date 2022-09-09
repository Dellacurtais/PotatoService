<?php
namespace infrastructure\core\traits;

trait Singleton {

    protected static $instance;

    /**
     * @return $this
     */
    final public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

}
