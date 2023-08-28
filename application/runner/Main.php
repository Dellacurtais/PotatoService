<?php
namespace application\runner;

use application\resources\DemoQueryResource;
use infrastructure\core\http\Routes;
use infrastructure\core\interfaces\iRunner;

class Main implements iRunner {

    private array $resources = [
        DemoQueryResource::class
    ];

    public function main(): void {
        Routes::registerResources($this->resources);
    }

    public function onStart() {
        // TODO: Implement onStart() method.
    }

    public function afterDatabaseConnection() {
        // TODO: Implement afterDatabaseConnection() method.
    }

    public function onFinish() {
        // TODO: Implement onFinish() method.
    }

    public function onDetectNewVersion()
    {

    }
}