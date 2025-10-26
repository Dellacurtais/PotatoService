<?php

namespace infrastructure\core\attributes;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\exception\ServerException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autowired {

    public static $allClass = [];
    protected $last;

    /**
     * Support both native ReflectionAttribute->getArguments() and ReflectionCache payloads.
     * - When used via ReflectionCache, the first parameter may be an array like ['class' => FQCN, 'args' => [...]]
     * - When used natively as an attribute, it will be (string $class, array $args = [])
     */
    public function __construct($class, $args = []){
        // Normalize input in case we're being constructed from cached metadata
        if (is_array($class) && isset($class['class'])) {
            $args = $class['args'] ?? [];
            $class = $class['class'];
        }

        // Ensure args is an array
        if (!is_array($args)) {
            $args = [$args];
        }

        if (!isset(self::$allClass[$class])){
            try{
                // Prefer a static singleton factory if available without using Reflection
                if (is_callable([$class, 'getInstance'])){
                    self::$allClass[$class] = call_user_func([$class, 'getInstance']);
                } else {
                    // Fallback: plain instantiation without ReflectionClass
                    self::$allClass[$class] = new $class(...$args);
                }
            }catch (\Exception $exception){
                if ($exception instanceof ServerException || $exception instanceof BusinessException){
                    throw $exception;
                }
                // As a very last resort, try ReflectionClass (kept for backward compatibility)
                $Reflection = new \ReflectionClass($class);
                self::$allClass[$class] = $Reflection->newInstance(...$args);
            }
        }

        $this->last = self::$allClass[$class];
    }

    public function getClass(){
        return $this->last;
    }

}