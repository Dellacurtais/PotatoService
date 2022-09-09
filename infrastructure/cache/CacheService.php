<?php
namespace infrastructure\cache;

use infrastructure\core\interfaces\iCache;
use infrastructure\core\attributes\Cache;

class CacheService implements iCache {

    public function execute(Cache $cache){
        $cacheFile = $this->getFileInDisk($cache);
        if (file_exists($cacheFile) && (time() - $cache->time) < filemtime($cacheFile)) {
            $cache->isInvalid = false;
            readfile($cacheFile);
        }
    }

    public function saveCache(Cache $cache, $data){
        file_put_contents($this->getFileInDisk($cache), $data);
        echo $data;
    }

    public function clearCache($key = null){
        $cacheFile = INFRA_PATCH . '/cache/request/' . base64_encode($key) .".cache";
        if (is_file($cacheFile)){
            @unlink($cacheFile);
        }
    }

    private function getFileInDisk(Cache $cache): string|null {
        if ($cache->useUrl){
            return INFRA_PATCH . '/cache/request/' . base64_encode(request()->requestUri) .".cache";
        }else{
            return INFRA_PATCH . '/cache/request/' . base64_encode($cache->key) .".cache";
        }
    }

}