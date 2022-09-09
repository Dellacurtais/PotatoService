<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\ResponseType;

abstract class ResponseReturn {

    protected string|null $file;
    protected mixed $params = [];
    protected string|null $message;
    protected string|null $status;
    protected ResponseType $type = ResponseType::HTML;

    public function __construct(ResponseType $type){
        $this->type = $type;
    }

    public function getType() : ResponseType {
        return $this->type;
    }

}




