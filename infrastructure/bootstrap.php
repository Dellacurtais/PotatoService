<?php
const INFRA_PATCH = __DIR__;

switch (ENV){
    case 'dev':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;
    case 'prod':
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;
    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}

require_once "vendor/autoload.php";

$Requires = [
    "helpers/Handlers.php",
    "helpers/Core.php",
];

foreach ($Requires as $require){
    require_once $require;
}

spl_autoload_register('registerAutoloads');
set_exception_handler('handler_exception');

\infrastructure\core\PotatoCore::getInstance();
