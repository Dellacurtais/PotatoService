<?php
namespace infrastructure\core\interfaces;

interface iRunner {

    public function onStart();

    public function onDetectNewVersion();

    public function afterDatabaseConnection();

    public function main();

    public function onFinish();

}