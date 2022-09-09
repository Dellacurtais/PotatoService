<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\ResponseType;

class ResponseJson extends ResponseReturn {

    public function __construct(){
        parent::__construct(ResponseType::JSON );
    }

    public function setStatus(string $status): self {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string{
        return $this->status;
    }

    public function setMessage(string $message): self {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function setResponse(mixed $params): self {
        $this->params = $params;
        return $this;
    }

    public function getResponse(): mixed {
        return $this->params;
    }

    public function toJson(){
        return json_encode(["status" => $this->getStatus(), "message" => $this->getMessage(), "response" => $this->getResponse()]);
    }
}