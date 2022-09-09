<?php

namespace infrastructure\core\factory;

use infrastructure\core\http\ResponseHtml;
use infrastructure\core\http\ResponseJson;
use infrastructure\core\enums\ResponseType;

class FactoryResponse {

    public static function getResponseType(ResponseType $responseType): ResponseHtml|ResponseJson|null {

        return match ($responseType) {
            $responseType == ResponseType::HTML => new ResponseHtml(),
            $responseType == ResponseType::JSON => new ResponseJson(),
            default => null
        };

    }

}