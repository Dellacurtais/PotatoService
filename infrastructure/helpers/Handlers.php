<?php

function handler_exception($Execption): void {
    response()->setHeaderType(\infrastructure\core\enums\ContentType::CONTENT_JSON);
    echo json_encode([
        "timestamp" => time(),
        "exception" => get_class($Execption),
        "status" => $Execption->getCode(),
        "path" => request()->requestUri,
        "message" => $Execption->getMessage(),
        "error" => isset($Execption->error_message) ? _($Execption->error_message) : _("Exception")
    ]);
    http_response_code($Execption->getCode());
    exit();
}

function registerAutoloads($class): void {
    $filename = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class) . '.php';

    $filename = str_replace("//", "/", $filename);

    if (file_exists($filename)) {
        require_once($filename);
    }
}
