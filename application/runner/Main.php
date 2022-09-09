<?php
namespace application\runner;

use application\resources\DemoQueryResource;
use infrastructure\core\http\Routes;
use infrastructure\core\interfaces\iRunner;

class Main implements iRunner {

    public function main(){
        //do any you need
        //core()->systemLangCreator();

        $this->loadResources();
    }

    public function loadResources() {
        Routes::registerResources([
            DemoQueryResource::class
        ]);
    }
}