<?php

namespace application\console;

use infrastructure\core\interfaces\iConsole;

class CacheCommandConsole implements iConsole {

    public function execute($args): void{
        $function = array_shift($args);
        if (method_exists($this, $function)){
            $this->$function($args);
        }else{
            logError("Wrong args for cache");
        }
    }

    public function clearingRoute($attrs): void {
        logInfo("Starting route cache cleanup");

        @removeFiles(INFRA_PATCH. '/cache/routes');

        logInfo("Finishing route cache clearing");
    }

    public function clearingRequest($attrs): void {
        logInfo("Starting request cache cleanup");

        @removeFiles(INFRA_PATCH. '/cache/request');

        logInfo("Finishing request cache clearing");
    }

    public function clearingSmarty($attrs): void {
        logInfo("Starting smarty cache cleanup");

        @removeFiles(INFRA_PATCH. '/cache/request');

        logInfo("Finishing smarty cache clearing");
    }

}