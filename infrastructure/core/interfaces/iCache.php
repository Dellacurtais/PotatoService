<?php
namespace infrastructure\core\interfaces;

use infrastructure\core\attributes\Cache;

interface iCache {

    public function execute(Cache $cache);
    public function saveCache(Cache $cache, mixed $data);
    public function clearCache(mixed $key);

}