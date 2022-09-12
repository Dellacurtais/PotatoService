<?php

namespace infrastructure\core\attributes;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\exception\ServerException;
use infrastructure\core\exception\SetRepositoryAttributeNotFoundException;

#[attribute(Attribute::TARGET_PROPERTY)]
class Autowired {

    protected static $allClass = [];
    protected $last;

    public function __construct($class, $args = []){
        if (!isset(self::$allClass[$class])){
            $Reflection =  new \ReflectionClass($class);
            try{
                $Method = $Reflection->getMethod('getInstance');
                if ($Method && $Method->isStatic()){
                    self::$allClass[$class] = call_user_func([$class, 'getInstance']);
                }
            }catch (\Exception $exception){
                if ($exception instanceof ServerException || $exception instanceof BusinessException){
                    throw $exception;
                }
                self::$allClass[$class] = $Reflection->newInstance(...$args);
            }
            $this->last = self::$allClass[$class];
        }
    }

    public function getClass(){
        return $this->last;
    }

}