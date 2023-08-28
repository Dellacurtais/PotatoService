<?php
namespace infrastructure\core\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cache {

    public bool $isInvalid = true;

    public function __construct(public bool $useUrl = true, public ?string $key = null, public $time = 3600) {}

    public function execute(){
        cacheService()->execute($this);
    }

    public function saveCache($data){
        cacheService()->saveCache($this, $data);
    }

    public static function clearCache(string $key = null){
        cacheService()->clearCache($key);
    }

}