<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\ResponseType;

class ResponseJson extends ResponseReturn {

    public static function build($response, $message = "", $status = 200): ResponseJson {
        return responseJson()
            ->setResponse($response)
            ->setMessage($message)
            ->setStatus($status);
    }

    public function __construct(){
        parent::__construct(ResponseType::JSON );
        $this->message = '';
        $this->status = 200;
        $this->params = [];
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
        $data = [
            "status" => $this->getStatus(),
            "message" => $this->getMessage(),
            "response" => $this->getResponse(),
            "processTime" => getTimeSinceInit(),
        ];
        if (function_exists('profiler_enabled') && profiler_enabled()){
            $data['debug'] = [
                'profile' => profiler_report(),
            ];
        }
        return json_encode($data);
    }
}