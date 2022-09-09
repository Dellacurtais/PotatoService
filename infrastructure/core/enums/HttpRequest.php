<?php
namespace infrastructure\core\enums;


enum HttpRequest: int {

    case GET = 0;
    case POST = 1;
    case PUT = 2;
    case DELETE = 3;
    case PATCH = 4;

    public static function fromString(string $httpRequest): HttpRequest {
        return match(strtolower($httpRequest)) {
            $httpRequest == "post" => self::POST,
            $httpRequest == "put" => self::PUT,
            $httpRequest == "delete" => self::DELETE,
            $httpRequest == "patch" => self::PATCH,
            default => self::GET
        };
    }
}