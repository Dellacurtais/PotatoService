<?php
namespace infrastructure\core\http;

use infrastructure\core\enums\HttpRequest;
use infrastructure\core\traits\Singleton;

class Request {

    use Singleton;

    public array|null $headers;
    public array|null $json;
    public array|null $bundle;
    public array|null $post;
    public array|null $get;

    public HttpRequest|null $requestType;
    public string|null $requestUri;
    public array|null $mapUri;
    public RouteMap|null $activeRoute = null;

    public function __construct(){
        $this->requestType = HttpRequest::fromString($_SERVER['REQUEST_METHOD']);
        $this->mapUri = array_filter( explode('/', getUriPatch()), fn($patch) => $patch != "");

        $this->requestUri = implode('/', $this->mapUri);
        if (empty($this->requestUri))
            $this->requestUri = $_ENV['DEFAULT_ROUTE'];

        $this->headers = getRequestHeaders();
        $this->json = jsonBody();
        $this->get = $_GET;
        $this->post = $_POST;
    }

    public function get(string $key, $default = null){
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null){
        return $this->post[$key] ?? $default;
    }

    public function json(string $key, mixed $default = null){
        return $this->json[$key] ?? $default;
    }

    public function request(string $key = null, mixed $default = null){
        return $_REQUEST[$key] ?? $default;
    }

    public function getHeader($key = null){
        return $this->allHeaders[$key] ?? null;
    }

    public function setBundle($key, $value){
        $this->bundle[$key] = $value;
    }

    public function bundle($key){
        return $this->bundle[$key] ?? null;
    }

    public function find(string|null $key){
        if (is_null($key))
            return null;

        if (isset($this->get[$key]))
            return $this->get[$key];

        if (isset($this->post[$key]))
            return $this->post[$key];

        if (isset($this->json[$key]))
            return $this->json[$key];

        if (isset($this->bundle[$key]))
            return $this->bundle[$key];

        if (isset($this->bundle[$key]))
            return $this->bundle[$key];

        if (isset($_REQUEST[$key]))
            return $_REQUEST[$key];

        return null;
    }

    public function __get($key){
        return self::find($key);
    }

}
