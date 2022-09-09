<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\ContentType;
use infrastructure\core\enums\ResponseType;
use infrastructure\core\factory\FactoryResponse;
use infrastructure\core\traits\Singleton;

class Response {

    use Singleton;

    public function setHeader(string $key, mixed $value = null){
        if (is_null($value)) {
            header($key);
        }else{
            header("{$key}:{$value}");
        }
    }

    public function setHeaderType(string $type){
        $this->setHeader("Content-Type", $type);
    }

    public function json() : ResponseJson {
        $this->setHeaderType(ContentType::CONTENT_JSON);
        return FactoryResponse::getResponseType(ResponseType::JSON);
    }

    public function html(): ResponseHtml|null {
        $this->setHeaderType(ContentType::CONTENT_HTML);
        return FactoryResponse::getResponseType(ResponseType::HTML);
    }

}