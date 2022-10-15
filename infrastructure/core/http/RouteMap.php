<?php
namespace infrastructure\core\http;


use infrastructure\core\enums\HttpRequest;
use infrastructure\core\enums\StatusCode;
use infrastructure\core\exception\InvalidRequestException;

class RouteMap {

    private string|null $pattern = null;
    private array|null $properties = [];
    private array|null $params = null;
    public StatusCode|null $statusCode = null;

    public function __construct(
        private HttpRequest $httpRequest,
        private string $route,
        private array $targetResource,
        public string|null $alias,
        private array|null $responseHeaders,
        private array|null $requireHeadersOnRequest,
    ){
        preg_match_all("/{(.*?)}/", $this->route, $haveArgs);
        if (count($haveArgs[0]) > 0){
            $this->pattern = str_replace($haveArgs[0], '([^/]+)', $this->route);
            $this->properties = $haveArgs[1];
        }
        if (empty($this->alias))
            $this->alias = $this->route;
    }

    public function setStatusCode(StatusCode $statusCode){
        $this->statusCode = $statusCode;
    }

    public function getClass() : string {
        return $this->targetResource[0];
    }

    public function getMethod() : string {
        return $this->targetResource[1];
    }

    public function getParams(): ?array {
        return $this->params;
    }

    public function validate($requestUri) : bool{
        if (request()->requestType != $this->httpRequest)
            return false;

        if (empty($this->pattern) && $requestUri === $this->route){
            return true;
        }else{
            if (preg_match('#^'. $this->pattern .'$#', $requestUri, $validate)){
                foreach ($this->properties as $k=>$var){
                    $this->params[$var] = $validate[$k+1];
                }
                return $validate[0] == $requestUri;
            }
        }
        return false;
    }

    public function validateRequiredHeaders(){
        foreach ($this->requireHeadersOnRequest as $key=>$header) {
            if (!request()->getHeader($key)){
                throw new InvalidRequestException();
            }
        }
    }

    public function setResponseHeaders(){
        foreach ($this->responseHeaders as $header){
            response()->setHeader($header);
        }
    }


    public function toUri(array $args): string {
        return base_url(str_replace($this->properties, $args, $this->route));
    }
}