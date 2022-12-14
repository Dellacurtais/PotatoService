<?php
namespace infrastructure\core\enums;


enum HttpRequest: int {

    case GET = 0;
    case POST = 1;
    case PUT = 2;
    case DELETE = 3;
    case PATCH = 4;

    public static function fromString(string $httpRequest): HttpRequest {
        $httpRequest = strtolower($httpRequest);

        return match($httpRequest) {
            "post" => self::POST,
            "put" => self::PUT,
            "delete" => self::DELETE,
            "patch" => self::PATCH,
            default => self::GET
        };
    }
}