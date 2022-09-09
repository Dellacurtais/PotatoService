<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\ResponseType;

class ResponseHtml extends ResponseReturn {

    public function __construct(){
        parent::__construct(ResponseType::HTML);
    }

    public function setView(string $file): self{
        $this->file = $file;
        return $this;
    }

    public function getView(): string{
        return $this->file;
    }

    public function setParams(array $params): self{
        $this->params = $params;
        return $this;
    }

    public function getParams(): array {
        return $this->params;
    }

}