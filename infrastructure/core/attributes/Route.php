<?php
namespace infrastructure\core\attributes;

use Attribute;
use infrastructure\core\enums\HttpRequest;
use infrastructure\core\enums\StatusCode;

#[Attribute(Attribute::TARGET_METHOD)]
class Route {

    public function __construct(
        public string      $route,
        public StatusCode $code = StatusCode::OK,
        public HttpRequest $type = HttpRequest::GET,
        public string|null $alias = null,
        public             $headers = [],
        public             $requireHeader = []
    ) {
    }


}