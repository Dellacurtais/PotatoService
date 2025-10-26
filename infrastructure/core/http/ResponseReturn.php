<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\ResponseType;

abstract class ResponseReturn {

    protected string|null $file = null;
    protected mixed $params = [];
    protected string|null $message = null;
    protected string|null $status = null;
    protected ResponseType $type = ResponseType::HTML;

    public function __construct(ResponseType $type){
        $this->type = $type;
    }

    public function getType() : ResponseType {
        return $this->type;
    }

}




